@extends('layouts.shop')

@section('title', 'Giỏ hàng — '.config('app.name'))

@section('content')
    <div class="nd-shop-page-head">
        <h1 class="nd-shop-page-title">Giỏ hàng</h1>
        <p class="nd-shop-page-lead">Kiểm tra sản phẩm và áp dụng voucher trước khi thanh toán.</p>
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

        <div class="shop-shell mt-8 p-5">
            <h2 class="font-semibold text-[color:var(--primary)]">Voucher công khai</h2>
            <p class="mt-1 text-xs text-[color:var(--text-muted)]">Mỗi đơn được dùng tối đa một voucher sản phẩm và một voucher vận chuyển.</p>
            <div class="mt-4 grid gap-3 sm:grid-cols-2">
                @forelse($publicCoupons as $publicCoupon)
                    <div class="voucher-card flex items-center justify-between gap-3 border border-[color:var(--line-soft)] bg-[#f7f7f9] p-3 transition-opacity" data-voucher-card data-voucher-kind="{{ $publicCoupon->isShipping() ? 'shipping' : 'product' }}" data-voucher-code="{{ $publicCoupon->code }}">
                        <div class="min-w-0">
                            <p class="font-mono text-sm font-bold text-[color:var(--accent)]">{{ $publicCoupon->code }}</p>
                            <p class="mt-1 text-xs text-[color:var(--text-muted)]">{{ $publicCoupon->kindLabel() }} · {{ $publicCoupon->type === 'percent' ? rtrim(rtrim((string) $publicCoupon->value, '0'), '.').'%' : number_format((float) $publicCoupon->value, 0, ',', '.').' ₫' }}</p>
                        </div>
                        <form method="post" action="{{ route('cart.coupon') }}" data-voucher-form onsubmit="event.preventDefault(); window.submitVoucherForm(this); return false;">@csrf
                            <input type="hidden" name="code" value="{{ $publicCoupon->code }}" />
                            <button type="submit" class="shrink-0 text-xs font-semibold text-[color:var(--accent)] underline">Áp dụng</button>
                        </form>
                        <span data-voucher-selected class="hidden shrink-0 text-xs font-bold text-[color:var(--accent)]">Đã chọn</span>
                    </div>
                @empty
                    <p class="text-sm text-[color:var(--text-muted)]">Hiện chưa có voucher công khai.</p>
                @endforelse
            </div>
        </div>

        <div class="mt-5 grid gap-3 sm:grid-cols-2">
            <form method="post" action="{{ route('cart.coupon') }}" class="flex flex-wrap gap-2" data-voucher-form onsubmit="event.preventDefault(); window.submitVoucherForm(this); return false;">@csrf
                <input name="code" class="field-control min-w-0 flex-1 py-2" placeholder="Mã giảm sản phẩm" value="{{ $cart->applied_coupon_code }}" />
                <button type="submit" class="rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-[#f7f7f9] px-4 py-2 text-sm font-semibold transition hover:border-[color:var(--accent)]">Áp dụng</button>
            </form>
            <form method="post" action="{{ route('cart.coupon') }}" class="flex flex-wrap gap-2" data-voucher-form onsubmit="event.preventDefault(); window.submitVoucherForm(this); return false;">@csrf
                <input name="code" class="field-control min-w-0 flex-1 py-2" placeholder="Mã giảm vận chuyển" value="{{ $cart->applied_shipping_coupon_code }}" />
                <button type="submit" class="rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-[#f7f7f9] px-4 py-2 text-sm font-semibold transition hover:border-[color:var(--accent)]">Áp dụng</button>
            </form>
        </div>

        <div class="mt-2 flex flex-wrap gap-4">
            @if($cart->applied_coupon_code)
                <form method="post" action="{{ route('cart.coupon.remove') }}" data-voucher-remove onsubmit="event.preventDefault(); window.submitVoucherForm(this); return false;">@csrf @method('DELETE')
                    <input type="hidden" name="kind" value="product" />
                    <button class="text-sm font-medium text-red-600 hover:underline" type="submit">Xóa voucher sản phẩm</button>
                </form>
            @endif
            @if($cart->applied_shipping_coupon_code)
                <form method="post" action="{{ route('cart.coupon.remove') }}" data-voucher-remove onsubmit="event.preventDefault(); window.submitVoucherForm(this); return false;">@csrf @method('DELETE')
                    <input type="hidden" name="kind" value="shipping" />
                    <button class="text-sm font-medium text-red-600 hover:underline" type="submit">Xóa voucher vận chuyển</button>
                </form>
            @endif
        </div>

        <div class="shop-shell ml-auto mt-8 max-w-sm space-y-2 p-5 text-sm">
            <p class="flex justify-between"><span>Tạm tính</span><strong>{{ number_format($totals['subtotal'], 0, ',', '.') }} ₫</strong></p>
            <p class="flex justify-between"><span>Giảm giá sản phẩm</span><strong id="cart-product-discount" class="text-[color:var(--accent)]">-{{ number_format($totals['discount'], 0, ',', '.') }} ₫</strong></p>
            <p class="flex justify-between"><span>Giảm phí vận chuyển</span><strong id="cart-shipping-discount" class="text-[color:var(--accent)]">-{{ number_format($totals['shipping_discount'], 0, ',', '.') }} ₫</strong></p>
            <p class="flex justify-between"><span>Phí vận chuyển</span><strong id="cart-shipping">{{ number_format($totals['shipping'], 0, ',', '.') }} ₫</strong></p>
            <p class="flex justify-between border-t border-[color:var(--line-soft)] pt-3 text-lg font-bold text-[color:var(--primary)]"><span>Tổng cộng</span><span id="cart-total">{{ number_format($totals['total'], 0, ',', '.') }} ₫</span></p>
        </div>
        @if($totals['is_free_shipping'])
            <p class="mt-4 text-right text-sm font-semibold text-emerald-700">Đơn hàng của bạn được miễn phí vận chuyển.</p>
        @elseif($totals['free_shipping_threshold'] > 0)
            <p class="mt-4 text-right text-sm text-[color:var(--text-muted)]">Mua thêm {{ number_format($totals['remaining_for_free_shipping'], 0, ',', '.') }} ₫ để được miễn phí vận chuyển.</p>
        @endif
        <div class="mt-8 flex justify-end">
            @auth
                <a href="{{ route('checkout.create') }}" class="primary-cta">Thanh toán</a>
            @else
                <a href="{{ route('login') }}" class="primary-cta">Đăng nhập để thanh toán</a>
            @endauth
        </div>
    @endif
