@extends('layouts.shop')

@section('title', 'Thanh toán — '.config('app.name'))

@section('content')
    <div class="nd-shop-page-head">
        <h1 class="nd-shop-page-title">Thanh toán</h1>
        <p class="nd-shop-page-lead">Kiểm tra lại đơn hàng, nhập địa chỉ giao và chọn phương thức thanh toán.</p>
    </div>

    <nav class="mb-8 flex flex-wrap items-center gap-2 text-sm text-[color:var(--text-muted)]" aria-label="Tiến trình đặt hàng">
        <a href="{{ route('cart.index') }}" class="font-semibold text-[color:var(--accent)] hover:underline">1. Giỏ hàng</a>
        <span aria-hidden="true">→</span><span class="font-semibold text-[color:var(--primary)]">2. Giao hàng & thanh toán</span>
        <span aria-hidden="true">→</span><span>3. Hoàn tất</span>
    </nav>

    <form id="checkout-form" method="post" action="{{ route('checkout.store') }}" class="grid items-start gap-8 lg:grid-cols-[minmax(0,1fr)_380px]">@csrf
        @foreach($selectedItemIds as $selectedItemId)
            <input type="hidden" name="items[]" value="{{ $selectedItemId }}" />
        @endforeach
        <div class="space-y-6">
            <section class="shop-shell space-y-4 p-5 sm:p-6" aria-labelledby="shipping-title">
                <div><h2 id="shipping-title" class="text-lg font-semibold text-[color:var(--primary)]">Thông tin giao hàng</h2><p class="mt-1 text-sm text-[color:var(--text-muted)]">Chọn địa chỉ đã lưu hoặc nhập địa chỉ mới cho đơn này.</p></div>
                @if($addresses->isNotEmpty())
                    <div><label for="saved-address" class="text-sm font-medium text-[color:var(--primary)]">Địa chỉ đã lưu</label><select id="saved-address" name="address_id" class="field-control mt-1"><option value="">— Nhập địa chỉ mới bên dưới —</option>@foreach($addresses as $a)<option value="{{ $a->id }}" @selected(old('address_id', $a->is_default ? $a->id : '') == $a->id)>{{ $a->full_name }} — {{ $a->line1 }}, {{ $a->city }}</option>@endforeach</select></div>
                @endif
                <div id="new-address-fields" class="space-y-3">
                    <p class="text-xs font-semibold uppercase tracking-wide text-[color:var(--text-muted)]">Địa chỉ mới</p>
                    <div class="grid gap-3 sm:grid-cols-2"><label class="text-sm font-medium text-[color:var(--primary)]">Họ và tên<input name="recipient_name" class="field-control mt-1" placeholder="Nguyễn Văn A" value="{{ old('recipient_name', auth()->user()->name) }}" /></label><label class="text-sm font-medium text-[color:var(--primary)]">Số điện thoại<input name="phone" class="field-control mt-1" placeholder="090..." value="{{ old('phone', auth()->user()->phone) }}" /></label></div>
                    <label class="block text-sm font-medium text-[color:var(--primary)]">Địa chỉ<input name="line1" class="field-control mt-1" placeholder="Số nhà, tên đường" value="{{ old('line1') }}" /></label>
                    <input name="line2" class="field-control" placeholder="Địa chỉ bổ sung (tuỳ chọn)" value="{{ old('line2') }}" />
                    <div class="grid gap-3 sm:grid-cols-2"><input name="city" class="field-control" placeholder="Thành phố / Tỉnh" value="{{ old('city') }}" /><input name="state" class="field-control" placeholder="Quận / Huyện" value="{{ old('state') }}" /></div>
                    <div class="grid gap-3 sm:grid-cols-2"><input name="postal_code" class="field-control" placeholder="Mã bưu điện" value="{{ old('postal_code') }}" /><input name="country" class="field-control uppercase" placeholder="Mã quốc gia" value="{{ old('country', 'VN') }}" /></div>
                    <textarea name="customer_note" class="field-control" rows="2" placeholder="Ghi chú giao hàng (tuỳ chọn)">{{ old('customer_note') }}</textarea>
                </div>
            </section>

            <section class="shop-shell space-y-4 p-5 sm:p-6" aria-labelledby="payment-title">
                <div><h2 id="payment-title" class="text-lg font-semibold text-[color:var(--primary)]">Phương thức thanh toán</h2><p class="mt-1 text-sm text-[color:var(--text-muted)]">Bạn chỉ cần chọn một phương thức cho đơn hàng.</p></div>
                <div class="space-y-3">
                    <label data-payment-option class="flex cursor-pointer items-start gap-3 rounded-[var(--radius-ui)] border-2 border-[color:var(--line-soft)] p-4 transition hover:border-[color:var(--accent)]"><input type="radio" name="payment_method" value="cod" class="mt-1" @checked(old('payment_method', 'cod') === 'cod')><span><span class="block font-semibold text-[color:var(--primary)]">Thanh toán khi nhận hàng (COD)</span><span class="mt-1 block text-xs text-[color:var(--text-muted)]">Thanh toán cho đơn vị giao hàng khi nhận sản phẩm.</span></span></label>
                    @if($bankTransferAvailable)
                        <label data-payment-option class="flex cursor-pointer items-start gap-3 rounded-[var(--radius-ui)] border-2 border-[color:var(--line-soft)] p-4 transition hover:border-[color:var(--accent)]"><input type="radio" name="payment_method" value="bank_transfer" class="mt-1" @checked(old('payment_method') === 'bank_transfer')><span><span class="block font-semibold text-[color:var(--primary)]">Chuyển khoản ngân hàng qua QR</span><span class="mt-1 block text-xs text-[color:var(--text-muted)]">QR sẽ hiển thị số tiền chính xác sau khi đặt đơn. Cửa hàng xác nhận thủ công.</span></span></label>
                    @endif
                </div>
            </section>
        </div>

        <aside class="shop-shell space-y-4 p-5 sm:p-6 lg:sticky lg:top-24" aria-labelledby="summary-title">
            <h2 id="summary-title" class="text-lg font-semibold text-[color:var(--primary)]">Đơn hàng của bạn</h2>
            <div class="max-h-72 space-y-3 overflow-y-auto pr-1">
                @foreach($cart->items as $line)
                    @php($variant = $line->variant)
                    @if($variant?->product)
                        <div class="flex items-center gap-3 text-sm"><img src="{{ $variant->product->mainImageUrl() }}" alt="{{ $variant->product->name }}" class="h-14 w-14 shrink-0 rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] object-cover" /><div class="min-w-0 flex-1"><p class="truncate font-semibold text-[color:var(--primary)]">{{ $variant->product->name }}</p><p class="text-xs text-[color:var(--text-muted)]">{{ $variant->size }} / {{ $variant->color }} × {{ $line->quantity }}</p></div><strong class="shrink-0">{{ number_format((float) $variant->unitPriceCents() * $line->quantity, 0, ',', '.') }} ₫</strong></div>
                    @endif
                @endforeach
            </div>
            <div class="space-y-2 border-t border-[color:var(--line-soft)] pt-4 text-sm"><p class="flex justify-between"><span>Tạm tính</span><span>{{ number_format($totals['subtotal'], 0, ',', '.') }} ₫</span></p><p class="flex justify-between"><span>Giảm giá sản phẩm</span><span class="text-[color:var(--accent)]">-{{ number_format($totals['discount'], 0, ',', '.') }} ₫</span></p><p class="flex justify-between"><span>Phí vận chuyển</span><span>{{ number_format($totals['shipping'], 0, ',', '.') }} ₫</span></p><p class="flex justify-between border-t border-[color:var(--line-soft)] pt-3 text-lg font-bold text-[color:var(--primary)]"><span>Tổng cộng</span><span>{{ number_format($totals['total'], 0, ',', '.') }} ₫</span></p></div>
            <p id="payment-method-note" class="rounded-[var(--radius-ui)] bg-slate-50 p-3 text-xs text-[color:var(--text-muted)]" aria-live="polite"></p>
            <button id="checkout-submit" class="primary-cta w-full justify-center" type="submit"><span data-submit-label>Đặt hàng</span></button>
            <a href="{{ route('cart.index') }}" class="block text-center text-sm font-semibold text-[color:var(--accent)] hover:underline">Quay lại giỏ hàng</a>
        </aside>
    </form>

    <script>
        (() => {
            const form = document.getElementById('checkout-form');
            const address = document.getElementById('saved-address');
            const addressFields = document.getElementById('new-address-fields');
            const note = document.getElementById('payment-method-note');
            const submit = document.getElementById('checkout-submit');
            const syncAddress = () => { if (addressFields) addressFields.classList.toggle('opacity-60', Boolean(address?.value)); };
            const syncPayment = () => {
                const selected = document.querySelector('input[name="payment_method"]:checked')?.value;
                document.querySelectorAll('[data-payment-option]').forEach((option) => { const active = option.querySelector('input')?.value === selected; option.classList.toggle('border-[color:var(--accent)]', active); option.classList.toggle('bg-rose-50', active); });
                if (note) note.textContent = selected === 'bank_transfer' ? 'Bạn sẽ quét mã QR ở trang chi tiết đơn hàng sau khi đặt thành công.' : 'Bạn thanh toán khi nhận sản phẩm từ đơn vị giao hàng.';
            };
            address?.addEventListener('change', syncAddress);
            document.querySelectorAll('input[name="payment_method"]').forEach((input) => input.addEventListener('change', syncPayment));
            form?.addEventListener('submit', () => { if (submit) { submit.disabled = true; submit.querySelector('[data-submit-label]').textContent = 'Đang tạo đơn...'; } });
            syncAddress(); syncPayment();
        })();
    </script>
@endsection
