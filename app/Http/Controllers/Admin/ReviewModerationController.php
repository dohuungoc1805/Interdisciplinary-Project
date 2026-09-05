<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ReviewModerationController extends Controller
{
    public function index(): View
    {
        $reviews = Review::query()->with(['user', 'product'])->where('is_approved', false)->latest()->paginate(25);

        return view('admin.reviews.index', compact('reviews'));
    }

    public function approve(Review $review): RedirectResponse
    {
        $review->update(['is_approved' => true]);
        $review->product->recalculateReviewStats();

        return back()->with('status', 'Đã duyệt đánh giá.');
    }

    public function reject(Review $review): RedirectResponse
    {
        $product = $review->product;
        $review->delete();
        $product->recalculateReviewStats();

        return back()->with('status', 'Đã từ chối đánh giá.');
    }
}
