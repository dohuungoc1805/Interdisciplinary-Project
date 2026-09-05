@extends('layouts.admin')
@section('title', 'Sửa mã: '.$coupon->code)
@section('content')
    <div class="admin-shell max-w-lg">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Sửa mã: {{ $coupon->code }}</h1>
            </div>
            <a href="{{ route('admin.coupons.index') }}" class="admin-btn-outline shrink-0">← Danh sách</a>
        </div>
        <div class="admin-card space-y-4">
            <form method="post" action="{{ route('admin.coupons.update', $coupon) }}" class="space-y-4 text-sm">@csrf @method('PUT')
                <div>
                    <label class="admin-label" for="code">Mã</label>
                    <input id="code" class="admin-input font-mono" name="code" value="{{ old('code', $coupon->code) }}" required />
                </div>
                <div>
                    <label class="admin-label" for="type">Loại</label>
                    <select id="type" name="type" class="admin-input">@foreach(\App\Enums\CouponType::cases() as $t)<option value="{{ $t->value }}" @selected(old('type', $coupon->type) === $t->value)>{{ $t === \App\Enums\CouponType::Percent ? 'Giảm theo %' : 'Giảm số tiền cố định' }}</option>@endforeach</select>
                </div>
                <div>
                    <label class="admin-label" for="value">Giá trị</label>
                    <input id="value" class="admin-input" name="value" type="number" step="0.01" value="{{ old('value', $coupon->value) }}" required />
                </div>
                <div>
                    <label class="admin-label" for="min_order_amount">Đơn tối thiểu (₫)</label>
                    <input id="min_order_amount" class="admin-input" name="min_order_amount" type="number" step="0.01" value="{{ old('min_order_amount', $coupon->min_order_amount) }}" />
                </div>
                <div>
                    <label class="admin-label" for="used_count">Đã dùng</label>
                    <input id="used_count" class="admin-input" name="used_count" type="number" value="{{ old('used_count', $coupon->used_count) }}" />
                </div>
                <div>
                    <label class="admin-label" for="max_uses">Giới hạn lượt</label>
                    <input id="max_uses" class="admin-input" name="max_uses" type="number" value="{{ old('max_uses', $coupon->max_uses) }}" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="admin-label" for="starts_at">Bắt đầu</label>
                        <input id="starts_at" class="admin-input" name="starts_at" type="datetime-local" value="{{ old('starts_at', $coupon->starts_at?->format('Y-m-d\TH:i')) }}" />
                    </div>
                    <div>
                        <label class="admin-label" for="ends_at">Kết thúc</label>
                        <input id="ends_at" class="admin-input" name="ends_at" type="datetime-local" value="{{ old('ends_at', $coupon->ends_at?->format('Y-m-d\TH:i')) }}" />
                    </div>
                </div>
                <label class="flex items-center gap-2 text-slate-700">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-orange-600" @checked($coupon->is_active) /> Đang kích hoạt
                </label>
                <button class="admin-btn" type="submit">Cập nhật</button>
            </form>
        </div>
    </div>
@endsection
