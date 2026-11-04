@extends('layouts.shop')

@section('title', 'Giỏ hàng — '.config('app.name'))

@section('content')
    <div class="nd-shop-page-head">
        <h1 class="nd-shop-page-title">Giỏ hàng</h1>
        <p class="nd-shop-page-lead">Kiểm tra sản phẩm trước khi thanh toán.</p>
    </div>

    @if($cart->items->isEmpty())
        <div class="shop-shell p-10 text-center text-[color:var(--text-muted)]">
            Giỏ hàng của bạn đang trống. <a href="{{ route('products.index') }}" class="font-semibold text-[color:var(--accent)] hover:underline">Tiếp tục mua sắm</a>
        </div>
    @else
        <div class="cart-checkout-layout mt-4">
        <div class="min-w-0">
        @if($totals['free_shipping_threshold'] > 0)
            <div class="shop-shell mb-4 p-4" aria-label="Tiến độ miễn phí vận chuyển">
                <div class="flex items-center justify-between gap-3 text-sm">
                    <span class="font-semibold text-[color:var(--primary)]">Miễn phí vận chuyển</span>
                    <span class="text-[color:var(--text-muted)]" data-free-shipping-label>
                        @if($totals['is_free_shipping']) Đã đạt điều kiện @else Mua thêm {{ number_format($totals['remaining_for_free_shipping'], 0, ',', '.') }} ₫ @endif
                    </span>
                </div>
                <div class="mt-2 h-2 overflow-hidden rounded-full bg-slate-100">
                    <div class="h-full rounded-full bg-[color:var(--accent)] transition-all" style="width: {{ min(100, ($totals['subtotal'] / $totals['free_shipping_threshold']) * 100) }}%" data-free-shipping-progress></div>
                </div>
            </div>
        @endif

        <div class="mb-3 flex flex-wrap items-center justify-between gap-2 rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-white px-4 py-3 text-sm">
            <label class="flex cursor-pointer items-center gap-2 font-semibold text-[color:var(--primary)]">
                <input type="checkbox" data-cart-select-all class="rounded border-[color:var(--line-soft)] text-[color:var(--accent)]" checked>
                Chọn tất cả sản phẩm
            </label>
            <span class="text-xs text-[color:var(--text-muted)]" data-cart-selection-count>Đã chọn tất cả</span>
        </div>
        <div id="cart-lines" class="space-y-3">
            @foreach($cart->items as $line)
                @php $v = $line->variant; $p = $v?->product; $unit = $v ? (float) $v->unitPriceCents() : 0; @endphp
                <div class="shop-shell flex flex-wrap items-center justify-between gap-3 p-3 sm:p-4" data-cart-line="{{ $line->id }}" data-unit-price="{{ $unit }}">
                    <label class="flex shrink-0 cursor-pointer items-center self-start pt-1" title="Chọn sản phẩm để thanh toán">
                        <input type="checkbox" name="cart_item_ids[]" value="{{ $line->id }}" data-cart-item-check class="h-4 w-4 rounded border-[color:var(--line-soft)] text-[color:var(--accent)]" checked>
                        <span class="sr-only">Chọn {{ $p?->name }}</span>
                    </label>
                    <div class="flex min-w-0 items-center gap-3">
                        @if($p)
                            <a href="{{ route('products.show', ['slug' => $p->slug, 'size' => $v?->size, 'color' => $v?->color]) }}" class="block shrink-0 rounded-[var(--radius-ui)] focus:outline-none focus:ring-2 focus:ring-[color:var(--accent)]" title="Chọn lại size hoặc màu">
                                <img src="{{ $p->mainImageUrl() }}" alt="{{ $p->name }}" class="h-20 w-20 rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] object-cover transition hover:opacity-85 sm:h-24 sm:w-24" />
                            </a>
                        @endif
                        <div class="min-w-0">
                            @if($p)
                                <a href="{{ route('products.show', ['slug' => $p->slug, 'size' => $v?->size, 'color' => $v?->color]) }}" class="font-semibold text-[color:var(--primary)] transition hover:text-[color:var(--accent)] hover:underline" title="Chọn lại size hoặc màu">{{ $p->name }} — {{ $v?->size }} / {{ $v?->color }}</a>
                            @else
                                <p class="font-semibold text-[color:var(--primary)]">{{ $p?->name }} — {{ $v?->size }} / {{ $v?->color }}</p>
                            @endif
                            <p class="mt-1 text-xs text-[color:var(--text-muted)]">Đơn giá {{ number_format($unit, 0, ',', '.') }} ₫</p>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 sm:gap-5">
                    <p class="font-semibold text-[color:var(--accent)]" data-line-total>{{ number_format($unit * $line->quantity, 0, ',', '.') }} ₫</p>
                    <form method="post" action="{{ route('cart.update', $line) }}" data-cart-update-form class="flex items-center gap-1">@csrf @method('PATCH')
                        <label class="sr-only" for="cart-quantity-{{ $line->id }}">Số lượng</label>
                        <button type="button" class="quantity-stepper" data-quantity-decrement aria-label="Giảm số lượng">−</button>
                        <input id="cart-quantity-{{ $line->id }}" type="number" name="quantity" value="{{ $line->quantity }}" min="0" max="99" class="field-control w-12 px-1 py-1.5 text-center" />
                        <button type="button" class="quantity-stepper" data-quantity-increment aria-label="Tăng số lượng">+</button>
                        <button type="submit" class="sr-only">Cập nhật</button>
                    </form>
                    <button type="button" class="cart-remove-button" data-cart-remove aria-label="Xóa sản phẩm" title="Xóa sản phẩm">🗑</button>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="shop-shell mt-6 p-3.5 sm:p-4">
            <h2 class="text-sm font-semibold text-[color:var(--primary)]">Mã giảm giá</h2>
            <p class="mt-1 text-xs text-[color:var(--text-muted)]">Chọn tối đa một voucher sản phẩm và một voucher vận chuyển.</p>
            <div class="mt-3 grid gap-2 sm:grid-cols-2">
                @forelse($publicCoupons as $publicCoupon)
                    @php
                        $couponAmount = $publicCoupon->isShipping() ? ($totals['base_shipping'] ?? 0) : $totals['subtotal'];
                        $couponReason = $publicCoupon->validationErrorForAmount($couponAmount);
                        $couponEligible = $couponReason === null;
                        $couponBenefit = $publicCoupon->type === 'percent'
                            ? rtrim(rtrim((string) $publicCoupon->value, '0'), '.').'%'
                            : number_format((float) $publicCoupon->value, 0, ',', '.').' đ';
                    @endphp
                    <div class="voucher-card flex flex-col justify-between gap-2 rounded-lg border border-slate-200 bg-white p-2.5 shadow-sm transition-all" data-voucher-card data-voucher-kind="{{ $publicCoupon->isShipping() ? 'shipping' : 'product' }}" data-voucher-code="{{ $publicCoupon->code }}" data-remove-url="{{ route('cart.coupon.remove') }}" data-voucher-eligible="{{ $couponEligible ? '1' : '0' }}" data-voucher-reason="{{ $couponReason }}">
                        <div class="flex items-center justify-between gap-2">
                            <div class="min-w-0">
                                <p class="inline-block rounded bg-rose-50 px-1.5 py-0.5 font-mono text-[10px] font-bold text-[color:var(--accent)]">{{ $publicCoupon->code }}</p>
                                <p class="mt-1 text-sm font-bold text-[color:var(--accent)]">{{ $publicCoupon->isShipping() && $publicCoupon->type === 'percent' && (float) $publicCoupon->value >= 100 ? 'Miễn phí vận chuyển' : 'Giảm '.$couponBenefit }}</p>
                            </div>
                            <span data-voucher-state class="shrink-0 rounded-full border px-2 py-0.5 text-[10px] font-semibold"></span>
                        </div>
                        <div class="flex items-center justify-between gap-2 border-t border-slate-100 pt-2">
                            <div class="min-w-0 text-[10px] leading-4 text-slate-500">
                                <p>{{ $publicCoupon->kindLabel() }} · Tối thiểu {{ number_format((float) $publicCoupon->min_order_amount, 0, ',', '.') }} đ</p>
                                <p data-voucher-reason class="text-rose-600"></p>
                            </div>
                            <form method="post" action="{{ route('cart.coupon') }}" data-voucher-modern-form onsubmit="event.preventDefault(); window.submitVoucherForm(this); return false;">@csrf
                                <input type="hidden" name="code" value="{{ $publicCoupon->code }}" />
                                <button type="submit" data-voucher-apply class="rounded-md bg-[color:var(--accent)] px-3 py-1.5 text-[11px] font-semibold text-white transition hover:brightness-95">Áp dụng</button>
                            </form>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-[color:var(--text-muted)]">Hiện chưa có voucher công khai.</p>
                @endforelse
            </div>
        </div>

        <div class="shop-shell mt-3 p-3 sm:p-3.5">
            <div class="mb-2 flex items-center gap-2">
                <span class="text-base text-[color:var(--primary)]">▱</span>
                <div>
                    <h3 class="text-xs font-semibold text-[color:var(--primary)]">Bạn có mã giảm giá riêng?</h3>
                    <p class="mt-0.5 text-[10px] text-[color:var(--text-muted)]">Nhập mã voucher để được hưởng ưu đãi.</p>
                </div>
            </div>
            <form method="post" action="{{ route('cart.coupon') }}" class="flex gap-2" data-voucher-form onsubmit="event.preventDefault(); window.submitVoucherForm(this); return false;">@csrf
                <input name="code" class="field-control min-w-0 flex-1 py-1.5 text-xs" placeholder="Nhập mã voucher" />
                <button type="submit" class="rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-[#f7f7f9] px-3 py-1.5 text-xs font-semibold transition hover:border-[color:var(--accent)]">Áp dụng</button>
            </form>
        </div>
        </div>
        <aside class="min-w-0">
        <div class="shop-shell mt-0 flex min-h-[380px] w-full flex-col space-y-4 p-5 text-sm lg:sticky lg:top-24">
            <p class="flex justify-between"><span>Tạm tính</span><strong id="cart-subtotal">{{ number_format($totals['subtotal'], 0, ',', '.') }} ₫</strong></p>
            <p id="cart-product-discount-row" class="flex justify-between {{ (float) $totals['discount'] <= 0 ? 'hidden' : '' }}"><span>Giảm giá sản phẩm</span><strong id="cart-product-discount" class="text-[color:var(--accent)]">-{{ number_format($totals['discount'], 0, ',', '.') }} ₫</strong></p>
            <p id="cart-shipping-discount-row" class="flex justify-between {{ (float) $totals['shipping_discount'] <= 0 ? 'hidden' : '' }}"><span>Giảm phí vận chuyển</span><strong id="cart-shipping-discount" class="text-[color:var(--accent)]">-{{ number_format($totals['shipping_discount'], 0, ',', '.') }} ₫</strong></p>
            <p class="flex justify-between"><span>Phí vận chuyển</span><strong id="cart-shipping">{{ number_format($totals['shipping'], 0, ',', '.') }} ₫</strong></p>
            <p class="flex justify-between border-t border-[color:var(--line-soft)] pt-3 text-xl font-bold text-[color:var(--accent)]"><span class="text-[color:var(--primary)]">Tổng cộng</span><span id="cart-total">{{ number_format($totals['total'], 0, ',', '.') }} ₫</span></p>
            <div class="mt-auto space-y-2 pt-4">
                @auth
                    <button type="button" data-checkout-selected class="primary-cta w-full justify-center">Thanh toán sản phẩm đã chọn</button>
                    <a href="{{ route('checkout.create') }}" class="block w-full rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] px-4 py-2.5 text-center text-sm font-semibold text-[color:var(--primary)] transition hover:border-[color:var(--accent)] hover:text-[color:var(--accent)]">Thanh toán tất cả</a>
                @else
                    <a href="{{ route('login') }}" class="primary-cta w-full justify-center">Đăng nhập để thanh toán</a>
                @endauth
            </div>
        </div>
        </aside>
        </div>
    @endif
