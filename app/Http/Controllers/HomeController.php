<?php

namespace App\Http\Controllers;

use App\Models\Banner;
use App\Models\Category;
use App\Models\Product;
use Illuminate\View\View;

class HomeController extends Controller
{
    public function __invoke(): View
    {
        $banners = Banner::query()->visible()->with(['product.images'])->orderBy('sort_order')->take(8)->get();

        $parentCategories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->orderBy('position')
            ->get();

        $flashDeals = Product::query()
            ->active()
            ->whereNotNull('compare_price')
            ->whereColumn('compare_price', '>', 'price')
            ->with('category')
            ->latest('id')
            ->take(10)
            ->get();

        $newArrivals = Product::query()
            ->active()
            ->where('is_new', true)
            ->with('category')
            ->latest('id')
            ->take(12)
            ->get();

        $topRated = Product::query()
            ->active()
            ->where('review_count', '>', 0)
            ->orderByDesc('average_rating')
            ->orderByDesc('review_count')
            ->with('category')
            ->take(8)
            ->get();

        $featured = Product::query()
            ->active()
            ->where('is_featured', true)
            ->with(['images', 'category'])
            ->latest()
            ->take(10)
            ->get();

        $moreToLove = Product::query()
            ->active()
            ->with('category')
            ->inRandomOrder()
            ->take(16)
            ->get();

        return view('shop.home', compact(
            'banners',
            'parentCategories',
            'flashDeals',
            'newArrivals',
            'topRated',
            'featured',
            'moreToLove'
        ));
    }
}
