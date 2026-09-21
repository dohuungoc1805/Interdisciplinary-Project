<?php

namespace App\Http\Controllers\Shop;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Services\ProductSearchService;
use App\Services\RelatedProductService;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request, ProductSearchService $productSearchService): View
    {
        $q = Product::query()->where('is_active', true)->with(['images', 'category', 'variants']);

        if ($request->filled('category')) {
            $selectedCategory = Category::query()
                ->where('is_active', true)
                ->find($request->integer('category'));
            if ($selectedCategory) {
                $categoryIds = [$selectedCategory->id];
                if ($selectedCategory->parent_id === null) {
                    $categoryIds = $selectedCategory->children()
                        ->where('is_active', true)
                        ->pluck('id')
                        ->prepend($selectedCategory->id)
                        ->all();
                }
                $q->whereIn('category_id', $categoryIds);
            }
        }
        $searchTerm = trim((string) $request->query('q', ''));
        if ($request->filled('min')) {
            $q->where('price', '>=', (float) $request->get('min'));
        }
        if ($request->filled('max')) {
            $q->where('price', '<=', (float) $request->get('max'));
        }
        $size = trim((string) $request->query('size', ''));
        $color = trim((string) $request->query('color', ''));
        $onlyInStock = $request->boolean('in_stock');
        if ($size !== '' || $color !== '' || $onlyInStock) {
            $q->whereHas('variants', function ($variantQuery) use ($size, $color, $onlyInStock) {
                if ($size !== '') {
                    $variantQuery->where('size', $size);
                }
                if ($color !== '') {
                    $variantQuery->where('color', $color);
                }
                if ($onlyInStock) {
                    $variantQuery->where('stock', '>', 0);
                }
            });
        }
        $onlyOnSale = $request->boolean('sale');
        if ($onlyOnSale) {
            $q->where('is_on_sale', true);
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

        $hasFuzzyResults = false;
        if ($searchTerm !== '') {
            $rankedProducts = $productSearchService->rank($q->get(), $searchTerm);
            $hasFuzzyResults = $rankedProducts->contains(fn (Product $product) => (bool) $product->getAttribute('is_fuzzy_match'));
            $page = LengthAwarePaginator::resolveCurrentPage();
            $products = new LengthAwarePaginator(
                $rankedProducts->forPage($page, 12)->values(),
                $rankedProducts->count(),
                12,
                $page,
                ['path' => $request->url(), 'query' => $request->query()]
            );
        } else {
            $products = $q->paginate(12)->withQueryString();
        }
        $categories = Category::query()
            ->where('is_active', true)
            ->whereNull('parent_id')
            ->with(['children' => fn ($childrenQuery) => $childrenQuery->where('is_active', true)->orderBy('position')->orderBy('name')])
            ->orderBy('position')
            ->orderBy('name')
            ->get();
        $activeVariants = ProductVariant::query()->whereHas('product', fn ($productQuery) => $productQuery->where('is_active', true));
        $sizes = (clone $activeVariants)->where('size', '!=', '')->distinct()->orderBy('size')->pluck('size');
        $colors = (clone $activeVariants)->where('color', '!=', '')->distinct()->orderBy('color')->pluck('color');

        return view('shop.products.index', compact('products', 'categories', 'sizes', 'colors', 'onlyNew', 'hasFuzzyResults'));
    }

    public function show(string $slug, RelatedProductService $relatedProductService): View
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
        $relatedProducts = $relatedProductService->for($product);

        return view('shop.products.show', compact('product', 'reviews', 'userReview', 'reviewableOrders', 'relatedProducts'));
    }
}