@endsection

@push('scripts')
<script>
    (() => {
        const token = document.querySelector('meta[name="csrf-token"]')?.content;
        const formatMoney = (value) => new Intl.NumberFormat('vi-VN').format(Math.round(Number(value))) + ' ₫';
        window.updateCartSummary = (payload) => {
            document.querySelector('#cart-subtotal')?.replaceChildren(document.createTextNode(formatMoney(payload.subtotal)));
            document.querySelector('#cart-product-discount')?.replaceChildren(document.createTextNode('-' + formatMoney(payload.discount)));
            document.querySelector('#cart-shipping-discount')?.replaceChildren(document.createTextNode('-' + formatMoney(payload.shipping_discount)));
            document.querySelector('#cart-shipping')?.replaceChildren(document.createTextNode(formatMoney(payload.shipping)));
            document.querySelector('#cart-total')?.replaceChildren(document.createTextNode(formatMoney(payload.total)));
            document.querySelector('#cart-product-discount-row')?.classList.toggle('hidden', Number(payload.discount) <= 0);
            document.querySelector('#cart-shipping-discount-row')?.classList.toggle('hidden', Number(payload.shipping_discount) <= 0);
            const progress = document.querySelector('[data-free-shipping-progress]');
            const label = document.querySelector('[data-free-shipping-label]');
            if (progress && Number(payload.free_shipping_threshold) > 0) {
                progress.style.width = Math.min(100, (Number(payload.subtotal) / Number(payload.free_shipping_threshold)) * 100) + '%';
            }
            if (label) label.textContent = payload.is_free_shipping
                ? 'Đã đạt điều kiện'
                : 'Mua thêm ' + formatMoney(payload.remaining_for_free_shipping);
        };
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
                card.querySelector('[data-voucher-form]')?.classList.add('hidden');
                const modernForm = card.querySelector('[data-voucher-modern-form]');
                const eligible = card.dataset.voucherEligible === '1';
                const state = card.querySelector('[data-voucher-state]');
                const reason = card.querySelector('[data-voucher-reason]');
                if (state) state.textContent = selected ? 'Đang áp dụng' : (eligible ? 'Có thể áp dụng' : 'Không đủ điều kiện');
                if (reason) reason.textContent = eligible ? '' : (card.dataset.voucherReason || 'Voucher chưa đủ điều kiện.');
                modernForm?.classList.toggle('hidden', selected || !eligible);
            });
            window.updateCartSummary(payload);
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

        syncSelection({ product_code: @json($cart->applied_coupon_code), shipping_code: @json($cart->applied_shipping_coupon_code), discount: @json($totals['discount']), shipping_discount: @json($totals['shipping_discount']), shipping: @json($totals['shipping']), total: @json($totals['total']), subtotal: @json($totals['subtotal']), is_free_shipping: @json($totals['is_free_shipping']), remaining_for_free_shipping: @json($totals['remaining_for_free_shipping']), free_shipping_threshold: @json($totals['free_shipping_threshold']) });
    })();
