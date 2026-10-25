<?php

namespace Tests\Feature\Shop;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RelatedProductsTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_page_shows_active_products_from_the_same_category_group(): void
    {
        \DB::table('categories')->insert([
            ['id' => 1, 'name' => 'Trẻ em', 'slug' => 'tre-em', 'parent_id' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Bé trai', 'slug' => 'be-trai', 'parent_id' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Bé gái', 'slug' => 'be-gai', 'parent_id' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Thời trang nam', 'slug' => 'thoi-trang-nam', 'parent_id' => null, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        \DB::table('products')->insert([
            [
                'name' => 'Áo hoodie nỉ bé trai', 'slug' => 'ao-hoodie-ni-be-trai', 'description' => 'Áo ấm cho bé',
                'price' => 259000, 'category_id' => 2, 'is_active' => true, 'is_featured' => false, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'name' => 'Chân váy dự tiệc bé gái', 'slug' => 'chan-vay-du-tiec-be-gai', 'description' => 'Váy cho bé',
                'price' => 229000, 'category_id' => 3, 'is_active' => true, 'is_featured' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'name' => 'Áo polo nam', 'slug' => 'ao-polo-nam', 'description' => 'Áo nam',
                'price' => 399000, 'category_id' => 4, 'is_active' => true, 'is_featured' => false, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        \DB::table('product_variants')->insert([
            ['product_id' => 1, 'size' => 'M', 'color' => 'Den', 'sku' => 'HD-1', 'stock' => 5, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => 2, 'size' => 'M', 'color' => 'Hong', 'sku' => 'CV-1', 'stock' => 3, 'created_at' => now(), 'updated_at' => now()],
            ['product_id' => 3, 'size' => 'M', 'color' => 'Den', 'sku' => 'AP-1', 'stock' => 2, 'created_at' => now(), 'updated_at' => now()],
        ]);

        $this->get(route('products.show', 'ao-hoodie-ni-be-trai'))
            ->assertOk()
            ->assertSee('Khám phá thêm')
            ->assertSee('Chân váy dự tiệc bé gái')
            ->assertDontSee('Áo polo nam');
    }
}
