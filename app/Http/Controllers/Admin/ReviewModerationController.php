<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ReviewModerationController extends Controller
{
    public function index(): View
    {
        $reviews = Review::query()->with(['user', 'product'])->latest()->paginate(25);
        $productSummaries = Product::query()
            ->whereHas('reviews')
            ->withCount('reviews')
            ->withCount(['reviews as approved_reviews_count' => fn ($query) => $query->where('is_approved', true)])
            ->withCount(['reviews as pending_reviews_count' => fn ($query) => $query->where('is_approved', false)])
            ->withAvg(['reviews as approved_rating_average' => fn ($query) => $query->where('is_approved', true)], 'rating')
            ->orderByDesc('reviews_count')
            ->orderBy('name')
            ->paginate(20, ['*'], 'products_page');

        return view('admin.reviews.index', compact('reviews', 'productSummaries'));
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => true]);
        $review->product->recalculateReviewStats();

        return back()->with('status', 'Đã duyệt đánh giá.');
    }

    public function reply(Request $request, Review $review): RedirectResponse
    {
        $data = $request->validate([
            'admin_reply' => 'nullable|string|max:2000',
        ]);
        $reply = trim((string) ($data['admin_reply'] ?? ''));
        $review->update([
            'admin_reply' => $reply !== '' ? $reply : null,
            'admin_replied_at' => $reply !== '' ? now() : null,
        ]);

        return back()->with('status', $reply !== '' ? 'Đã gửi phản hồi cho đánh giá.' : 'Đã xóa phản hồi.');
    }

    public function reject(Review $review): RedirectResponse
    {
        $product = $review->product;
        $imagePath = $review->image_path;
        $review->delete();
        if ($imagePath && ! preg_match('#^https?://#i', (string) $imagePath)) {
            Storage::disk('public')->delete($imagePath);
        }
        $product->recalculateReviewStats();

        return back()->with('status', 'Đã từ chối đánh giá.');
    }
}
