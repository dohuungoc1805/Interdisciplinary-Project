<?php

namespace App\Http\Controllers\Shop;

use App\Enums\CouponKind;
use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(private CartService $cartService) {}

    public function index(): View
    {
        $cart = $this->cartService->currentCart()->load(['items.variant.product.images']);
        $totals = $this->cartService->getTotals($cart);
        $publicCoupons = Coupon::query()->where('is_active', true)->orderBy('kind')->orderBy('code')->get();

        return view('shop.cart', compact('cart', 'totals', 'publicCoupons'));
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'product_variant_id' => 'required|exists:product_variants,id',
            'quantity' => 'integer|min:1|max:99',
        ]);
        $variant = ProductVariant::query()->with('product')->findOrFail($data['product_variant_id']);
        if (! $variant->product->is_active) {
            return back()->with('error', 'This product is unavailable.');
        }
        $qty = $data['quantity'] ?? 1;
        if ($variant->stock < 1) {
            return back()->with('error', 'Out of stock for this option.');
        }
        $cart = $this->cartService->currentCart();
        if (auth()->check() && $cart->user_id !== auth()->id()) {
            $cart = $this->cartService->getOrCreateForUser(auth()->id());
        }
        if (auth()->check() && ! $cart->user_id) {
            $cart->user_id = auth()->id();
            $cart->session_id = null;
            $cart->save();
        }
        $this->cartService->addLine($cart, $variant, $qty);

        return back()->with('status', 'Added to cart.');
    }

    public function update(Request $request, CartItem $item): RedirectResponse
    {
        $cart = $this->cartService->currentCart();
        if ((int) $item->cart_id !== (int) $cart->id) {
            abort(403);
        }
        $data = $request->validate(['quantity' => 'required|integer|min:0|max:99']);
        $this->cartService->setQuantity($item, (int) $data['quantity']);

        return back()->with('status', 'Cart updated.');
    }

    public function applyCoupon(Request $request): RedirectResponse|JsonResponse
    {
        $data = $request->validate(['code' => 'required|string|max:32']);
        $code = strtoupper(trim($data['code']));
        $coupon = Coupon::query()->where('code', $code)->first();
        $cart = $this->cartService->currentCart();
        $totals = $this->cartService->getTotals($cart);
        if (! $coupon) {
            return $this->couponError($request, 'Mã giảm giá không tồn tại.');
        }
        if ($message = $coupon->validationErrorForAmount($totals['subtotal'])) {
            return $this->couponError($request, $message);
        }
        $discount = $coupon->isShipping()
            ? $coupon->discountForAmount($totals['base_shipping'])
            : $coupon->discountForSubtotal($totals['subtotal']);
        if ($discount <= 0) {
            return $this->couponError($request, 'Mã giảm giá này chưa có giá trị giảm hợp lệ.');
        }
        $cart->update($coupon->isShipping()
            ? ['applied_shipping_coupon_code' => $code]
            : ['applied_coupon_code' => $code]);

        if ($request->expectsJson()) {
            return response()->json($this->couponPayload($cart));
        }

        return back()->with('status', 'Đã áp dụng mã giảm giá.');
    }

    public function removeCoupon(Request $request): RedirectResponse|JsonResponse
    {
        $cart = $this->cartService->currentCart();
        $kind = $request->string('kind')->toString();
        $cart->update($kind === CouponKind::Shipping->value
            ? ['applied_shipping_coupon_code' => null]
            : ['applied_coupon_code' => null]);

        if ($request->expectsJson()) {
            return response()->json($this->couponPayload($cart));
        }

        return back()->with('status', 'Đã bỏ mã giảm giá.');
    }

    private function couponError(Request $request, string $message): RedirectResponse|JsonResponse
    {
        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 422);
        }

        return back()->with('error', $message);
    }

    private function couponPayload(Cart $cart): array
    {
        $totals = $this->cartService->getTotals($cart);

        return [
            'message' => 'Cập nhật voucher thành công.',
            'product_code' => $cart->applied_coupon_code,
            'shipping_code' => $cart->applied_shipping_coupon_code,
            'subtotal' => $totals['subtotal'],
            'discount' => $totals['discount'],
            'shipping_discount' => $totals['shipping_discount'],
            'shipping' => $totals['shipping'],
            'total' => $totals['total'],
        ];
    }
}
