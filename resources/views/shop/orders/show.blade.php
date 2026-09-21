@extends('layouts.shop')

@section('title', $order->order_number.' — '.config('app.name'))

@section('content')
    <a href="{{ route('purchases.index') }}" class="text-sm font-medium text-[color:var(--accent)] hover:underline">&larr; Danh sách đơn mua</a>
    <h1 class="shop-heading mt-3 text-2xl font-bold text-[color:var(--primary)]">{{ $order->order_number }}</h1>
    <p class="mt-2 text-[color:var(--text-muted)]">
        Trạng thái: <strong class="text-[color:var(--primary)]">{{ $order->statusEnum()?->label() ?? $order->status }}</strong>
        @if($order->tracking_number) — Mã vận đơn: {{ $order->tracking_number }}@endif
    </p>
    <x-order-status-timeline :status="$order->status" />
    <div class="shop-shell mt-6 space-y-1 p-4 text-sm">
        <p class="text-[color:var(--primary)]">{{ $order->recipient_name }} — {{ $order->phone }}</p>
        <p>{{ $order->line1 }}@if($order->line2), {{ $order->line2 }}@endif</p>
        <p>{{ $order->city }} {{ $order->postal_code }} {{ $order->country }}</p>
    </div>
    <h2 class="mt-8 font-semibold text-[color:var(--primary)]">Sản phẩm</h2>
    <ul class="shop-shell mt-2 divide-y divide-[color:var(--line-soft)]">
        @foreach($order->items as $i)
            <li class="flex justify-between gap-4 p-4 text-sm">
                <span>{{ $i->name }} @if($i->size) ({{ $i->size }} / {{ $i->color }}) @endif × {{ $i->quantity }}</span>
                <span class="shrink-0 font-semibold text-[color:var(--accent)]">{{ number_format((float) $i->line_total, 0, ',', '.') }} ₫</span>
            </li>
        @endforeach
    </ul>
    <p class="mt-4 text-right text-lg font-bold text-[color:var(--primary)]">Tổng cộng: {{ number_format((float) $order->total, 0, ',', '.') }} ₫ (COD)</p>
    <section class="shop-shell mt-6 p-5" aria-labelledby="payment-title">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <h2 id="payment-title" class="font-semibold text-[color:var(--primary)]">Thanh toán</h2>
                <p class="mt-1 text-sm text-[color:var(--text-muted)]">{{ $order->paymentMethodLabel() }}</p>
            </div>
            <span @class([
                'px-3 py-1 text-xs font-semibold',
                'bg-emerald-100 text-emerald-800' => $order->payment_status === 'paid',
                'bg-amber-100 text-amber-900' => $order->payment_status !== 'paid',
            ])>{{ $order->paymentStatusLabel() }}</span>
        </div>
        @if($order->payment_method === 'bank_transfer' && $order->payment_status !== 'paid')
            @if($bankTransferDetails)
                <div class="mt-5 grid gap-6 sm:grid-cols-[300px_1fr] sm:items-center">
                    <img src="{{ $bankTransferDetails['qr_url'] }}" alt="Mã QR chuyển khoản cho đơn {{ $order->order_number }}" class="mx-auto w-full max-w-[300px] border border-[color:var(--line-soft)] bg-white p-3" />
                    <dl class="space-y-2 text-sm">
                        <div class="flex flex-wrap justify-between gap-2"><dt class="text-[color:var(--text-muted)]">Ngân hàng</dt><dd class="font-semibold text-[color:var(--primary)]">{{ $bankTransferDetails['bank_name'] }}</dd></div>
                        <div class="flex flex-wrap justify-between gap-2"><dt class="text-[color:var(--text-muted)]">Số tài khoản</dt><dd class="font-semibold text-[color:var(--primary)]">{{ $bankTransferDetails['account_number'] }}</dd></div>
                        <div class="flex flex-wrap justify-between gap-2"><dt class="text-[color:var(--text-muted)]">Chủ tài khoản</dt><dd class="font-semibold text-[color:var(--primary)]">{{ $bankTransferDetails['account_name'] }}</dd></div>
                        <div class="flex flex-wrap justify-between gap-2"><dt class="text-[color:var(--text-muted)]">Số tiền</dt><dd class="font-bold text-[color:var(--accent)]">{{ number_format($bankTransferDetails['amount'], 0, ',', '.') }} ₫</dd></div>
                        <div class="flex flex-wrap justify-between gap-2"><dt class="text-[color:var(--text-muted)]">Nội dung chuyển khoản</dt><dd class="font-mono font-semibold text-[color:var(--primary)]">{{ $bankTransferDetails['transfer_content'] }}</dd></div>
                    </dl>
                </div>
                <p class="mt-5 text-xs leading-relaxed text-[color:var(--text-muted)]">Vui lòng không thay đổi số tiền hoặc nội dung chuyển khoản. Cửa hàng sẽ kiểm tra giao dịch trước khi chuẩn bị đơn.</p>
            @else
                <p class="mt-3 text-sm text-[color:var(--text-muted)]">Thông tin chuyển khoản đang được cửa hàng cập nhật. Vui lòng liên hệ cửa hàng để được hỗ trợ.</p>
            @endif
        @elseif($order->payment_method === 'bank_transfer')
            <p class="mt-3 text-sm text-emerald-700">Cửa hàng đã xác nhận thanh toán lúc {{ $order->payment_confirmed_at?->format('d/m/Y H:i') }}.</p>
        @endif
    </section>
    @if($order->status === 'pending' || $order->status === 'processing')
        <form method="post" action="{{ route('orders.cancel', $order) }}" class="mt-6">@csrf
            <button class="text-sm font-semibold text-red-600 hover:underline" type="submit" onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">Hủy đơn hàng</button>
        </form>
    @endif
@endsection
