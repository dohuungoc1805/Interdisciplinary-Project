<?php

namespace Tests\Feature\Shop;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductFilterTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_filters_by_size_color_stock_and_sale_on_the_same_variant(): void
    {
        \DB::table('categories')->insert([
            'name' => 'Áo',
            'slug' => 'ao',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('products')->insert([
            [
                'id' => 1, 'name' => 'Áo thun đen sale', 'slug' => 'ao-thun-den-sale', 'description' => 'Có size M màu đen',
                'price' => 299000, 'category_id' => 1, 'is_active' => true, 'is_on_sale' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 2, 'name' => 'Áo thun đen hết hàng', 'slug' => 'ao-thun-den-het-hang', 'description' => 'Đã hết hàng',
                'price' => 299000, 'category_id' => 1, 'is_active' => true, 'is_on_sale' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 3, 'name' => 'Áo có màu và size lệch nhau', 'slug' => 'ao-size-mau-lech', 'description' => 'Không có size M màu đen',
                'price' => 299000, 'category_id' => 1, 'is_active' => true, 'is_on_sale' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'id' => 4, 'name' => 'Áo đen không sale', 'slug' => 'ao-den-khong-sale', 'description' => 'Không sale',
                'price' => 299000, 'category_id' => 1, 'is_active' => true, 'is_on_sale' => false, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        \DB::table('product_variants')->insert([
            ['product_id' => 1, 'size' => 'M', 'color' => 'Đen', 'sku' => 'AT-1', 'stock' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => 2, 'size' => 'M', 'color' => 'Đen', 'sku' => 'AT-2', 'stock' => 0, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => 3, 'size' => 'M', 'color' => 'Đỏ', 'sku' => 'AT-3-M', 'stock' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => 3, 'size' => 'L', 'color' => 'Đen', 'sku' => 'AT-3-L', 'stock' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => 4, 'size' => 'M', 'color' => 'Đen', 'sku' => 'AT-4', 'stock' => 5, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->get(route('products.index', [
            'size' => 'M',
            'color' => 'Đen',
            'in_stock' => 1,
            'sale' => 1,
        ]))
            ->assertOk()
            ->assertSee('Áo thun đen sale')
            ->assertDontSee('Áo thun đen hết hàng')
            ->assertDontSee('Áo có màu và size lệch nhau')
            ->assertDontSee('Áo đen không sale');
    }

    public function test_catalog_parent_category_includes_products_from_its_child_categories(): void
    {
        \DB::table('categories')->insert([
            ['id' => 1, 'name' => 'Trẻ em', 'slug' => 'tre-em', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Bé trai', 'slug' => 'be-trai', 'parent_id' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Thời trang nam', 'slug' => 'thoi-trang-nam', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);
        \DB::table('products')->insert([
            ['name' => 'Áo hoodie bé trai', 'slug' => 'ao-hoodie-be-trai', 'description' => 'Áo cho bé', 'price' => 259000, 'category_id' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['name' => 'Áo polo nam', 'slug' => 'ao-polo-nam', 'description' => 'Áo nam', 'price' => 399000, 'category_id' => 3, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->get(route('products.index', ['category' => 1]))
            ->assertOk()
            ->assertSee('Áo hoodie bé trai')
            ->assertDontSee('Áo polo nam');
    }
}
