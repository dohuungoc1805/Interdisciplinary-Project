<?php

namespace Tests\Feature\Admin;

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductBulkActionsTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_selected_products_and_deactivate_them_without_deleting_rows(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $category = Category::query()->create(['name' => 'Áo', 'slug' => 'ao', 'is_active' => true]);
        $otherCategory = Category::query()->create(['name' => 'Quần', 'slug' => 'quan', 'is_active' => true]);
        $products = collect(['Áo một', 'Áo hai'])->map(function (string $name, int $index) use ($category) {
            $product = Product::query()->create([
                'category_id' => $category->id,
                'name' => $name,
                'slug' => 'ao-'.$index,
                'price' => 100000,
                'sku' => 'AO-'.$index,
                'is_active' => true,
            ]);
            ProductVariant::query()->create([
                'product_id' => $product->id,
                'size' => 'M',
                'color' => 'Đen',
                'sku' => 'AO-'.$index.'-M-DEN',
                'stock' => 2,
            ]);

            return $product;
        });

        $payload = [
            'product_ids' => $products->pluck('id')->all(),
            'action' => 'price',
            'price' => 125000,
        ];
        $this->actingAs($admin)->post(route('admin.products.bulk'), $payload)->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $products[0]->id, 'price' => 125000]);

        $this->actingAs($admin)->post(route('admin.products.bulk'), [
            'product_ids' => $products->pluck('id')->all(),
            'action' => 'category',
            'category_id' => $otherCategory->id,
        ])->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $products[1]->id, 'category_id' => $otherCategory->id]);

        $this->actingAs($admin)->post(route('admin.products.bulk'), [
            'product_ids' => $products->pluck('id')->all(),
            'action' => 'stock',
            'stock' => 8,
        ])->assertRedirect();
        $this->assertDatabaseHas('product_variants', ['product_id' => $products[0]->id, 'stock' => 8]);

        $this->actingAs($admin)->post(route('admin.products.bulk'), [
            'product_ids' => $products->pluck('id')->all(),
            'action' => 'price_increase_percent',
            'percent' => 10,
        ])->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $products[0]->id, 'price' => 137500]);

        $this->actingAs($admin)->post(route('admin.products.bulk'), [
            'product_ids' => $products->pluck('id')->all(),
            'action' => 'stock_increase',
            'quantity' => 2,
        ])->assertRedirect();
        $this->assertDatabaseHas('product_variants', ['product_id' => $products[0]->id, 'stock' => 10]);

        $this->actingAs($admin)->delete(route('admin.products.destroy', $products[0]))->assertRedirect();
        $this->assertDatabaseHas('products', ['id' => $products[0]->id, 'is_active' => false]);
        $this->assertDatabaseHas('product_variants', ['product_id' => $products[0]->id]);
    }
}