</script>

<script>
    (() => {
        const money = (value) => new Intl.NumberFormat('vi-VN').format(Math.round(Number(value))) + ' ₫';
        const token = document.querySelector('meta[name="csrf-token"]')?.content || '';

        document.querySelectorAll('[data-cart-update-form]').forEach((form) => {
            const quantity = form.querySelector('[name="quantity"]');
            const submitQuantity = () => {
                const submitButton = form.querySelector('button[type="submit"]');
                if (typeof form.requestSubmit === 'function') form.requestSubmit();
                else submitButton?.click();
            };
            form.querySelector('[data-quantity-decrement]')?.addEventListener('click', () => {
                quantity.value = Math.max(Number(quantity.min || 0), Number(quantity.value || 0) - 1);
                submitQuantity();
            });
            form.querySelector('[data-quantity-increment]')?.addEventListener('click', () => {
                quantity.value = Math.min(Number(quantity.max || 99), Number(quantity.value || 0) + 1);
                submitQuantity();
            });
            quantity?.addEventListener('change', submitQuantity);
            form.closest('[data-cart-line]')?.querySelector('[data-cart-remove]')?.addEventListener('click', () => {
                quantity.value = 0;
                submitQuantity();
            });
            form.addEventListener('submit', async (event) => {
                event.preventDefault();
                const button = form.querySelector('button[type="submit"]');
                const line = form.closest('[data-cart-line]');
                const quantity = form.querySelector('[name="quantity"]');
                if (button) {
                    button.disabled = true;
                    button.textContent = 'Đang lưu...';
                }
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': token },
                        body: new FormData(form),
                    });
                    const payload = await response.json();
                    if (!response.ok) throw new Error(payload.message || 'Không thể cập nhật giỏ hàng.');
                    if (!payload.quantity) {
                        line?.remove();
                    } else {
                        line?.querySelector('[data-line-total]')?.replaceChildren(document.createTextNode(money(payload.line_total)));
                        const detail = line?.querySelector('p.text-sm');
                        if (detail) detail.textContent = detail.textContent.replace(/×\s*\d+/, '× ' + payload.quantity);
                    }
                    window.updateCartSummary?.(payload);
                    window.dispatchEvent(new CustomEvent('cart-updated', { detail: payload }));
                    if (!document.querySelector('[data-cart-line]')) window.location.reload();
                } catch (error) {
                    window.alert(error.message);
                } finally {
                    if (button) {
                        button.disabled = false;
                        button.textContent = 'Cập nhật';
                    }
                }
            });
        });
    })();
