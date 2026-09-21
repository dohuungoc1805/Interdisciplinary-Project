<?php

namespace Tests\Feature\Shop;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BankTransferPaymentTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'shop.bank_transfer.bank_code' => 'VCB',
            'shop.bank_transfer.bank_name' => 'Vietcombank',
            'shop.bank_transfer.account_number' => '0123456789',
            'shop.bank_transfer.account_name' => 'QUYEN BUI',
        ]);
    }

    public function test_customer_sees_a_qr_code_with_the_order_amount_and_reference(): void
    {
        $customer = User::factory()->create();
        $order = $this->createBankTransferOrder($customer);

        $this->actingAs($customer)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('img.vietqr.io')
            ->assertSee('0123456789')
            ->assertSee('Vietcombank')
            ->assertSee('QUDENA QN-20260921-001')
            ->assertSee('280.000');
    }

    public function test_admin_can_confirm_a_manual_bank_transfer_payment(): void
    {
        $customer = User::factory()->create();
        $admin = User::factory()->create(['is_admin' => true]);
        $order = $this->createBankTransferOrder($customer);

        $this->actingAs($admin)
            ->post(route('admin.orders.confirm-bank-transfer', $order))
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);
        $this->assertNotNull($order->fresh()->payment_confirmed_at);
    }

    public function test_customer_cannot_cancel_a_paid_bank_transfer_order_online(): void
    {
        $customer = User::factory()->create();
        $order = $this->createBankTransferOrder($customer);
        $order->update([
            'payment_status' => 'paid',
            'payment_confirmed_at' => now(),
            'status' => 'processing',
        ]);

        $this->actingAs($customer)
            ->post(route('orders.cancel', $order))
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'payment_status' => 'paid',
            'status' => 'processing',
        ]);
    }

    private function createBankTransferOrder(User $user): Order
    {
        return Order::query()->create([
            'order_number' => 'QN-20260921-001',
            'user_id' => $user->id,
            'status' => 'pending',
            'payment_method' => 'bank_transfer',
            'payment_status' => 'pending',
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
