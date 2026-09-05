@extends('layouts.admin')
@section('title', 'Nhập hàng')
@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Phiếu nhập hàng</h1>
                <p class="admin-page-lead">Theo dõi chi phí nhập và tồn kho cập nhật.</p>
            </div>
            <a href="{{ route('admin.purchases.create') }}" class="admin-btn shrink-0">Tạo phiếu nhập</a>
        </div>

        <form method="get" class="admin-card mb-6 flex flex-wrap items-end gap-3">
            <div class="min-w-[12rem] flex-1">
                <label class="admin-label" for="q">Tìm kiếm</label>
                <input id="q" name="q" class="admin-input" value="{{ request('q') }}" placeholder="Mã phiếu hoặc nhà cung cấp…" />
            </div>
            <button class="admin-btn-outline" type="submit">Lọc</button>
        </form>

        <div class="admin-card divide-y divide-slate-100 p-0">
            @foreach($receipts as $receipt)
                <div class="flex flex-col gap-3 px-5 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <a href="{{ route('admin.purchases.show', $receipt) }}" class="font-semibold text-slate-900 hover:text-orange-600">{{ $receipt->code }}</a>
                        <div class="text-sm text-slate-500">
                            {{ $receipt->supplier_name ?: 'Không có NCC' }} · {{ $receipt->received_at?->format('d/m/Y') }}
                        </div>
                    </div>
                    <div class="text-sm font-semibold text-slate-900">{{ number_format($receipt->total_cost, 0, ',', '.') }} ₫</div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">{{ $receipts->links() }}</div>
    </div>
@endsection
