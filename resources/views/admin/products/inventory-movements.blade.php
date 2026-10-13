@extends('layouts.admin')

@section('title', 'Lịch sử tồn kho')

@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div><h1 class="admin-page-title">Lịch sử tồn kho</h1><p class="admin-page-lead">Theo dõi các lần nhập, bán, hoàn hàng và điều chỉnh tồn kho.</p></div>
            <a href="{{ route('admin.products.index') }}" class="admin-btn-outline">Quản lý sản phẩm</a>
        </div>
        <div class="admin-table-wrap overflow-x-auto">
            <table class="admin-table">
                <thead><tr><th>Thời gian</th><th>Sản phẩm / biến thể</th><th>Loại</th><th>Số lượng</th><th>Tham chiếu</th></tr></thead>
                <tbody>
                    @forelse($movements as $movement)
                        @php $labels = ['in' => ['Nhập kho', 'bg-emerald-100 text-emerald-800'], 'out' => ['Xuất kho', 'bg-rose-100 text-rose-800'], 'return' => ['Hoàn kho', 'bg-blue-100 text-blue-800']]; @endphp
                        <tr>
                            <td class="whitespace-nowrap text-slate-500">{{ $movement->moved_at?->format('d/m/Y H:i') }}</td>
                            <td><strong class="text-slate-900">{{ $movement->variant?->product?->name ?? 'Sản phẩm đã ẩn' }}</strong><span class="ml-2 text-xs text-slate-500">{{ $movement->variant?->size }} / {{ $movement->variant?->color }}</span></td>
                            <td><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $labels[$movement->type][1] ?? 'bg-slate-100 text-slate-700' }}">{{ $labels[$movement->type][0] ?? $movement->type }}</span></td>
                            <td class="font-semibold">{{ $movement->type === 'out' ? '-' : '+' }}{{ number_format($movement->quantity) }}</td>
                            <td class="text-xs text-slate-500">{{ $movement->reference_type ?? '—' }}{{ $movement->reference_id ? ' #'.$movement->reference_id : '' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="py-10 text-center text-slate-500">Chưa có lịch sử tồn kho.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $movements->links() }}</div>
    </div>
@endsection