@endsection

<script>
    (() => {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const formatMoney = (value) => new Intl.NumberFormat('vi-VN').format(Math.round(Number(value))) + ' ₫';
        const cards = [...document.querySelectorAll('[data-voucher-card]')];

        const syncSelection = (payload) => {
            cards.forEach((card) => {
                const kind = card.dataset.voucherKind;
                const selected = card.dataset.voucherCode === (kind === 'shipping' ? payload.shipping_code : payload.product_code);
                const sameKindSelected = kind === 'shipping' ? Boolean(payload.shipping_code) : Boolean(payload.product_code);
                card.classList.toggle('ring-2', selected);
                card.classList.toggle('ring-[color:var(--accent)]', selected);
                card.classList.toggle('font-semibold', selected);
                card.classList.toggle('opacity-50', sameKindSelected && !selected);
                card.classList.toggle('opacity-100', !sameKindSelected || selected);
            });
            document.querySelector('#cart-product-discount')?.replaceChildren(document.createTextNode('-' + formatMoney(payload.discount)));
            document.querySelector('#cart-shipping-discount')?.replaceChildren(document.createTextNode('-' + formatMoney(payload.shipping_discount)));
            document.querySelector('#cart-shipping')?.replaceChildren(document.createTextNode(formatMoney(payload.shipping)));
            document.querySelector('#cart-total')?.replaceChildren(document.createTextNode(formatMoney(payload.total)));
        };

        document.querySelectorAll('[data-voucher-form], [data-voucher-remove]').forEach((form) => {
            form.addEventListener('submit', async (event) => {
                if (form.hasAttribute('onsubmit')) return;
                event.preventDefault();
                const button = form.querySelector('button[type="submit"]');
                if (button) button.disabled = true;
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
                        body: new FormData(form),
                    });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(payload.message || 'Không thể áp dụng voucher.');
                    syncSelection(payload);
                    window.dispatchEvent(new CustomEvent('voucher-updated', { detail: payload }));
                } catch (error) {
                    window.alert(error.message);
                } finally {
                    if (button) button.disabled = false;
                }
            });
        });

        syncSelection({ product_code: @json($cart->applied_coupon_code), shipping_code: @json($cart->applied_shipping_coupon_code), discount: @json($totals['discount']), shipping_discount: @json($totals['shipping_discount']), shipping: @json($totals['shipping']), total: @json($totals['total']) });
    })();
</script>

<script>
    window.submitVoucherForm = async (form) => {
        const button = form.querySelector('button[type="submit"]');
        if (button) button.disabled = true;
        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body: new FormData(form),
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Không thể cập nhật voucher.');
            document.querySelectorAll('[data-voucher-card]').forEach((card) => {
                const kind = card.dataset.voucherKind;
                const selectedCode = kind === 'shipping' ? payload.shipping_code : payload.product_code;
                const selected = card.dataset.voucherCode === selectedCode;
                const hasSelected = Boolean(selectedCode);
                const badge = card.querySelector('[data-voucher-selected]');
                card.style.opacity = hasSelected && !selected ? '0.38' : '1';
                card.style.borderColor = selected ? 'var(--accent)' : '';
                card.style.backgroundColor = selected ? 'rgba(233, 69, 96, 0.08)' : '';
                card.style.boxShadow = selected ? '0 0 0 2px var(--accent)' : '';
                card.style.fontWeight = selected ? '700' : '';
                if (badge) badge.classList.toggle('hidden', !selected);
            });
            const money = (value) => new Intl.NumberFormat('vi-VN').format(Math.round(Number(value))) + ' ₫';
            document.querySelector('#cart-product-discount')?.replaceChildren(document.createTextNode('-' + money(payload.discount)));
            document.querySelector('#cart-shipping-discount')?.replaceChildren(document.createTextNode('-' + money(payload.shipping_discount)));
            document.querySelector('#cart-shipping')?.replaceChildren(document.createTextNode(money(payload.shipping)));
            document.querySelector('#cart-total')?.replaceChildren(document.createTextNode(money(payload.total)));
        } catch (error) {
            window.alert(error.message);
        } finally {
            if (button) button.disabled = false;
        }
    };
</script>
