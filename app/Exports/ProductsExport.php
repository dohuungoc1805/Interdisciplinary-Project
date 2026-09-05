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
            'description',
            'price',
            'compare_price',
            'stock',
            'variant_size',
            'variant_color',
            'is_active',
        ];
    }

    public function collection(): Collection
    {
        return Product::query()
            ->with(['variants' => fn ($query) => $query->orderBy('id')])
            ->orderBy('id')
            ->get()
            ->map(function (Product $product) {
                $variant = $product->variants->first();

                return [
                    'sku' => $product->sku,
                    'name' => $product->name,
                    'category_id' => $product->category_id,
                    'description' => $product->description,
                    'price' => $product->price,
                    'compare_price' => $product->compare_price,
                    'stock' => $variant?->stock ?? 0,
                    'variant_size' => $variant?->size ?? 'Freesize',
                    'variant_color' => $variant?->color ?? 'Mac dinh',
                    'is_active' => $product->is_active ? 1 : 0,
                ];
            });
    }
}
