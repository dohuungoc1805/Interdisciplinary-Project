<?php

namespace Database\Seeders;

use App\Enums\OrderStatus;
use App\Models\Address;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\NewsletterSubscription;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Review;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $customer = User::query()->where('email', 'customer@example.com')->firstOrFail();

        $shirt = Product::query()->where('slug', 'linen-shirt')->firstOrFail();
        $dress = Product::query()->where('slug', 'midi-dress')->firstOrFail();

        $img = 'images/shop/products/polo.png';

        ProductImage::query()->where('product_id', $shirt->id)->delete();
        ProductImage::query()->create([
            'product_id' => $shirt->id,
            'path' => $img,
            'position' => 1,
        ]);

        Address::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'line1' => '123 Đường Nguyễn Huệ',
            ],
            [
                'full_name' => 'Khách hàng mẫu',
                'phone' => '0901234567',
                'line2' => 'Phường Bến Nghé',
                'city' => 'Thành phố Hồ Chí Minh',
                'state' => null,
                'postal_code' => '700000',
                'country' => 'VN',
                'is_default' => true,
            ]
        );

        $variantShirt = ProductVariant::query()
            ->where('product_id', $shirt->id)
            ->where('size', 'M')
            ->where('color', 'Trắng')
            ->firstOrFail();

        $variantDress = ProductVariant::query()
            ->where('product_id', $dress->id)
            ->where('size', 'S')
            ->where('color', 'Đen')
            ->firstOrFail();

        $coupon = Coupon::query()->where('code', 'WELCOME10')->first();

        $unitShirt = (float) $variantShirt->unitPriceCents();
        $qtyShirt = 1;
        $lineShirt = $unitShirt * $qtyShirt;
        $shipping = 30000.0;
        $discount = 59900.0;
        $subtotal = $lineShirt;
        $total = $subtotal + $shipping - $discount;

        $order = Order::query()->updateOrCreate(
            ['order_number' => 'FF-SEED-000001'],
            [
                'user_id' => $customer->id,
                'status' => OrderStatus::Completed->value,
                'payment_method' => 'cod',
                'subtotal' => $subtotal,
                'discount_total' => $discount,
                'shipping' => $shipping,
                'total' => $total,
                'coupon_id' => $coupon?->id,
                'coupon_code' => $coupon?->code,
                'recipient_name' => 'Khách hàng mẫu',
                'phone' => '0901234567',
                'line1' => '123 Đường Nguyễn Huệ',
                'line2' => 'Phường Bến Nghé',
                'city' => 'Thành phố Hồ Chí Minh',
                'state' => null,
                'postal_code' => '700000',
                'country' => 'VN',
                'customer_note' => null,
                'tracking_number' => 'TRACK-SEED-001',
            ]
        );

        $order->items()->delete();
        OrderItem::query()->create([
            'order_id' => $order->id,
            'product_id' => $shirt->id,
            'product_variant_id' => $variantShirt->id,
            'name' => $shirt->name,
            'sku' => $variantShirt->sku,
            'size' => $variantShirt->size,
            'color' => $variantShirt->color,
            'unit_price' => $unitShirt,
            'quantity' => $qtyShirt,
            'line_total' => $lineShirt,
        ]);

        Review::query()->updateOrCreate(
            [
                'user_id' => $customer->id,
                'product_id' => $shirt->id,
            ],
            [
                'order_id' => $order->id,
                'rating' => 5,
                'comment' => 'Vải mát, form chuẩn size, giao hàng nhanh. Rất hài lòng!',
                'is_approved' => true,
            ]
        );

        $shirt->recalculateReviewStats();
        $dress->recalculateReviewStats();

        WishlistItem::query()->firstOrCreate(
            [
                'user_id' => $customer->id,
                'product_id' => $dress->id,
            ]
        );

        NewsletterSubscription::query()->updateOrCreate(
            ['email' => 'newsletter@example.com'],
            [
                'unsubscribe_token' => hash('sha256', 'newsletter-seed-token'),
                'is_active' => true,
            ]
        );

        $cart = Cart::query()->firstOrCreate(
            ['user_id' => $customer->id],
            ['session_id' => null, 'applied_coupon_code' => null]
        );
        $cart->items()->delete();
        CartItem::query()->create([
            'cart_id' => $cart->id,
            'product_variant_id' => $variantDress->id,
            'quantity' => 2,
        ]);
    }
}
