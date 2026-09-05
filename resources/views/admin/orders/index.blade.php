@extends('layouts.admin')
@section('title', 'Đơn hàng')
@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Đơn hàng</h1>
                <p class="admin-page-lead">Lọc, xem chi tiết và cập nhật hàng loạt trạng thái.</p>
            </div>
        </div>

        <form method="get" class="admin-card mb-6 flex flex-wrap items-end gap-3">
            <div class="min-w-[10rem] flex-1">
                <label class="admin-label" for="q">Tìm</label>
                <input id="q" name="q" class="admin-input" value="{{ request('q') }}" placeholder="Mã đơn hoặc email…" />
            </div>
            <div class="min-w-[10rem]">
                <label class="admin-label" for="status">Trạng thái</label>
                <select id="status" name="status" class="admin-input">
                    <option value="">Tất cả</option>
                    @foreach(\App\Enums\OrderStatus::cases() as $s)<option value="{{ $s->value }}" @selected(request('status')===$s->value)>{{ $s->label() }}</option>@endforeach
                </select>
            </div>
            <button class="admin-btn-outline" type="submit">Lọc</button>
        </form>

        <form method="post" action="{{ route('admin.orders.bulk') }}" class="space-y-4">@csrf
            <div class="admin-card flex flex-wrap items-end gap-3">
                <div class="min-w-[12rem] flex-1">
                    <label class="admin-label" for="bulk_status">Đặt trạng thái cho đơn đã chọn</label>
                    <select id="bulk_status" name="status" class="admin-input" required>
                        @foreach(\App\Enums\OrderStatus::cases() as $s)<option value="{{ $s->value }}">{{ $s->label() }}</option>@endforeach
                    </select>
                </div>
                <button class="admin-btn" type="submit">Áp dụng</button>
            </div>

            <div class="admin-table-wrap overflow-x-auto">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th class="w-10"></th>
                            <th>Đơn</th>
                            <th>Người mua</th>
                            <th>Trạng thái</th>
                            <th>Tổng</th>
                            <th class="text-right"></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($orders as $o)
                            <tr>
                                <td><input type="checkbox" name="order_ids[]" value="{{ $o->id }}" class="rounded border-slate-300 text-orange-600" /></td>
                                <td class="font-mono text-xs">{{ $o->order_number }}</td>
                                <td class="max-w-[200px] truncate text-slate-600" title="{{ $o->user?->email }}">{{ $o->user?->email }}</td>
                                <td><span class="rounded-full bg-slate-100 px-2.5 py-0.5 text-xs font-medium text-slate-700">{{ $o->statusEnum()?->label() ?? $o->status }}</span></td>
                                <td class="font-medium">{{ number_format((float) $o->total, 0, ',', '.') }} ₫</td>
                                <td class="text-right"><a class="admin-link" href="{{ route('admin.orders.show', $o) }}">Chi tiết</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </form>
        <div class="mt-6">{{ $orders->links() }}</div>
    </div>
@endsection
