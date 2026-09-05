@extends('layouts.shop')

@section('title', 'Địa chỉ — '.config('app.name'))

@section('content')
    <div class="nd-shop-page-head">
        <h1 class="nd-shop-page-title">Địa chỉ đã lưu</h1>
        <p class="nd-shop-page-lead">Dùng nhanh khi thanh toán — bạn có thể đặt một địa chỉ mặc định.</p>
    </div>

    <div class="mx-auto max-w-2xl space-y-8">
        <section class="shop-shell p-5 sm:p-6">
            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-[color:var(--text-muted)]">Thêm địa chỉ mới</h2>
            <form method="post" action="{{ route('addresses.store') }}" class="space-y-3">@csrf
                <input class="field-control" name="full_name" required placeholder="Họ và tên" value="{{ old('full_name', auth()->user()->name) }}" />
                <input class="field-control" name="phone" required placeholder="Điện thoại" value="{{ old('phone', auth()->user()->phone) }}" />
                <input class="field-control" name="line1" required placeholder="Địa chỉ dòng 1" value="{{ old('line1') }}" />
                <input class="field-control" name="line2" placeholder="Địa chỉ dòng 2 (tuỳ chọn)" value="{{ old('line2') }}" />
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <input class="field-control" name="city" required placeholder="Thành phố / Tỉnh" value="{{ old('city') }}" />
                    <input class="field-control" name="state" placeholder="Quận / Huyện" value="{{ old('state') }}" />
                </div>
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <input class="field-control" name="postal_code" required placeholder="Mã bưu điện" value="{{ old('postal_code') }}" />
                    <input class="field-control uppercase" name="country" value="{{ old('country', 'VN') }}" required maxlength="2" placeholder="Quốc gia (ISO)" />
                </div>
                <label class="flex items-center gap-2 text-sm text-[color:var(--text-main)]">
                    <input type="checkbox" name="is_default" value="1" class="rounded border-[color:var(--line-soft)] text-[color:var(--accent)] focus:ring-[color:var(--accent)]" />
                    Đặt làm mặc định
                </label>
                <button class="primary-cta" type="submit">Lưu địa chỉ</button>
            </form>
        </section>

        <ul class="space-y-4">
            @foreach($addresses as $a)
                <li class="shop-shell p-5">
                    @if($a->is_default)
                        <span class="mb-3 inline-block rounded-full bg-[color:var(--accent)]/15 px-2.5 py-0.5 text-xs font-semibold text-[color:var(--accent)]">Mặc định</span>
                    @endif
                    <form method="post" action="{{ route('addresses.update', $a) }}" class="space-y-3">@csrf @method('PATCH')
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <input class="field-control" name="full_name" value="{{ $a->full_name }}" required />
                            <input class="field-control" name="phone" value="{{ $a->phone }}" required />
                        </div>
                        <input class="field-control" name="line1" value="{{ $a->line1 }}" required />
                        <input class="field-control" name="line2" value="{{ $a->line2 }}" />
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <input class="field-control" name="city" value="{{ $a->city }}" required />
                            <input class="field-control" name="state" value="{{ $a->state }}" />
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <input class="field-control" name="postal_code" value="{{ $a->postal_code }}" required />
                            <input class="field-control uppercase" name="country" value="{{ $a->country }}" required maxlength="2" />
                        </div>
                        <label class="flex items-center gap-2 text-sm text-[color:var(--text-main)]">
                            <input type="checkbox" name="is_default" value="1" class="rounded border-[color:var(--line-soft)] text-[color:var(--accent)] focus:ring-[color:var(--accent)]" @checked($a->is_default) />
                            Địa chỉ giao hàng mặc định
                        </label>
                        <div class="flex flex-wrap gap-2 pt-1">
                            <button class="rounded-[var(--radius-ui)] bg-[color:var(--primary)] px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-[#16213e]" type="submit">Cập nhật</button>
                        </div>
                    </form>
                    <form method="post" action="{{ route('addresses.destroy', $a) }}" class="mt-4 border-t border-[color:var(--line-soft)] pt-4" onsubmit="return confirm('Xóa địa chỉ này?')">@csrf @method('DELETE')
                        <button class="text-sm font-semibold text-red-600 hover:underline" type="submit">Xóa địa chỉ</button>
                    </form>
                </li>
            @endforeach
        </ul>
    </div>
@endsection
