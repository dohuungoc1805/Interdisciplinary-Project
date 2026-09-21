<?php

namespace App\Services;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Support\Collection;

class RelatedProductService
{
    /**
     * Find active products from the same category group as the viewed product.
     *
     * @return Collection<int, Product>
     */
    public function for(Product $product, int $limit = 4): Collection
    {
        if (! $product->category_id) {
            return collect();
        }

        $categoryIds = [$product->category_id];
        $category = $product->category;

        if ($category?->parent_id) {
            $categoryIds = Category::query()
                ->where('parent_id', $category->parent_id)
                ->where('is_active', true)
                ->pluck('id')
                ->all();
        }

        return Product::query()
            ->where('is_active', true)
            ->whereIn('category_id', $categoryIds)
            ->whereKeyNot($product->id)
            ->with(['images', 'category', 'variants'])
            ->orderByDesc('is_featured')
            ->orderByDesc('is_new')
            ->orderByDesc('is_on_sale')
            ->orderByDesc('average_rating')
            ->orderByDesc('review_count')
            ->latest()
            ->limit($limit)
            ->get();
    }
}
