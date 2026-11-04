<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Mail\ReviewPendingAdminMail;
use App\Models\Order;
use App\Models\Product;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Illuminate\Validation\Rule;

class ReviewController extends Controller
{
    public function store(Request $request, Product $product): RedirectResponse
    {
        $data = $request->validate([
            'order_id' => [
                'required',
                'integer',
                Rule::exists('orders', 'id')->where(function ($query) {
                    $query->where('user_id', auth()->id())
                        ->where('status', OrderStatus::Completed->value);
                }),
            ],
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov,webm|max:20480',
        ]);
        $order = Order::query()->whereKey($data['order_id'])->firstOrFail();
        if (! $order->items()->where('product_id', $product->id)->exists()) {
            return back()->with('error', 'Sản phẩm này không có trong đơn hàng đã chọn.');
        }
        if (Review::query()->where('user_id', auth()->id())->where('product_id', $product->id)->exists()) {
            return back()->with('error', 'Bạn đã đánh giá sản phẩm này rồi. Có thể chỉnh sửa đánh giá hiện có.');
        }
        $review = Review::query()->create([
            'user_id' => auth()->id(),
            'product_id' => $product->id,
            'order_id' => $order->id,
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'image_path' => $request->hasFile('media') ? $request->file('media')->store('reviews', 'public') : null,
            'media_type' => $request->hasFile('media') && str_starts_with((string) $request->file('media')->getMimeType(), 'video/') ? 'video' : 'image',
            'is_approved' => false,
        ]);
        $adminUrl = URL::route('admin.reviews-moderation.index');
        $review->load('product');
        foreach (config('shop.admin_notify_emails', []) as $email) {
            if ($email) {
                Mail::to($email)->queue(new ReviewPendingAdminMail($review, $adminUrl));
            }
        }

        return back()->with('status', 'Cảm ơn bạn! Đánh giá sẽ hiển thị sau khi được duyệt.');
    }

    public function update(Request $request, Review $review): RedirectResponse
    {
        abort_if($review->user_id !== auth()->id(), 403);

        $data = $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:2000',
            'media' => 'nullable|file|mimes:jpg,jpeg,png,webp,mp4,mov,webm|max:20480',
        ]);

        $wasApproved = $review->is_approved;
        $oldImagePath = $review->image_path;
        $mediaPath = $request->hasFile('media')
            ? $request->file('media')->store('reviews', 'public')
            : $oldImagePath;
        $mediaType = $request->hasFile('media')
            ? (str_starts_with((string) $request->file('media')->getMimeType(), 'video/') ? 'video' : 'image')
            : ($review->media_type ?: 'image');
        $review->update([
            'rating' => $data['rating'],
            'comment' => $data['comment'] ?? null,
            'image_path' => $mediaPath,
            'media_type' => $mediaType,
            'is_approved' => false,
        ]);
        if ($request->hasFile('media') && $oldImagePath && ! preg_match('#^https?://#i', (string) $oldImagePath)) {
            Storage::disk('public')->delete($oldImagePath);
        }
        $review->product->recalculateReviewStats();

        if ($wasApproved) {
            $review->load('product');
            $adminUrl = URL::route('admin.reviews-moderation.index');
            foreach (config('shop.admin_notify_emails', []) as $email) {
                if ($email) {
                    Mail::to($email)->queue(new ReviewPendingAdminMail($review, $adminUrl));
                }
            }
        }

        return back()->with('status', 'Đã cập nhật đánh giá. Nội dung mới sẽ hiển thị sau khi được duyệt lại.');
    }
}
