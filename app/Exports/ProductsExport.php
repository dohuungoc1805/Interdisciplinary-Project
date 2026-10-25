<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'sku',
            'name',
            'category_id',
            'category_name',
            'description',
            'main_image_url',
            'price',
            'compare_price',
            'variant_stock',
            'variant_size',
            'variant_color',
            'variant_sku',
            'variant_price',
            'variant_image_url',
            'is_active',
            'status',
        ];
    }

    public function collection(): Collection
    {
        return Product::query()
            ->with(['variants' => fn ($query) => $query->orderBy('id')])
            ->orderBy('id')
            ->get()
            ->flatMap(function (Product $product) {
                $variants = $product->variants->isNotEmpty()
                    ? $product->variants
                    : collect([null]);

                return $variants->map(function ($variant) use ($product) {
                    return [
                        'sku' => $product->sku,
                        'name' => $product->name,
                        'category_id' => $product->category_id,
                        'category_name' => $product->category?->name ?? '',
                        'description' => $product->description,
                        'main_image_url' => $product->mainImageUrl(),
                        'price' => $product->price,
                        'compare_price' => $product->compare_price,
                        'variant_stock' => $variant?->stock ?? 0,
                        'variant_size' => $variant?->size ?? 'Freesize',
                        'variant_color' => $variant?->color ?? 'Mac dinh',
                        'variant_sku' => $variant?->sku ?? '',
                        'variant_price' => $variant?->price,
                        'variant_image_url' => $variant?->imageUrl() ?? '',
                        'is_active' => $product->is_active ? 1 : 0,
                        'status' => $product->status,
                    ];
                });
            });
    }
}
