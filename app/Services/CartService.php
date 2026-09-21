<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CartService
{
    public function currentCart(): Cart
    {
        if (auth()->check()) {
            return $this->getOrCreateForUser(auth()->id());
        }

        return $this->getOrCreateForSession(session()->getId());
    }

    public function getOrCreateForUser(int $userId): Cart
    {
        return Cart::query()->firstOrCreate(
            ['user_id' => $userId],
            ['session_id' => null],
        );
    }

    public function getOrCreateForSession(string $sessionId): Cart
    {
        return Cart::query()->firstOrCreate(
            ['session_id' => $sessionId, 'user_id' => null],
        );
    }

    public function mergeGuestCartToUser(User $user): void
    {
        $sessionId = session()->getId();
        $guest = Cart::query()->where('session_id', $sessionId)->whereNull('user_id')->first();
        if (! $guest || $guest->items->isEmpty()) {
            return;
        }
        $userCart = $this->getOrCreateForUser($user->id);
        DB::transaction(function () use ($guest, $userCart) {
            foreach ($guest->items as $item) {
                $existing = $userCart->items()->where('product_variant_id', $item->product_variant_id)->first();
                if ($existing) {
                    $existing->update(['quantity' => $existing->quantity + $item->quantity]);
                } else {
                    CartItem::query()->create([
                        'cart_id' => $userCart->id,
                        'product_variant_id' => $item->product_variant_id,
                        'quantity' => $item->quantity,
                    ]);
                }
            }
            if ($guest->applied_coupon_code && ! $userCart->applied_coupon_code) {
                $userCart->update(['applied_coupon_code' => $guest->applied_coupon_code]);
            }
            $guest->items()->delete();
            $guest->delete();
        });
    }

    public function addLine(Cart $cart, ProductVariant $variant, int $qty = 1): void
    {
        if ($qty < 1) {
            $qty = 1;
        }
        $row = $cart->items()->where('product_variant_id', $variant->id)->first();
        if ($row) {
            $row->update(['quantity' => min($row->quantity + $qty, $variant->stock)]);

            return;
        }
        $cart->items()->create([
            'product_variant_id' => $variant->id,
            'quantity' => min($qty, $variant->stock),
        ]);
    }

    public function setQuantity(CartItem $item, int $qty): void
    {
        $variant = $item->variant;
        if ($qty < 1) {
            $item->delete();

            return;
        }
        $item->update(['quantity' => min($qty, $variant->stock)]);
    }

    /**
     * @return array{subtotal: float, discount: float, shipping: float, total: float, coupon: ?Coupon, is_free_shipping: bool, free_shipping_threshold: float, remaining_for_free_shipping: float}
     */
    public function getTotals(Cart $cart): array
    {
        $cart->load('items.variant.product');
        $subtotal = 0.0;
        foreach ($cart->items as $line) {
            $v = $line->variant;
            if (! $v) {
                continue;
            }
            $unit = (float) $v->unitPriceCents();
            $subtotal += $unit * (int) $line->quantity;
        }
        $discount = 0.0;
        $coupon = null;
        if ($cart->applied_coupon_code) {
            $coupon = Coupon::query()->where('code', $cart->applied_coupon_code)->first();
            if ($coupon) {
                $discount = $coupon->discountForSubtotal($subtotal);
            } else {
                $discount = 0.0;
            }
        }
        $freeShippingThreshold = (float) config('shop.free_shipping_threshold');
        $isFreeShipping = $freeShippingThreshold > 0 && $subtotal >= $freeShippingThreshold;
        $shipping = $isFreeShipping ? 0.0 : (float) config('shop.default_shipping');
        $total = max(0, $subtotal - $discount) + $shipping;

        return [
            'subtotal' => $subtotal,
            'discount' => $discount,
            'shipping' => $shipping,
            'total' => $total,
            'coupon' => $discount > 0 ? $coupon : null,
            'is_free_shipping' => $isFreeShipping,
            'free_shipping_threshold' => $freeShippingThreshold,
            'remaining_for_free_shipping' => max(0, $freeShippingThreshold - $subtotal),
        ];
    }
}
