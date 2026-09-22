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
use App\Models\ProductVariant;
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
        string $paymentMethod = 'cod',
    ): Order {
        $totals = $this->cartService->getTotals($cart);
        $cart->load('items.variant.product');
        if ($cart->items->isEmpty()) {
            throw new \InvalidArgumentException('Cart is empty.');
        }

        return DB::transaction(function () use ($user, $cart, $totals, $addressId, $manualAddress, $customerNote, $paymentMethod) {
            if ($totals['discount'] > 0 && $totals['coupon']) {
                $c = $totals['coupon'];
                $c->increment('used_count');
            }
            if ($totals['shipping_discount'] > 0 && $totals['shipping_coupon']) {
                $totals['shipping_coupon']->increment('used_count');
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
                'payment_method' => $paymentMethod,
                'payment_status' => 'pending',
                'subtotal' => $totals['subtotal'],
                'discount_total' => $totals['discount'],
                'shipping_discount_total' => $totals['shipping_discount'],
                'shipping' => $totals['shipping'],
                'total' => $totals['total'],
                'coupon_id' => $totals['coupon']?->id,
                'coupon_code' => $totals['coupon']?->code,
                'shipping_coupon_id' => $totals['shipping_coupon']?->id,
                'shipping_coupon_code' => $totals['shipping_coupon']?->code,
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
                $v = ProductVariant::query()
                    ->with('product')
                    ->lockForUpdate()
                    ->find($line->product_variant_id);
                if (! $v || ! $v->product) {
                    throw new \RuntimeException('A product in your cart is no longer available.');
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
            $cart->update(['applied_coupon_code' => null, 'applied_shipping_coupon_code' => null]);
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

    public function confirmBankTransferPayment(Order $order): void
    {
        $result = DB::transaction(function () use ($order) {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->id);
            if ($lockedOrder->payment_method !== 'bank_transfer') {
                throw new \RuntimeException('Only bank transfer payments can be confirmed here.');
            }
            if ($lockedOrder->payment_status === 'paid') {
                return [$lockedOrder, null];
            }

            $previousStatus = $lockedOrder->status;
            $lockedOrder->payment_status = 'paid';
            $lockedOrder->payment_confirmed_at = now();
            if ($lockedOrder->status === OrderStatus::Pending->value) {
                $lockedOrder->status = OrderStatus::Processing->value;
            }
            $lockedOrder->save();

            return [$lockedOrder->fresh(), $lockedOrder->status !== $previousStatus ? $previousStatus : null];
        });

        if ($result[1] !== null) {
            event(new OrderStatusChanged($result[0], $result[1]));
        }
    }

    public function cancelByUser(Order $order): void
    {
        if ($order->payment_method === 'bank_transfer' && $order->payment_status === 'paid') {
            throw new \RuntimeException('A paid bank transfer order cannot be cancelled online. Please contact the store for support.');
        }
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
            if ($order->shipping_coupon_id) {
                $c = Coupon::query()->find($order->shipping_coupon_id);
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
