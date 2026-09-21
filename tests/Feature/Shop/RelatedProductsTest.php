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
            ['id' => 1, 'name' => 'Trẻ em', 'slug' => 'tre-em', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Bé trai', 'slug' => 'be-trai', 'parent_id' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 3, 'name' => 'Bé gái', 'slug' => 'be-gai', 'parent_id' => 1, 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
            ['id' => 4, 'name' => 'Thời trang nam', 'slug' => 'thoi-trang-nam', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()],
        ]);

        \DB::table('products')->insert([
            [
                'name' => 'Áo hoodie nỉ bé trai', 'slug' => 'ao-hoodie-ni-be-trai', 'description' => 'Áo ấm cho bé',
                'price' => 259000, 'category_id' => 2, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'name' => 'Chân váy dự tiệc bé gái', 'slug' => 'chan-vay-du-tiec-be-gai', 'description' => 'Váy cho bé',
                'price' => 229000, 'category_id' => 3, 'is_active' => true, 'is_featured' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'name' => 'Áo polo nam', 'slug' => 'ao-polo-nam', 'description' => 'Áo nam',
                'price' => 399000, 'category_id' => 4, 'is_active' => true, 'created_at' => now(), 'updated_at' => now(),
            ],
        ]);

        $this->get(route('products.show', 'ao-hoodie-ni-be-trai'))
            ->assertOk()
            ->assertSee('Khám phá thêm')
            ->assertSee('Chân váy dự tiệc bé gái')
            ->assertDontSee('Áo polo nam');
    }
}
