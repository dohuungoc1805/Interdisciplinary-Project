@extends('layouts.shop')

@section('title', 'Giỏ hàng — '.config('app.name'))

@section('content')
    <div class="nd-shop-page-head">
        <h1 class="nd-shop-page-title">Giỏ hàng</h1>
        <p class="nd-shop-page-lead">Kiểm tra sản phẩm và tiến hành thanh toán khi sẵn sàng.</p>
    </div>

    @if($cart->items->isEmpty())
        <div class="shop-shell p-10 text-center text-[color:var(--text-muted)]">
            Giỏ hàng của bạn đang trống. <a href="{{ route('products.index') }}" class="font-semibold text-[color:var(--accent)] hover:underline">Tiếp tục mua sắm</a>
        </div>
    @else
        <div class="space-y-4">
            @foreach($cart->items as $line)
                @php $v = $line->variant; $p = $v?->product; $unit = $v ? (float) $v->unitPriceCents() : 0; @endphp
                <div class="shop-shell flex flex-wrap items-center justify-between gap-4 p-4">
                    <div>
                        <p class="font-semibold text-[color:var(--primary)]">{{ $p?->name }} — {{ $v?->size }} / {{ $v?->color }}</p>
                        <p class="text-sm text-[color:var(--text-muted)]">{{ number_format($unit, 0, ',', '.') }} ₫ × {{ $line->quantity }}</p>
                    </div>
                    <form method="post" action="{{ route('cart.update', $line) }}">@csrf @method('PATCH')
                        <input type="number" name="quantity" value="{{ $line->quantity }}" min="0" class="field-control inline-block w-20 py-1.5" />
                        <button class="ml-2 text-sm font-semibold text-[color:var(--accent)] underline" type="submit">Cập nhật</button>
                    </form>
                </div>
            @endforeach
        </div>
        <form method="post" action="{{ route('cart.coupon') }}" class="mt-8 flex flex-wrap gap-2">@csrf
            <input name="code" class="field-control max-w-xs py-2" placeholder="Mã giảm giá" value="{{ $cart->applied_coupon_code }}" />
            <button type="submit" class="rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-[#f7f7f9] px-4 py-2 text-sm font-semibold transition hover:border-[color:var(--accent)]">Áp dụng</button>
        </form>
        @if($cart->applied_coupon_code)
            <form method="post" action="{{ route('cart.coupon.remove') }}" class="mt-2">@csrf @method('DELETE')
                <button class="text-sm font-medium text-red-600 hover:underline" type="submit">Xóa mã giảm giá</button>
            </form>
        @endif
        <div class="shop-shell ml-auto mt-8 max-w-sm space-y-2 p-5 text-sm">
            <p class="flex justify-between"><span>Tạm tính</span><strong>{{ number_format($totals['subtotal'], 0, ',', '.') }} ₫</strong></p>
            <p class="flex justify-between"><span>Giảm giá</span><strong class="text-[color:var(--accent)]">-{{ number_format($totals['discount'], 0, ',', '.') }} ₫</strong></p>
            <p class="flex justify-between"><span>Phí vận chuyển</span><strong>{{ number_format($totals['shipping'], 0, ',', '.') }} ₫</strong></p>
            <p class="flex justify-between border-t border-[color:var(--line-soft)] pt-3 text-lg font-bold text-[color:var(--primary)]"><span>Tổng cộng</span><span>{{ number_format($totals['total'], 0, ',', '.') }} ₫</span></p>
        </div>
        <div class="mt-8 flex justify-end">
            @auth
                <a href="{{ route('checkout.create') }}" class="primary-cta">Thanh toán</a>
            @else
                <a href="{{ route('login') }}" class="primary-cta">Đăng nhập để thanh toán</a>
            @endauth
        </div>
    @endif
@endsection
