@extends('layouts.shop')

@section('title', $order->order_number.' — '.config('app.name'))

@section('content')
    <a href="{{ route('purchases.index') }}" class="text-sm font-medium text-[color:var(--accent)] hover:underline">&larr; Danh sách đơn mua</a>
    <h1 class="shop-heading mt-3 text-2xl font-bold text-[color:var(--primary)]">{{ $order->order_number }}</h1>
    <p class="mt-2 text-[color:var(--text-muted)]">
        Trạng thái: <strong class="text-[color:var(--primary)]">{{ $order->statusEnum()?->label() ?? $order->status }}</strong>
        @if($order->tracking_number) — Mã vận đơn: {{ $order->tracking_number }}@endif
    </p>
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
    @if($order->status === 'pending' || $order->status === 'processing')
        <form method="post" action="{{ route('orders.cancel', $order) }}" class="mt-6">@csrf
            <button class="text-sm font-semibold text-red-600 hover:underline" type="submit" onclick="return confirm('Bạn có chắc muốn hủy đơn hàng này?')">Hủy đơn hàng</button>
        </form>
    @endif
@endsection
