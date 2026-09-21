@extends('layouts.shop')

@section('title', 'Thanh toán — '.config('app.name'))

@section('content')
    <div class="nd-shop-page-head">
        <h1 class="nd-shop-page-title">Thanh toán (COD)</h1>
        <p class="nd-shop-page-lead">Nhập địa chỉ giao hàng và xác nhận đơn — thanh toán khi nhận hàng.</p>
    </div>

    <form method="post" action="{{ route('checkout.store') }}" class="grid gap-10 md:grid-cols-2">@csrf
        <div class="shop-shell space-y-4 p-5 sm:p-6">
            <h2 class="text-lg font-semibold text-[color:var(--primary)]">Giao hàng</h2>
            @if($addresses->isNotEmpty())
                <div>
                    <label class="text-sm font-medium text-[color:var(--primary)]">Địa chỉ đã lưu</label>
                    <select name="address_id" class="field-control mt-1">
                        <option value="">— Nhập địa chỉ mới bên dưới —</option>
                        @foreach($addresses as $a)
                            <option value="{{ $a->id }}" @selected($a->is_default)>
                                {{ $a->full_name }} — {{ $a->line1 }}, {{ $a->city }}
                            </option>
                        @endforeach
                    </select>
                </div>
            @endif
            <p class="text-sm text-[color:var(--text-muted)]">Hoặc điền địa chỉ mới (để trống mục đã lưu nếu dùng form dưới):</p>
            <input name="recipient_name" class="field-control" placeholder="Họ và tên người nhận" value="{{ old('recipient_name', auth()->user()->name) }}" />
            <input name="phone" class="field-control" placeholder="Số điện thoại" value="{{ old('phone', auth()->user()->phone) }}" />
            <input name="line1" class="field-control" placeholder="Địa chỉ (dòng 1)" value="{{ old('line1') }}" />
            <input name="line2" class="field-control" placeholder="Địa chỉ (dòng 2, tuỳ chọn)" value="{{ old('line2') }}" />
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <input name="city" class="field-control" placeholder="Thành phố / Tỉnh" value="{{ old('city') }}" />
                <input name="state" class="field-control" placeholder="Quận / Huyện" value="{{ old('state') }}" />
            </div>
            <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                <input name="postal_code" class="field-control" placeholder="Mã bưu điện" value="{{ old('postal_code') }}" />
                <input name="country" class="field-control uppercase" placeholder="Quốc gia (mã ISO)" value="{{ old('country', 'VN') }}" />
            </div>
            <textarea name="customer_note" class="field-control" rows="2" placeholder="Ghi chú giao hàng (tuỳ chọn)">{{ old('customer_note') }}</textarea>
            <fieldset class="border-t border-[color:var(--line-soft)] pt-5">
                <legend class="text-sm font-semibold text-[color:var(--primary)]">Phương thức thanh toán</legend>
                <div class="mt-3 space-y-3">
                    <label class="flex cursor-pointer items-start gap-3 border border-[color:var(--line-soft)] p-4">
                        <input type="radio" name="payment_method" value="cod" class="mt-1" @checked(old('payment_method', 'cod') === 'cod')>
                        <span>
                            <span class="block text-sm font-semibold text-[color:var(--primary)]">Thanh toán khi nhận hàng (COD)</span>
                            <span class="mt-1 block text-xs text-[color:var(--text-muted)]">Thanh toán cho đơn vị giao hàng khi nhận được sản phẩm.</span>
                        </span>
                    </label>
                    @if($bankTransferAvailable)
                        <label class="flex cursor-pointer items-start gap-3 border border-[color:var(--line-soft)] p-4">
                            <input type="radio" name="payment_method" value="bank_transfer" class="mt-1" @checked(old('payment_method') === 'bank_transfer')>
                            <span>
                                <span class="block text-sm font-semibold text-[color:var(--primary)]">Chuyển khoản ngân hàng qua QR</span>
                                <span class="mt-1 block text-xs text-[color:var(--text-muted)]">Mã QR đúng số tiền sẽ hiện sau khi đặt đơn. Cửa hàng xác nhận thanh toán thủ công.</span>
                            </span>
                        </label>
                    @endif
                </div>
            </fieldset>
        </div>
        <div class="shop-shell p-5 sm:p-6">
            <h2 class="mb-4 text-lg font-semibold text-[color:var(--primary)]">Tóm tắt</h2>
            <div class="space-y-2 rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-[#f7f7f9] p-4 text-sm">
                <p class="flex justify-between"><span>Tạm tính</span><span>{{ number_format($totals['subtotal'], 0, ',', '.') }} ₫</span></p>
                <p class="flex justify-between"><span>Giảm giá</span><span>-{{ number_format($totals['discount'], 0, ',', '.') }} ₫</span></p>
                <p class="flex justify-between"><span>Phí ship</span><span>{{ number_format($totals['shipping'], 0, ',', '.') }} ₫</span></p>
                <p class="flex justify-between border-t border-[color:var(--line-soft)] pt-3 text-base font-bold text-[color:var(--primary)]"><span>Tổng cộng</span><span>{{ number_format($totals['total'], 0, ',', '.') }} ₫</span></p>
                <p class="pt-2 text-xs text-[color:var(--text-muted)]">Hình thức: thanh toán khi nhận hàng (COD).</p>
            </div>
            <button class="primary-cta mt-6 w-full justify-center" type="submit">Đặt hàng</button>
            @if($totals['is_free_shipping'])
                <p class="mt-4 text-sm font-semibold text-emerald-700">Đơn hàng này được miễn phí vận chuyển.</p>
            @elseif($totals['free_shipping_threshold'] > 0)
                <p class="mt-4 text-xs text-[color:var(--text-muted)]">Mua thêm {{ number_format($totals['remaining_for_free_shipping'], 0, ',', '.') }} ₫ để được miễn phí vận chuyển.</p>
            @endif
        </div>
    </form>
@endsection
