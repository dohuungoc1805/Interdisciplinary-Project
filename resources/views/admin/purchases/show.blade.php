@extends('layouts.admin')
@section('title', 'Chi tiết phiếu nhập')
@section('content')
    <div class="admin-shell space-y-6">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Phiếu {{ $receipt->code }}</h1>
                <p class="admin-page-lead">{{ $receipt->supplier_name ?: 'Không có nhà cung cấp' }} · {{ $receipt->received_at?->format('d/m/Y') }}</p>
            </div>
            <a href="{{ route('admin.purchases.index') }}" class="admin-btn-outline">Quay lại</a>
        </div>

        <div class="admin-card">
            <h2 class="mb-3 text-sm font-bold text-slate-800">Hàng nhập</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-3 py-2 text-left">Sản phẩm</th>
                            <th class="px-3 py-2 text-left">Biến thể</th>
                            <th class="px-3 py-2 text-right">SL</th>
                            <th class="px-3 py-2 text-right">Giá nhập</th>
                            <th class="px-3 py-2 text-right">Thành tiền</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($receipt->items as $item)
                            <tr class="border-t border-slate-100">
                                <td class="px-3 py-2">{{ $item->variant?->product?->name }}</td>
                                <td class="px-3 py-2">{{ $item->variant?->size }}/{{ $item->variant?->color }}</td>
                                <td class="px-3 py-2 text-right">{{ $item->quantity }}</td>
                                <td class="px-3 py-2 text-right">{{ number_format($item->unit_cost, 0, ',', '.') }} ₫</td>
                                <td class="px-3 py-2 text-right font-medium">{{ number_format($item->line_total, 0, ',', '.') }} ₫</td>
                            </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr class="border-t border-slate-200 bg-slate-50">
                            <th colspan="4" class="px-3 py-2 text-right">Tổng chi phí</th>
                            <th class="px-3 py-2 text-right text-slate-900">{{ number_format($receipt->total_cost, 0, ',', '.') }} ₫</th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>

        @if($receipt->note)
            <div class="admin-card">
                <h2 class="mb-2 text-sm font-bold text-slate-800">Ghi chú</h2>
                <p class="text-sm text-slate-700">{{ $receipt->note }}</p>
            </div>
        @endif
    </div>
@endsection
