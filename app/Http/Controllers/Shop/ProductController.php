<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $q = Product::query()->where('is_active', true)->with(['images', 'category', 'variants']);

        if ($request->filled('category')) {
            $q->where('category_id', $request->integer('category'));
        }
        $searchTerm = trim((string) $request->query('q', ''));
        if ($searchTerm !== '') {
            $like = '%'.addcslashes($searchTerm, '%_\\').'%';
            $q->where(function ($q2) use ($like) {
                $q2->where('name', 'like', $like)
                    ->orWhere('sku', 'like', $like)
                    ->orWhere('description', 'like', $like);
            });
        }
        if ($request->filled('min')) {
            $q->where('price', '>=', (float) $request->get('min'));
        }
        if ($request->filled('max')) {
            $q->where('price', '<=', (float) $request->get('max'));
        }
        $onlyNew = $request->boolean('new');
        if ($onlyNew) {
            $q->where('is_new', true);
        }
        $sort = $request->get('sort', 'newest');
        match ($sort) {
            'price_asc' => $q->orderBy('price'),
            'price_desc' => $q->orderByDesc('price'),
            'name' => $q->orderBy('name'),
            default => $q->latest(),
        };

        $products = $q->paginate(12)->withQueryString();
        $categories = Category::query()->where('is_active', true)->orderBy('name')->get();

        return view('shop.products.index', compact('products', 'categories', 'onlyNew'));
    }

    public function show(string $slug): View
    {
        $product = Product::query()->where('slug', $slug)->where('is_active', true)
            ->with(['images', 'category', 'variants' => function ($q) {
                $q->orderBy('size');
            }])
            ->firstOrFail();
        $reviews = $product->reviews()->where('is_approved', true)->with('user')->latest()->take(20)->get();

        $userReview = null;
        $reviewableOrders = collect();
        if (Auth::check()) {
            $userReview = $product->reviews()->where('user_id', Auth::id())->first();
            if (! $userReview) {
                $reviewableOrders = Order::query()
                    ->where('user_id', Auth::id())
                    ->where('status', OrderStatus::Completed->value)
                    ->whereHas('items', fn ($q) => $q->where('product_id', $product->id))
                    ->orderByDesc('id')
                    ->get(['id', 'order_number', 'created_at']);
            }
        }

        return view('shop.products.show', compact('product', 'reviews', 'userReview', 'reviewableOrders'));
    }
}
