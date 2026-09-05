<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Coupon;
use App\Models\ProductVariant;
use App\Services\CartService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function __construct(
        private CartService $cartService,
    ) {}

    public function index(): View
    {
        $cart = $this->cartService->currentCart()->load(['items.variant.product.images']);
        $totals = $this->cartService->getTotals($cart);

        return view('shop.cart', compact('cart', 'totals'));
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

    public function applyCoupon(Request $request): RedirectResponse
    {
        $data = $request->validate(['code' => 'required|string|max:32']);
        $code = strtoupper(trim($data['code']));
        $coupon = Coupon::query()->where('code', $code)->first();
        $cart = $this->cartService->currentCart();
        $totals = $this->cartService->getTotals($cart);
        if (! $coupon) {
            return back()->with('error', 'Mã giảm giá không tồn tại.');
        }
        if ($message = $coupon->validationErrorForAmount($totals['subtotal'])) {
            return back()->with('error', $message);
        }
        if ($coupon->discountForSubtotal($totals['subtotal']) <= 0) {
            return back()->with('error', 'Mã giảm giá này chưa có giá trị giảm hợp lệ.');
        }
        $cart->update(['applied_coupon_code' => $code]);

        return back()->with('status', 'Đã áp dụng mã giảm giá.');
    }

    public function removeCoupon(): RedirectResponse
    {
        $cart = $this->cartService->currentCart();
        $cart->update(['applied_coupon_code' => null]);

        return back()->with('status', 'Đã bỏ mã giảm giá.');
    }
}
