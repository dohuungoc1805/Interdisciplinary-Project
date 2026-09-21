@extends('layouts.admin')
@section('title', $order->order_number)
@section('content')
    <div class="admin-shell max-w-3xl space-y-6">
        <a href="{{ route('admin.orders.index') }}" class="admin-link text-sm">← Danh sách đơn hàng</a>
        <div>
            <h1 class="admin-page-title">{{ $order->order_number }}</h1>
            <p class="admin-page-lead">Khách: {{ $order->user?->email ?? '—' }} — {{ $order->created_at?->format('d/m/Y H:i') }}</p>
        </div>

        <div class="admin-card space-y-4">
            <h2 class="text-sm font-bold text-slate-800">Cập nhật đơn</h2>
            <div class="flex flex-wrap items-center justify-between gap-3 rounded-xl bg-slate-50 p-4 text-sm">
                <div>
                    <p class="font-semibold text-slate-800">{{ $order->paymentMethodLabel() }}</p>
                    <p class="mt-1 text-slate-500">{{ $order->paymentStatusLabel() }}</p>
                </div>
                @if($order->payment_method === 'bank_transfer' && $order->payment_status !== 'paid')
                    <form method="post" action="{{ route('admin.orders.confirm-bank-transfer', $order) }}">@csrf
                        <button class="admin-btn" type="submit" onclick="return confirm('Xác nhận đã nhận đúng số tiền chuyển khoản cho đơn này?')">Xác nhận đã nhận tiền</button>
                    </form>
                @elseif($order->payment_confirmed_at)
                    <span class="text-xs font-medium text-emerald-700">Đã xác nhận: {{ $order->payment_confirmed_at->format('d/m/Y H:i') }}</span>
                @endif
            </div>
            <form method="post" action="{{ route('admin.orders.update', $order) }}" class="space-y-4">@csrf @method('PATCH')
                <div>
                    <label class="admin-label" for="status">Trạng thái</label>
                    <select id="status" name="status" class="admin-input">@foreach(\App\Enums\OrderStatus::cases() as $s)<option value="{{ $s->value }}" @selected($order->status===$s->value)>{{ $s->label() }}</option>@endforeach</select>
                </div>
                <div>
                    <label class="admin-label" for="tracking_number">Mã vận đơn</label>
                    <input id="tracking_number" name="tracking_number" class="admin-input" value="{{ old('tracking_number', $order->tracking_number) }}" />
                </div>
                <div>
                    <label class="admin-label" for="admin_note">Ghi chú nội bộ</label>
                    <textarea id="admin_note" name="admin_note" class="admin-input min-h-[80px]" rows="3">{{ old('admin_note', $order->admin_note) }}</textarea>
                </div>
                <button class="admin-btn" type="submit">Lưu thay đổi</button>
            </form>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 text-sm font-bold text-slate-800">Sản phẩm trong đơn</h2>
            <ul class="divide-y divide-slate-100 text-sm">
                @foreach($order->items as $i)
                    <li class="flex justify-between py-3"><span>{{ $i->name }} × {{ $i->quantity }}</span><span class="font-medium">{{ number_format((float) $i->line_total, 0, ',', '.') }} ₫</span></li>
                @endforeach
            </ul>
            <p class="mt-4 border-t border-slate-100 pt-4 text-right text-sm font-bold text-slate-900">Tổng: {{ number_format((float) $order->total, 0, ',', '.') }} ₫ <span class="font-normal text-slate-500">({{ $order->payment_method }})</span></p>
        </div>
    </div>
@endsection
