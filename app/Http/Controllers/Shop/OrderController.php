<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\BankTransferPaymentService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function index(Request $request): View
    {
        $status = $request->string('status')->toString();
        $validStatuses = array_column(OrderStatus::cases(), 'value');
        $ordersQuery = auth()->user()->orders()->withCount('items')->latest();

        if (in_array($status, $validStatuses, true)) {
            $ordersQuery->where('status', $status);
        }

        $orders = $ordersQuery->paginate(10)->withQueryString();

        return view('shop.orders.index', compact('orders', 'status', 'validStatuses'));
    }

    public function show(Order $order, BankTransferPaymentService $bankTransferPaymentService): View
    {
        $this->authorizeOrder($order);
        $order->load('items');
        $bankTransferDetails = $order->payment_method === 'bank_transfer'
            ? $bankTransferPaymentService->detailsFor($order)
            : null;

        return view('shop.orders.show', compact('order', 'bankTransferDetails'));
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
