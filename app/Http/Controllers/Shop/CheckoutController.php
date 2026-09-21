<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Services\CartService;
use App\Services\BankTransferPaymentService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function __construct(
        private CartService $cartService,
        private OrderService $orderService,
        private BankTransferPaymentService $bankTransferPaymentService,
    ) {}

    public function create(): View|RedirectResponse
    {
        $cart = $this->cartService->getOrCreateForUser(auth()->id())->load(['items.variant.product']);
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }
        $totals = $this->cartService->getTotals($cart);
        $addresses = auth()->user()->addresses()->orderByDesc('is_default')->get();

        $bankTransferAvailable = $this->bankTransferPaymentService->isConfigured();

        return view('shop.checkout', compact('cart', 'totals', 'addresses', 'bankTransferAvailable'));
    }

    public function store(Request $request): RedirectResponse
    {
        $user = $request->user();
        if ($user->is_blocked) {
            abort(403);
        }
        $rules = [
            'address_id' => 'nullable|exists:addresses,id',
            'recipient_name' => 'required_without:address_id|nullable|string|max:120',
            'phone' => 'required_without:address_id|nullable|string|max:32',
            'line1' => 'required_without:address_id|nullable|string|max:255',
            'line2' => 'nullable|string|max:255',
            'city' => 'required_without:address_id|nullable|string|max:120',
            'state' => 'nullable|string|max:120',
            'postal_code' => 'required_without:address_id|nullable|string|max:32',
            'country' => 'nullable|string|size:2',
            'customer_note' => 'nullable|string|max:1000',
            'payment_method' => 'required|in:cod,bank_transfer',
        ];
        $data = $request->validate($rules);
        if ($data['payment_method'] === 'bank_transfer' && ! $this->bankTransferPaymentService->isConfigured()) {
            return back()->with('error', 'Bank transfer is not available yet.')->withInput();
        }
        if (! empty($data['address_id'])) {
            if (! $user->addresses()->whereKey($data['address_id'])->exists()) {
                return back()->with('error', 'Invalid address.')->withInput();
            }
        } else {
            if (empty($data['recipient_name'])) {
                return back()->with('error', 'Select a saved address or enter shipping details.')->withInput();
            }
        }
        $cart = $this->cartService->getOrCreateForUser($user->id);
        if ($cart->items->isEmpty()) {
            return redirect()->route('cart.index')->with('error', 'Your cart is empty.');
        }
        try {
            $order = $this->orderService->placeFromCart(
                $user,
                $cart,
                ! empty($data['address_id']) ? (int) $data['address_id'] : null,
                empty($data['address_id']) ? [
                    'recipient_name' => $data['recipient_name'],
                    'phone' => $data['phone'],
                    'line1' => $data['line1'],
                    'line2' => $data['line2'] ?? null,
                    'city' => $data['city'],
                    'state' => $data['state'] ?? null,
                    'postal_code' => $data['postal_code'],
                    'country' => $data['country'] ?? 'VN',
                ] : null,
                $data['customer_note'] ?? null,
                $data['payment_method'],
            );
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage())->withInput();
        }

        return redirect()->route('orders.show', $order)->with('status', 'Order placed. Thank you!');
    }
}
