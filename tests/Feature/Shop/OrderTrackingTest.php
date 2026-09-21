<?php

namespace Tests\Feature\Shop;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTrackingTest extends TestCase
{
    use RefreshDatabase;

    public function test_customer_can_filter_orders_and_see_delivery_progress(): void
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $shippingOrder = $this->createOrder($user, 'QN-001', 'shipping');
        $this->createOrder($user, 'QN-002', 'pending');

        $this->actingAs($user)
            ->get(route('purchases.index', ['status' => 'shipping']))
            ->assertOk()
            ->assertSee('QN-001')
            ->assertDontSee('QN-002');

        $this->actingAs($user)
            ->get(route('orders.show', $shippingOrder))
            ->assertOk()
            ->assertSee('Tiếp nhận')
            ->assertSee('Chuẩn bị hàng')
            ->assertSee('Đang giao')
            ->assertSee('Hoàn thành');
    }

    private function createOrder(User $user, string $number, string $status): Order
    {
        return Order::query()->create([
            'order_number' => $number,
            'user_id' => $user->id,
            'status' => $status,
            'payment_method' => 'cod',
            'subtotal' => 250000,
            'discount_total' => 0,
            'shipping' => 30000,
            'total' => 280000,
            'recipient_name' => $user->name,
            'phone' => '0900000000',
            'line1' => '1 Nguyen Hue',
            'city' => 'Ho Chi Minh City',
            'postal_code' => '700000',
            'country' => 'VN',
        ]);
    }
}