</script>

<script>
    window.submitVoucherForm = async (form) => {
        const button = form.querySelector('button[type="submit"]');
        if (button) button.disabled = true;
        try {
            const body = new FormData(form);
            document.querySelectorAll('[data-cart-item-check]:checked').forEach((item) => body.append('item_ids[]', item.value));
            const response = await fetch(form.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                },
                body,
            });
            const payload = await response.json();
            if (!response.ok) throw new Error(payload.message || 'Không thể cập nhật voucher.');
            document.querySelectorAll('[data-voucher-card]').forEach((card) => {
                const kind = card.dataset.voucherKind;
                const selectedCode = kind === 'shipping' ? payload.shipping_code : payload.product_code;
                const selected = card.dataset.voucherCode === selectedCode;
                const hasSelected = Boolean(selectedCode);
                card.querySelector('[data-voucher-form]')?.classList.add('hidden');
                const modernForm = card.querySelector('[data-voucher-modern-form]');
                const eligible = card.dataset.voucherEligible === '1';
                const state = card.querySelector('[data-voucher-state]');
                const reason = card.querySelector('[data-voucher-reason]');
                if (state) {
                    state.textContent = selected ? 'Đang áp dụng' : (eligible ? 'Có thể áp dụng' : 'Không đủ điều kiện');
                    state.className = selected
                        ? 'shrink-0 rounded-full border border-rose-200 bg-rose-50 px-2.5 py-1 text-xs font-semibold text-[color:var(--accent)]'
                        : (eligible ? 'shrink-0 rounded-full border border-emerald-200 bg-emerald-50 px-2.5 py-1 text-xs font-semibold text-emerald-700' : 'shrink-0 rounded-full border border-slate-200 bg-slate-50 px-2.5 py-1 text-xs font-semibold text-slate-400');
                }
                if (reason) reason.textContent = eligible ? '' : (card.dataset.voucherReason || 'Voucher chưa đủ điều kiện.');
                if (modernForm) {
                    modernForm.classList.toggle('hidden', selected || !eligible);
                    modernForm.querySelector('button')?.toggleAttribute('disabled', !eligible);
                }
                const badge = card.querySelector('[data-voucher-selected]');
                let remove = card.querySelector('[data-voucher-remove-card]');
                if (!remove) {
                    remove = document.createElement('form');
                    remove.method = 'post';
                    remove.action = card.dataset.removeUrl;
                    remove.className = 'hidden shrink-0';
                    remove.dataset.voucherRemoveCard = 'true';
                    remove.innerHTML = '<input type="hidden" name="_token" value="' + (document.querySelector('meta[name="csrf-token"]')?.content || '') + '"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="kind" value="' + card.dataset.voucherKind + '"><button type="submit" class="text-xs font-semibold text-red-600 underline">Bỏ chọn</button>';
                    remove.addEventListener('submit', (event) => { event.preventDefault(); window.submitVoucherForm(remove); });
                    card.appendChild(remove);
                }
                card.style.opacity = hasSelected && !selected ? '0.38' : '1';
                card.style.borderColor = selected ? 'var(--accent)' : '';
                card.style.backgroundColor = selected ? 'rgba(233, 69, 96, 0.08)' : '';
                card.style.boxShadow = selected ? '0 0 0 2px var(--accent)' : '';
                card.style.fontWeight = selected ? '700' : '';
                if (badge) badge.classList.add('hidden');
                remove.classList.toggle('hidden', !selected);
            });
            window.updateCartSummary?.(payload);
        } catch (error) {
            window.alert(error.message);
        } finally {
            if (button) button.disabled = false;
        }
    };
