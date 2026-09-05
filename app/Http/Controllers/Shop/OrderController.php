<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function index(): View
    {
        $orders = auth()->user()->orders()->withCount('items')->latest()->paginate(10);

        return view('shop.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $this->authorizeOrder($order);
        $order->load('items');

        return view('shop.orders.show', compact('order'));
    }

    public function cancel(Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);
        try {
            $this->orderService->cancelByUser($order);
        } catch (\Throwable $e) {
            return back()->with('error', $e->getMessage());
        }

        return back()->with('status', 'Đơn hàng đã được hủy.');
    }

    private function authorizeOrder(Order $order): void
    {
        if ((int) $order->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }
}
