<?php

namespace App\Services;

use App\Enums\OrderStatus;
use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Models\Address;
use App\Models\Cart;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class OrderService
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function placeFromCart(
        User $user,
        Cart $cart,
        ?int $addressId = null,
        ?array $manualAddress = null,
        ?string $customerNote = null,
    ): Order {
        $totals = $this->cartService->getTotals($cart);
        $cart->load('items.variant.product');
        if ($cart->items->isEmpty()) {
            throw new \InvalidArgumentException('Cart is empty.');
        }

        return DB::transaction(function () use ($user, $cart, $totals, $addressId, $manualAddress, $customerNote) {
            if ($totals['discount'] > 0 && $totals['coupon']) {
                $c = $totals['coupon'];
                $c->increment('used_count');
            }
            if ($addressId) {
                $addr = Address::query()->where('user_id', $user->id)->whereKey($addressId)->firstOrFail();
                $ship = $this->snapshotFromAddress($addr);
            } elseif (is_array($manualAddress)) {
                $ship = $this->normalizeManualAddress($manualAddress);
            } else {
                throw new \InvalidArgumentException('Shipping address is required.');
            }

            $order = Order::query()->create([
                'order_number' => $this->generateOrderNumber(),
                'user_id' => $user->id,
                'status' => OrderStatus::Pending->value,
                'payment_method' => 'cod',
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount'],
                'shipping' => $totals['shipping'],
                'total' => $totals['total'],
                'coupon_id' => $totals['coupon']?->id,
                'coupon_code' => $totals['coupon']?->code,
                'recipient_name' => $ship['recipient_name'],
                'phone' => $ship['phone'],
                'line1' => $ship['line1'],
                'line2' => $ship['line2'] ?? null,
                'city' => $ship['city'],
                'state' => $ship['state'] ?? null,
                'postal_code' => $ship['postal_code'],
                'country' => $ship['country'] ?? 'VN',
                'customer_note' => $customerNote,
            ]);

            foreach ($cart->items as $line) {
                $v = $line->variant;
                if (! $v) {
                    continue;
                }
                if ($v->stock < $line->quantity) {
                    throw new \RuntimeException('Insufficient stock for '.$v->product->name);
                }
                $unit = (float) $v->unitPriceCents();
                $name = $v->product->name;
                $lineTotal = $unit * (int) $line->quantity;
                OrderItem::query()->create([
                    'order_id' => $order->id,
                    'product_id' => $v->product_id,
                    'product_variant_id' => $v->id,
                    'name' => $name,
                    'sku' => $v->sku,
                    'size' => $v->size,
                    'color' => $v->color,
                    'unit_price' => $unit,
                    'quantity' => (int) $line->quantity,
                    'line_total' => $lineTotal,
                ]);
                $v->decrement('stock', (int) $line->quantity);
            }
            $cart->items()->delete();
            $cart->update(['applied_coupon_code' => null]);
            $placed = $order->fresh(['user', 'items']);
            event(new OrderPlaced($placed));

            return $placed;
        });
    }

    public function updateStatus(Order $order, string $newStatus, ?string $tracking = null, ?string $adminNote = null): void
    {
        $prev = $order->status;
        if ($adminNote !== null) {
            $order->admin_note = $adminNote;
        }
        if ($tracking !== null) {
            $order->tracking_number = $tracking;
        }
        $statusChanged = $order->status !== $newStatus;
        $order->status = $newStatus;
        $order->save();
        if ($statusChanged) {
            event(new OrderStatusChanged($order->fresh(), $prev));
        }
    }

    public function cancelByUser(Order $order): void
    {
        if (! in_array($order->status, [OrderStatus::Pending->value, OrderStatus::Processing->value], true)) {
            throw new \RuntimeException('Order cannot be cancelled.');
        }
        $this->restockAndCancel($order);
    }

    public function restockAndCancel(Order $order): void
    {
        DB::transaction(function () use ($order) {
            if ($order->status === OrderStatus::Cancelled->value) {
                return;
            }
            $prev = $order->status;
            foreach ($order->items as $oid) {
                if ($oid->product_variant_id) {
                    \App\Models\ProductVariant::query()->whereKey($oid->product_variant_id)
                        ->increment('stock', $oid->quantity);
                }
            }
            if ($order->coupon_id) {
                $c = Coupon::query()->find($order->coupon_id);
                if ($c && $c->used_count > 0) {
                    $c->decrement('used_count');
                }
            }
            $order->status = OrderStatus::Cancelled->value;
            $order->save();
            event(new OrderStatusChanged($order->fresh(), $prev));
        });
    }

    private function generateOrderNumber(): string
    {
        return 'FF-'.now()->format('Ymd').'-'.Str::upper(Str::random(6));
    }

    private function snapshotFromAddress(Address $addr): array
    {
        return [
            'recipient_name' => $addr->full_name,
            'phone' => $addr->phone,
            'line1' => $addr->line1,
            'line2' => $addr->line2,
            'city' => $addr->city,
            'state' => $addr->state,
            'postal_code' => $addr->postal_code,
            'country' => $addr->country,
        ];
    }

    private function normalizeManualAddress(array $data): array
    {
        return [
            'recipient_name' => $data['recipient_name'],
            'phone' => $data['phone'],
            'line1' => $data['line1'],
            'line2' => $data['line2'] ?? null,
            'city' => $data['city'],
            'state' => $data['state'] ?? null,
            'postal_code' => $data['postal_code'],
            'country' => $data['country'] ?? 'VN',
        ];
    }
}
