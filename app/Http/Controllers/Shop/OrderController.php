<?php

namespace App\Http\Controllers\Shop;

use App\Http\Controllers\Controller;
use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Review;
use App\Services\BankTransferPaymentService;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\Storage;

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
        $order->load('items.product');
        $reviewedProductIds = Review::query()
            ->where('user_id', auth()->id())
            ->whereIn('product_id', $order->items->pluck('product_id')->filter()->unique())
            ->pluck('product_id')
            ->all();
        $bankTransferDetails = $order->payment_method === 'bank_transfer'
            ? $bankTransferPaymentService->detailsFor($order)
            : null;

        return view('shop.orders.show', compact('order', 'bankTransferDetails', 'reviewedProductIds'));
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

    public function uploadPaymentProof(Request $request, Order $order): RedirectResponse
    {
        $this->authorizeOrder($order);
        if ($order->payment_method !== 'bank_transfer') {
            return back()->with('error', 'Đơn hàng này không sử dụng hình thức chuyển khoản.');
        }
        if ($order->payment_status === 'paid') {
            return back()->with('error', 'Đơn hàng này đã được xác nhận thanh toán.');
        }

        $data = $request->validate([
            'payment_proof' => 'required|image|mimes:jpg,jpeg,png,webp|max:8192',
        ]);
        $oldPath = $order->payment_proof_path;
        $newPath = $data['payment_proof']->store('payment-proofs', 'public');
        $order->update([
            'payment_proof_path' => $newPath,
            'payment_proof_submitted_at' => now(),
        ]);
        if ($oldPath && ! preg_match('#^https?://#i', (string) $oldPath)) {
            Storage::disk('public')->delete($oldPath);
        }

        return back()->with('status', 'Đã tải ảnh giao dịch. Cửa hàng sẽ kiểm tra và xác nhận thanh toán cho bạn.');
    }

    private function authorizeOrder(Order $order): void
    {
        if ((int) $order->user_id !== (int) auth()->id()) {
            abort(403);
        }
    }
}
