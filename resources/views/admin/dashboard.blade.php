@extends('layouts.admin')

@section('title', 'Tổng quan')

@section('content')
    <div class="admin-shell space-y-8">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Tổng quan</h1>
                <p class="admin-page-lead">Doanh thu, đơn hàng theo trạng thái và cảnh báo tồn kho.</p>
            </div>
        </div>

        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            <div class="admin-card border-orange-100/80 bg-gradient-to-br from-white to-orange-50/40">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Doanh thu 7 ngày</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($revenue7, 0, ',', '.') }} <span class="text-base font-normal text-slate-500">₫</span></p>
            </div>
            <div class="admin-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Doanh thu 30 ngày</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($revenue30, 0, ',', '.') }} <span class="text-base font-normal text-slate-500">₫</span></p>
            </div>
            <div class="admin-card border-emerald-100/80 bg-gradient-to-br from-white to-emerald-50/50">
                <p class="text-xs font-semibold uppercase tracking-wide text-emerald-700">Lợi nhuận gộp 30 ngày</p>
                <p class="mt-2 text-2xl font-bold text-emerald-700">{{ number_format($grossProfit30, 0, ',', '.') }} <span class="text-base font-normal text-emerald-600">₫</span></p>
            </div>
            <div class="admin-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Tổng tồn kho</p>
                <p class="mt-2 text-2xl font-bold text-slate-900">{{ number_format($totalInventoryUnits, 0, ',', '.') }}</p>
            </div>
            <div class="admin-card">
                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">SKU hết hàng</p>
                <p class="mt-2 text-2xl font-bold text-rose-600">{{ number_format($outOfStockCount, 0, ',', '.') }}</p>
            </div>
            <div class="admin-card border-orange-200/60 bg-gradient-to-br from-orange-50 to-white">
                <p class="text-xs font-semibold uppercase tracking-wide text-orange-800/90">Người dùng đã đăng ký</p>
                <p class="mt-2 text-2xl font-bold text-orange-600">{{ $userCount }}</p>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="admin-card">
                <h2 class="mb-4 text-sm font-bold text-slate-800">Đơn theo trạng thái</h2>
                <ul class="space-y-2 text-sm">
                    @foreach($ordersByStatus as $s => $c)
                        <li class="flex justify-between rounded-xl bg-slate-50 px-4 py-2.5">
                            <span class="text-slate-600">{{ \App\Enums\OrderStatus::tryFrom($s)?->label() ?? $s }}</span>
                            <span class="font-semibold text-slate-900">{{ $c }}</span>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="admin-card">
                <h2 class="mb-4 text-sm font-bold text-slate-800">Tồn kho thấp (≤ {{ config('shop.low_stock_threshold') }})</h2>
                <ul class="max-h-52 space-y-2 overflow-y-auto text-sm">
                    @forelse($lowStock as $v)
                        <li class="rounded-xl border border-amber-100 bg-amber-50 px-3 py-2 text-amber-950">
                            {{ $v->product->name }} — {{ $v->size }}/{{ $v->color }}: <strong>{{ $v->stock }}</strong>
                        </li>
                    @empty
                        <li class="text-slate-500">Mọi biến thể đều trên ngưỡng.</li>
                    @endforelse
                </ul>
            </div>
        </div>

        <div class="admin-card">
            <h2 class="mb-4 text-sm font-bold text-slate-800">Doanh thu theo ngày (30 ngày gần nhất)</h2>
            <div class="max-h-64 overflow-y-auto rounded-xl border border-slate-100">
                <table class="min-w-full text-sm">
                    <thead class="bg-slate-50 text-slate-600">
                        <tr>
                            <th class="px-4 py-2 text-left font-semibold">Ngày</th>
                            <th class="px-4 py-2 text-right font-semibold">Doanh thu</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($dailyRevenue->reverse() as $day => $total)
                            <tr class="border-t border-slate-100">
                                <td class="px-4 py-2 text-slate-700">{{ \Illuminate\Support\Carbon::parse($day)->format('d/m/Y') }}</td>
                                <td class="px-4 py-2 text-right font-medium text-slate-900">{{ number_format($total, 0, ',', '.') }} ₫</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            <div class="admin-card">
                <h2 class="mb-4 text-sm font-bold text-slate-800">Sản phẩm bán chạy (theo số lượng)</h2>
                <ol class="list-inside list-decimal space-y-2 text-sm text-slate-700">
                    @foreach($topProducts as $r)
                        <li><span class="font-medium text-slate-900">{{ $r->name }}</span> — {{ $r->sold }} đã bán</li>
                    @endforeach
                </ol>
            </div>

            <div class="admin-card">
                <h2 class="mb-4 text-sm font-bold text-slate-800">Sản phẩm bán chậm (30 ngày)</h2>
                <ul class="max-h-56 space-y-2 overflow-y-auto text-sm">
                    @forelse($slowProducts as $row)
                        <li class="rounded-xl border border-slate-100 bg-slate-50 px-3 py-2">
                            <div class="font-medium text-slate-900">{{ $row->name }} — {{ $row->size }}/{{ $row->color }}</div>
                            <div class="text-slate-600">Đã bán 30 ngày: <strong>{{ $row->sold_30d }}</strong> · Tồn: <strong>{{ $row->stock }}</strong></div>
                        </li>
                    @empty
                        <li class="text-slate-500">Không có dữ liệu sản phẩm bán chậm.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
@endsection