</script>
<script>
    (() => {
        const checks = () => [...document.querySelectorAll('[data-cart-item-check]')];
        const selectAll = document.querySelector('[data-cart-select-all]');
        const count = document.querySelector('[data-cart-selection-count]');
        const checkoutSelected = document.querySelector('[data-checkout-selected]');
        window.updateCartVoucherEligibility = (coupons = []) => {
            const byCode = new Map(coupons.map((coupon) => [coupon.code, coupon]));
            document.querySelectorAll('[data-voucher-card]').forEach((card) => {
                const coupon = byCode.get(card.dataset.voucherCode);
                if (!coupon) return;
                card.dataset.voucherEligible = coupon.eligible ? '1' : '0';
                card.dataset.voucherReason = coupon.reason || '';
                const state = card.querySelector('[data-voucher-state]');
                const modernForm = card.querySelector('[data-voucher-modern-form]');
                const selected = card.dataset.voucherCode === (card.dataset.voucherKind === 'shipping' ? @json($cart->applied_shipping_coupon_code) : @json($cart->applied_coupon_code));
                if (state && !selected) state.textContent = coupon.eligible ? 'Có thể áp dụng' : 'Không đủ điều kiện';
                card.querySelector('[data-voucher-reason]')?.replaceChildren(document.createTextNode(coupon.reason || ''));
                modernForm?.classList.toggle('hidden', selected || !coupon.eligible);
                modernForm?.querySelector('button')?.toggleAttribute('disabled', !coupon.eligible);
            });
        };
        const refreshSelection = async (selectedIds) => {
            if (!selectedIds.length) return;
            try {
                const response = await fetch(@json(route('cart.selection.preview')), {
                    method: 'POST',
                    headers: { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '' },
                    body: JSON.stringify({ item_ids: selectedIds }),
                });
                const payload = await response.json();
                if (!response.ok) throw new Error(payload.message || 'Không thể cập nhật tổng tiền.');
                window.updateCartSummary?.(payload);
                window.updateCartVoucherEligibility?.(payload.coupons);
            } catch (error) {
                window.alert(error.message);
            }
        };
        const sync = () => {
            const items = checks();
            const selected = items.filter((item) => item.checked).length;
            if (selectAll) selectAll.checked = items.length > 0 && selected === items.length;
            if (count) count.textContent = selected === items.length ? 'Đã chọn tất cả' : `Đã chọn ${selected}/${items.length} sản phẩm`;
            checkoutSelected?.toggleAttribute('disabled', selected === 0);
            checkoutSelected?.classList.toggle('opacity-50', selected === 0);
            refreshSelection(items.filter((item) => item.checked).map((item) => item.value));
        };
        selectAll?.addEventListener('change', () => {
            checks().forEach((item) => { item.checked = selectAll.checked; });
            sync();
        });
        document.addEventListener('change', (event) => {
            if (event.target.matches('[data-cart-item-check]')) sync();
        });
        checkoutSelected?.addEventListener('click', () => {
            const selectedIds = checks().filter((item) => item.checked).map((item) => item.value);
            if (!selectedIds.length) {
                window.alert('Vui lòng chọn ít nhất một sản phẩm để thanh toán.');
                return;
            }
            const url = new URL(@json(route('checkout.create')), window.location.origin);
            selectedIds.forEach((id) => url.searchParams.append('items[]', id));
            window.location.href = url.toString();
        });
        sync();
    })();
</script>
@endpush
