<?php

namespace Tests\Feature\Shop;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductSearchTest extends TestCase
{
    use RefreshDatabase;

    public function test_product_search_returns_a_similar_product_for_a_typo(): void
    {
        \DB::table('categories')->insert([
            'name' => 'Bé trai',
            'slug' => 'be-trai',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('products')->insert([
            'name' => 'Áo hoodie nỉ bé trai',
            'slug' => 'ao-hoodie-ni-be-trai',
            'description' => 'Áo hoodie mềm ấm cho bé',
            'price' => 259000,
            'category_id' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->get(route('products.index', ['q' => 'áo hoodlly']))
            ->assertOk()
            ->assertSee('Áo hoodie nỉ bé trai')
            ->assertSee('Có một số kết quả gần với từ khóa bạn nhập');
    }
}
