<?php

namespace Database\Seeders;

use App\Imports\ProductsImport;
use Illuminate\Database\Seeder;
use Maatwebsite\Excel\Facades\Excel;
use RuntimeException;

class ImportedProductsSeeder extends Seeder
{
    public function run(): void
    {
        $file = database_path('imports/products-shopee-import.xlsx');
        if (! is_file($file)) {
            throw new RuntimeException("Không tìm thấy file sản phẩm: {$file}");
        }

        $import = new ProductsImport($file);
        Excel::import($import, $file);

        if ($import->errors !== []) {
            throw new RuntimeException('Import sản phẩm lỗi: '.implode(' | ', $import->errors));
        }
    }
}
