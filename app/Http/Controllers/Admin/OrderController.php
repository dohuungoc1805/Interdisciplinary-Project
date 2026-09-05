<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OrderController extends Controller
{
    public function __construct(
        private OrderService $orderService,
    ) {}

    public function index(Request $request): View
    {
        $q = Order::query()->with('user')->latest();
        if ($request->filled('status')) {
            $q->where('status', $request->string('status'));
        }
        if ($request->filled('q')) {
            $s = $request->string('q');
            $q->where(function ($q2) use ($s) {
                $q2->where('order_number', 'like', '%'.$s.'%')
                    ->orWhereHas('user', fn ($u) => $u->where('email', 'like', '%'.$s.'%'));
            });
        }
        $orders = $q->paginate(25)->withQueryString();

        return view('admin.orders.index', compact('orders'));
    }

    public function show(Order $order): View
    {
        $order->load(['user', 'items', 'coupon']);

        return view('admin.orders.show', compact('order'));
    }

    public function update(Request $request, Order $order): RedirectResponse
    {
        $data = $request->validate([
            'status' => 'required|string|in:'.implode(',', array_column(OrderStatus::cases(), 'value')),
            'tracking_number' => 'nullable|string|max:64',
            'admin_note' => 'nullable|string|max:2000',
        ]);
        $this->orderService->updateStatus(
            $order,
            $data['status'],
            $data['tracking_number'] ?? null,
            $data['admin_note'] ?? null,
        );

        return back()->with('status', 'Đã cập nhật đơn hàng.');
    }

    public function bulkStatus(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'order_ids' => 'required|array',
            'order_ids.*' => 'integer|exists:orders,id',
            'status' => 'required|string|in:'.implode(',', array_column(OrderStatus::cases(), 'value')),
        ]);
        foreach (Order::query()->whereIn('id', $data['order_ids'])->get() as $order) {
            $this->orderService->updateStatus($order, $data['status']);
        }

        return back()->with('status', 'Đã cập nhật các đơn hàng.');
    }

    public function export(Request $request): StreamedResponse|Response
    {
        $from = $request->date('from')?->startOfDay() ?? now()->subMonth()->startOfDay();
        $to = $request->date('to')?->endOfDay() ?? now()->endOfDay();
        $name = 'orders-'.$from->format('Ymd').'-'.$to->format('Ymd').'.csv';
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="'.$name.'"',
        ];
        $orders = Order::query()
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->with('user')
            ->orderBy('id')
            ->get();

        return response()->streamDownload(function () use ($orders) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['order_number', 'date', 'email', 'status', 'total', 'payment']);
            foreach ($orders as $o) {
                fputcsv($out, [
                    $o->order_number,
                    $o->created_at?->toDateTimeString(),
                    $o->user?->email,
                    $o->status,
                    $o->total,
                    $o->payment_method,
                ]);
            }
            fclose($out);
        }, $name, $headers);
    }
}
