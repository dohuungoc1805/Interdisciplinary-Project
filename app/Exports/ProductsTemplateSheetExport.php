<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class ProductsTemplateSheetExport implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Products';
    }

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
        return collect([
            ['AO-SOM-001', 'Ao so mi mau', 1, 'Thoi trang nu', 'Mo ta san pham mau', '', 199000, 249000, 10, 'M', 'Trang', 'AO-SOM-001-M-TRANG', '', '', 1, 'active'],
            ['AO-SOM-001', 'Ao so mi mau', 1, 'Thoi trang nu', 'Mo ta san pham mau', '', 199000, 249000, 5, 'L', 'Trang', 'AO-SOM-001-L-TRANG', '', '', 1, 'active'],
            ['AO-SOM-001', 'Ao so mi mau', 1, 'Thoi trang nu', 'Mo ta san pham mau', '', 199000, 249000, 8, 'M', 'Den', 'AO-SOM-001-M-DEN', '', '', 1, 'active'],
            ['GIAY-001', 'Giay mau', 4, 'Giay dep & tui xach', 'Mo ta giay mau', '', 399000, 499000, 6, '39', 'Trang', 'GIAY-001-39-TRANG', '', '', 1, 'active'],
        ]);
    }
}
