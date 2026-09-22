@extends('layouts.admin')
@section('title', 'Thêm mã giảm giá')
@section('content')
    <div class="admin-shell max-w-lg">
        <div class="admin-page-head">
            <h1 class="admin-page-title">Thêm mã giảm giá</h1>
            <a href="{{ route('admin.coupons.index') }}" class="admin-btn-outline shrink-0">← Danh sách</a>
        </div>
        <div class="admin-card space-y-4">
            <form method="post" action="{{ route('admin.coupons.store') }}" class="space-y-4 text-sm">@csrf
                <div>
                    <label class="admin-label" for="code">Mã</label>
                    <input id="code" class="admin-input font-mono uppercase" name="code" required placeholder="VD: WELCOME10" value="{{ old('code') }}" />
                </div>
                <div>
                    <label class="admin-label" for="kind">Loại voucher</label>
                    <select id="kind" name="kind" class="admin-input" required>
                        <option value="product" @selected(old('kind', 'product') === 'product')>Giảm giá sản phẩm</option>
                        <option value="shipping" @selected(old('kind') === 'shipping')>Giảm giá vận chuyển</option>
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="type">Loại</label>
                    <select id="type" name="type" class="admin-input" required>
                        <option value="percent" @selected(old('type') === 'percent')>Giảm theo %</option>
                        <option value="fixed" @selected(old('type') === 'fixed')>Giảm số tiền cố định</option>
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="value">Giá trị</label>
                    <input id="value" class="admin-input" name="value" type="number" step="0.01" required value="{{ old('value') }}" />
                </div>
                <div>
                    <label class="admin-label" for="min_order_amount">Đơn tối thiểu (₫)</label>
                    <input id="min_order_amount" class="admin-input" name="min_order_amount" type="number" step="0.01" value="{{ old('min_order_amount', 0) }}" />
                </div>
                <div>
                    <label class="admin-label" for="max_uses">Giới hạn lượt dùng</label>
                    <input id="max_uses" class="admin-input" name="max_uses" type="number" placeholder="Để trống = không giới hạn" value="{{ old('max_uses') }}" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="admin-label" for="starts_at">Bắt đầu</label>
                        <input id="starts_at" class="admin-input" name="starts_at" type="datetime-local" value="{{ old('starts_at') }}" />
                    </div>
                    <div>
                        <label class="admin-label" for="ends_at">Kết thúc</label>
                        <input id="ends_at" class="admin-input" name="ends_at" type="datetime-local" value="{{ old('ends_at') }}" />
                    </div>
                </div>
                <label class="flex items-center gap-2 text-slate-700">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-orange-600" checked /> Đang kích hoạt
                </label>
                <button class="admin-btn" type="submit">Lưu</button>
            </form>
        </div>
    </div>
@endsection
