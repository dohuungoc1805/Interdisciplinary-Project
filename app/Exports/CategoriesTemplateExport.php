<?php

namespace App\Exports;

use App\Models\Category;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;

class CategoriesTemplateExport implements FromCollection, WithHeadings, WithTitle
{
    public function title(): string
    {
        return 'Categories';
    }

    public function headings(): array
    {
        return ['category_id', 'category_name', 'parent_category_id', 'is_active'];
    }

    public function collection(): Collection
    {
        return Category::query()
            ->orderBy('id')
            ->get(['id', 'name', 'parent_id', 'is_active'])
            ->map(fn (Category $category) => [
                'category_id' => $category->id,
                'category_name' => $category->name,
                'parent_category_id' => $category->parent_id,
                'is_active' => $category->is_active ? 1 : 0,
            ]);
    }
}
