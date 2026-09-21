<?php

namespace Tests\Feature\Shop;

use App\Models\Cart;
use App\Services\CartService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartShippingTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'shop.default_shipping' => 30000,
            'shop.free_shipping_threshold' => 500000,
        ]);
    }

    public function test_cart_charges_shipping_below_the_free_shipping_threshold(): void
    {
        $cart = $this->cartWithProductPrice(400000);

        $totals = app(CartService::class)->getTotals($cart);

        $this->assertSame(30000.0, $totals['shipping']);
        $this->assertFalse($totals['is_free_shipping']);
        $this->assertSame(100000.0, $totals['remaining_for_free_shipping']);
    }

    public function test_cart_gets_free_shipping_at_the_threshold(): void
    {
        $cart = $this->cartWithProductPrice(500000);

        $totals = app(CartService::class)->getTotals($cart);

        $this->assertSame(0.0, $totals['shipping']);
        $this->assertTrue($totals['is_free_shipping']);
        $this->assertSame(0.0, $totals['remaining_for_free_shipping']);
    }

    private function cartWithProductPrice(int $price): Cart
    {
        \DB::table('categories')->insert([
            'name' => 'Áo',
            'slug' => 'ao',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('products')->insert([
            'name' => 'Áo thun',
            'slug' => 'ao-thun-'.$price,
            'description' => 'Sản phẩm thử nghiệm',
            'price' => $price,
            'category_id' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        \DB::table('product_variants')->insert([
            'product_id' => 1,
            'size' => 'M',
            'color' => 'Đen',
            'sku' => 'AT-'.$price,
            'stock' => 5,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $cart = Cart::query()->create(['session_id' => 'shipping-test-'.$price]);
        $cart->items()->create(['product_variant_id' => 1, 'quantity' => 1]);

        return $cart;
    }
}
