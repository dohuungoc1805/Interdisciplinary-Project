@extends('layouts.shop')

@section('title', 'Đơn mua — '.config('app.name'))

@section('content')
    <div class="nd-shop-page-head">
        <h1 class="nd-shop-page-title">Danh sách đơn mua</h1>
        <p class="nd-shop-page-lead">Theo dõi đơn đã đặt, trạng thái giao hàng và xem chi tiết sản phẩm đã mua.</p>
    </div>

    <div class="mx-auto max-w-3xl space-y-4">
        <form method="get" class="flex flex-wrap items-center gap-2" aria-label="Lọc đơn mua">
            <label for="order-status" class="text-sm font-medium text-[color:var(--primary)]">Trạng thái</label>
            <select id="order-status" name="status" class="field-control w-auto min-w-44" onchange="this.form.submit()">
                <option value="">Tất cả đơn hàng</option>
                @foreach(\App\Enums\OrderStatus::cases() as $orderStatus)
                    <option value="{{ $orderStatus->value }}" @selected($status === $orderStatus->value)>{{ $orderStatus->label() }}</option>
                @endforeach
            </select>
        </form>

        @forelse($orders as $o)
            <a href="{{ route('orders.show', $o) }}" class="shop-shell flex flex-col gap-3 p-4 transition hover:-translate-y-0.5 hover:shadow-[var(--shadow-card-lg)] sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <span class="font-bold text-[color:var(--primary)]">{{ $o->order_number }}</span>
                    <p class="mt-1 text-sm text-[color:var(--text-muted)]">{{ $o->created_at?->format('d/m/Y H:i') }}</p>
                    <p class="mt-0.5 text-xs text-[color:var(--text-muted)]">{{ $o->items_count }} sản phẩm trong đơn</p>
                </div>
                <div class="flex flex-wrap items-center gap-3 text-sm">
                    <span class="inline-flex rounded-full bg-[#f7f7f9] px-3 py-1 font-medium text-[color:var(--primary)]">{{ $o->statusEnum()?->label() ?? $o->status }}</span>
                    <span class="font-bold text-[color:var(--accent)]">{{ number_format((float) $o->total, 0, ',', '.') }} ₫</span>
                </div>
            </a>
        @empty
            <div class="shop-shell p-10 text-center text-[color:var(--text-muted)]">
                Chưa có đơn mua. <a href="{{ route('products.index') }}" class="font-semibold text-[color:var(--accent)] hover:underline">Bắt đầu mua sắm</a>
            </div>
        @endforelse

        @if($orders->hasPages())
            <div class="pt-4">{{ $orders->links() }}</div>
        @endif
    </div>
@endsection
