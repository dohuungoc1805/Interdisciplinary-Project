<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ProductsTemplateExport implements FromCollection, WithHeadings
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
        return collect([
            ['SKU-001', 'Áo thun mẫu', 1, 'Mô tả sản phẩm mẫu', 199000, 249000, 100, 'Freesize', 'Mặc định', 1],
        ]);
    }
}
