@extends('layouts.admin')

@section('title', 'Tổng quan')

@section('content')
    @php
        $trend = fn (float $value) => $value >= 0 ? 'text-emerald-600' : 'text-rose-600';
        $trendIcon = fn (float $value) => $value >= 0 ? '↑' : '↓';
        $ordersTotal = max(1, (int) $ordersByStatus->sum());
        $cancelled = (int) ($ordersByStatus['cancelled'] ?? 0);
        $completed = (int) ($ordersByStatus['completed'] ?? 0);
        $pending = (int) ($ordersByStatus['pending'] ?? 0);
        $cancelledEnd = round($cancelled / $ordersTotal * 100, 2);
        $completedEnd = round(($cancelled + $completed) / $ordersTotal * 100, 2);
        $statusColors = ['cancelled' => 'bg-rose-400', 'completed' => 'bg-emerald-400', 'pending' => 'bg-amber-400', 'processing' => 'bg-violet-400', 'shipping' => 'bg-sky-400'];
    @endphp

    <div class="admin-shell space-y-6">
        <div class="flex flex-col justify-between gap-4 sm:flex-row sm:items-end">
            <div><h1 class="admin-page-title">Tổng quan</h1><p class="admin-page-lead">Theo dõi tình hình kinh doanh của cửa hàng</p></div>
            <form method="get" class="flex items-center gap-2"><label for="period" class="sr-only">Kỳ báo cáo</label><span class="text-xs text-slate-500">Kỳ báo cáo</span><select id="period" name="period" onchange="this.form.submit()" class="rounded-lg border-slate-200 bg-white px-3 py-2 text-xs font-semibold text-slate-700 shadow-sm focus:border-blue-400 focus:ring-blue-400">@foreach($periods as $key => $label)<option value="{{ $key }}" @selected($period === $key)>{{ $label }}</option>@endforeach</select></form>
        </div>

        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-4">
            <div class="admin-card border-blue-100 bg-blue-50/30 p-4"><div class="flex items-center justify-between"><div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-xl bg-blue-500 text-lg text-white shadow-sm" aria-hidden="true">₫</span><p class="text-xs font-medium text-slate-600">Doanh thu {{ $period === '7' || $period === '30' ? 'trong kỳ' : '' }}</p></div><span class="text-xl text-blue-400" aria-hidden="true">⌁</span></div><p class="mt-4 text-2xl font-bold text-slate-900">{{ number_format($currentRevenue, 0, ',', '.') }} <span class="text-sm font-normal text-slate-500">₫</span></p><p class="mt-2 text-xs font-semibold {{ $trend($revenueChange) }}">{{ $trendIcon($revenueChange) }} {{ number_format(abs($revenueChange), 1, ',', '.') }}% <span class="font-normal text-slate-500">so với kỳ trước</span></p></div>
            <div class="admin-card border-emerald-100 bg-emerald-50/30 p-4"><div class="flex items-center justify-between"><div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-xl bg-emerald-500 text-lg text-white shadow-sm" aria-hidden="true">▣</span><p class="text-xs font-medium text-slate-600">Lợi nhuận gộp</p></div><span class="text-xl text-emerald-400" aria-hidden="true">⌁</span></div><p class="mt-4 text-2xl font-bold text-slate-900">{{ number_format($currentProfit, 0, ',', '.') }} <span class="text-sm font-normal text-slate-500">₫</span></p><p class="mt-2 text-xs font-semibold {{ $trend($profitChange) }}">{{ $trendIcon($profitChange) }} {{ number_format(abs($profitChange), 1, ',', '.') }}% <span class="font-normal text-slate-500">so với kỳ trước</span></p></div>
            <div class="admin-card border-violet-100 bg-violet-50/30 p-4"><div class="flex items-center justify-between"><div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-xl bg-violet-500 text-lg text-white shadow-sm" aria-hidden="true">▣</span><p class="text-xs font-medium text-slate-600">Đơn hàng</p></div><span class="text-xl text-violet-400" aria-hidden="true">⌁</span></div><p class="mt-4 text-2xl font-bold text-slate-900">{{ number_format($currentOrders, 0, ',', '.') }}</p><p class="mt-2 text-xs font-semibold {{ $trend($ordersChange) }}">{{ $trendIcon($ordersChange) }} {{ number_format(abs($ordersChange), 1, ',', '.') }}% <span class="font-normal text-slate-500">so với kỳ trước</span></p></div>
            <div class="admin-card border-orange-100 bg-orange-50/30 p-4"><div class="flex items-center justify-between"><div class="flex items-center gap-3"><span class="grid h-10 w-10 place-items-center rounded-xl bg-orange-400 text-lg text-white shadow-sm" aria-hidden="true">♙</span><p class="text-xs font-medium text-slate-600">Người dùng</p></div><span class="text-xl text-orange-400" aria-hidden="true">⌁</span></div><p class="mt-4 text-2xl font-bold text-slate-900">{{ number_format($userCount, 0, ',', '.') }}</p><p class="mt-2 text-xs font-semibold text-emerald-600">↑ <span class="font-normal text-slate-500">Tài khoản đã đăng ký</span></p></div>
        </div>

        <div class="grid gap-5 xl:grid-cols-[minmax(0,1.7fr)_minmax(300px,1fr)]">
            <div class="admin-card p-5"><div class="mb-3 flex items-center justify-between"><div><h2 class="text-sm font-bold text-slate-800">Doanh thu theo ngày</h2><p class="mt-1 text-xs text-slate-500">Đơn hoàn thành · {{ $periods[$period] }}</p></div><span class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-xs font-semibold text-slate-600">{{ $periods[$period] }}</span></div><div id="revenue-chart-wrap" class="relative h-[340px] w-full overflow-visible lg:h-[390px]"><svg id="revenue-chart" viewBox="0 0 900 300" preserveAspectRatio="none" class="h-full w-full" role="img" aria-label="Biểu đồ doanh thu theo thời gian"><g id="revenue-grid"></g><line x1="48" y1="20" x2="48" y2="255" stroke="#e2e8f0"/><g id="revenue-bars"></g><g id="revenue-y-labels" fill="#64748b" font-size="9" font-family="Inter, ui-sans-serif, system-ui, sans-serif" font-weight="500" text-anchor="end"></g><g id="revenue-labels" fill="#64748b" font-size="8" font-family="Inter, ui-sans-serif, system-ui, sans-serif" font-weight="700" text-anchor="middle"></g></svg><div id="revenue-tooltip" class="revenue-tooltip pointer-events-none absolute z-10 -translate-x-1/2 -translate-y-full rounded-lg bg-slate-900 px-3 py-2 text-xs font-semibold text-white shadow-lg" style="opacity: 0"><div id="revenue-tooltip-date"></div><div id="revenue-tooltip-value" class="mt-0.5 text-rose-200"></div></div></div></div>
            <div class="admin-card p-5"><div class="mb-4"><h2 class="text-sm font-bold text-slate-800">Đơn hàng theo trạng thái</h2><p class="mt-1 text-xs text-slate-500">{{ $periods[$period] }} · {{ $ordersTotal }} tổng đơn</p></div><div class="flex items-center gap-5"><div class="relative grid h-44 w-44 shrink-0 place-items-center rounded-full" style="background: conic-gradient(#fb7185 0 {{ $cancelledEnd }}%, #34d399 {{ $cancelledEnd }}% {{ $completedEnd }}%, #fbbf24 {{ $completedEnd }}% 100%);"><div class="grid h-28 w-28 place-items-center rounded-full bg-white text-center"><strong class="text-2xl text-slate-800">{{ $ordersTotal }}</strong><span class="text-[10px] text-slate-500">Tổng đơn hàng</span></div></div><div class="min-w-0 flex-1 space-y-3 text-xs">@foreach(\App\Enums\OrderStatus::cases() as $status) @php $count = (int) ($ordersByStatus[$status->value] ?? 0); @endphp <div class="flex items-center gap-2"><span class="h-2.5 w-2.5 shrink-0 rounded-full {{ $statusColors[$status->value] ?? 'bg-slate-300' }}"></span><span class="min-w-0 flex-1 truncate text-slate-600">{{ $status->label() }}</span><strong class="text-slate-800">{{ $count }}</strong><span class="w-8 text-right text-slate-400">{{ round($count / $ordersTotal * 100) }}%</span></div>@endforeach</div></div></div>
        </div>

        <div class="grid gap-4 xl:grid-cols-3">
            <div class="admin-card p-0"><div class="flex items-center justify-between border-b border-slate-100 px-4 py-4"><h2 class="text-sm font-bold text-slate-800">★ &nbsp;Sản phẩm bán chạy</h2><a href="{{ route('admin.products.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Xem tất cả</a></div><div class="divide-y divide-slate-100">@forelse($topProducts as $index => $product)<div class="flex items-center gap-3 px-4 py-3 text-xs"><span class="w-4 text-slate-400">{{ $index + 1 }}</span><span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-slate-100 text-lg" aria-hidden="true">▧</span><span class="min-w-0 flex-1 truncate"><strong class="block truncate text-slate-800">{{ $product->name }}</strong><span class="text-slate-400">Sản phẩm thời trang</span></span><strong class="text-slate-700">{{ $product->sold }}</strong></div>@empty<div class="px-4 py-8 text-center text-sm text-slate-500">Chưa có dữ liệu.</div>@endforelse</div></div>
            <div class="admin-card p-0"><div class="flex items-center justify-between border-b border-slate-100 px-4 py-4"><h2 class="text-sm font-bold text-slate-800">▤ &nbsp;Đơn hàng gần đây</h2><a href="{{ route('admin.orders.index') }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Xem tất cả</a></div><div class="divide-y divide-slate-100">@forelse($recentOrders as $order)<div class="flex items-center gap-2 px-4 py-3 text-xs"><span class="min-w-0 flex-1"><strong class="block truncate text-slate-800">#{{ $order->order_number }}</strong><span class="text-slate-400">{{ $order->user?->name ?? $order->user?->email ?? 'Khách hàng' }}</span></span><strong class="text-slate-700">{{ number_format($order->total, 0, ',', '.') }} ₫</strong><span class="rounded-full px-2 py-1 {{ $order->status === 'completed' ? 'bg-emerald-50 text-emerald-700' : ($order->status === 'cancelled' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">{{ $order->statusEnum()?->label() ?? $order->status }}</span></div>@empty<div class="px-4 py-8 text-center text-sm text-slate-500">Chưa có đơn hàng.</div>@endforelse</div></div>
            <div class="admin-card p-0"><div class="flex items-center justify-between border-b border-slate-100 px-4 py-4"><h2 class="text-sm font-bold text-slate-800">⚠ &nbsp;Cảnh báo tồn kho</h2><a href="{{ route('admin.products.index', ['availability' => 'low_stock']) }}" class="text-xs font-semibold text-blue-600 hover:text-blue-700">Xem tất cả</a></div><div class="divide-y divide-slate-100">@forelse($lowStock->take(5) as $variant)<div class="flex items-center gap-3 px-4 py-3 text-xs"><span class="grid h-10 w-10 shrink-0 place-items-center rounded-lg bg-slate-100 text-lg" aria-hidden="true">▧</span><span class="min-w-0 flex-1 truncate"><strong class="block truncate text-slate-800">{{ $variant->product->name }} — {{ $variant->size }}/{{ $variant->color }}</strong><span class="text-slate-400">Tồn kho</span></span><span class="rounded-full px-2 py-1 {{ $variant->stock <= 2 ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-700' }}">{{ $variant->stock <= 2 ? 'Rất thấp' : 'Sắp hết' }}</span><strong class="text-slate-700">{{ $variant->stock }}</strong></div>@empty<div class="px-4 py-8 text-center text-sm text-slate-500">Tồn kho ổn định.</div>@endforelse</div></div>
        </div>

        <div class="admin-card"><div class="mb-3 flex items-center justify-between"><div><h2 class="text-sm font-bold text-slate-800">Thao tác nhanh</h2><p class="mt-1 text-xs text-slate-500">Các công việc thường dùng</p></div></div><div class="grid gap-3 sm:grid-cols-3"><a href="{{ route('admin.products.create') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50"><span class="rounded-lg bg-blue-100 px-2 py-1 text-lg" aria-hidden="true">＋</span>Thêm sản phẩm</a><a href="{{ route('admin.orders.index') }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50"><span class="rounded-lg bg-violet-100 px-2 py-1 text-lg" aria-hidden="true">▣</span>Xem đơn hàng</a><a href="{{ route('admin.products.index', ['availability' => 'low_stock']) }}" class="flex items-center gap-3 rounded-xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 transition hover:border-blue-300 hover:bg-blue-50"><span class="rounded-lg bg-amber-100 px-2 py-1 text-lg" aria-hidden="true">▤</span>Xem tồn kho</a></div></div>
    </div>

    <script>
        (() => {
            const chart = @json($chart), barsGroup = document.getElementById('revenue-bars'), gridGroup = document.getElementById('revenue-grid'), yLabelsGroup = document.getElementById('revenue-y-labels'), labelsGroup = document.getElementById('revenue-labels'), chartWrap = document.getElementById('revenue-chart-wrap'), tooltip = document.getElementById('revenue-tooltip'), tooltipDate = document.getElementById('revenue-tooltip-date'), tooltipValue = document.getElementById('revenue-tooltip-value');
            if (!barsGroup || !chart.values.length) return;
            const width = 900, height = 300, left = 48, right = 20, top = 20, bottom = 45, baseline = height - bottom;
            const niceNum = (value, round) => {
                const exponent = Math.floor(Math.log10(Math.max(value, 1)));
                const fraction = value / Math.pow(10, exponent);
                const niceFraction = round
                    ? (fraction < 1.5 ? 1 : fraction < 3 ? 2 : fraction < 7 ? 5 : 10)
                    : (fraction <= 1 ? 1 : fraction <= 2 ? 2 : fraction <= 5 ? 5 : 10);
                return niceFraction * Math.pow(10, exponent);
            };
            const niceScale = (maxValue, maxTicks = 4) => {
                const range = niceNum(maxValue, false);
                const step = niceNum(range / (maxTicks - 1), true);
                return { step, niceMax: Math.ceil(maxValue / step) * step };
            };
            const scale = niceScale(Math.max(...chart.values, 1));
            const tickCount = Math.max(1, Math.ceil(scale.niceMax / scale.step));
            const scaleMax = scale.step * tickCount;
            const formatAxisValue = (value) => {
                if (value === 0) return '0 ₫';
                if (value >= 1000000) {
                    const millions = (value / 1000000).toFixed(1).replace('.', ',').replace(',0', '');
                    return `${millions}tr`;
                }
                return `${new Intl.NumberFormat('vi-VN').format(value)} ₫`;
            };
            const step = (width - left - right) / chart.values.length;
            const barWidth = Math.max(8, Math.min(30, step * 0.58));
            for (let tick = 0; tick <= tickCount; tick += 1) {
                const y = baseline - (tick / tickCount) * (baseline - top);
                const gridLine = document.createElementNS('http://www.w3.org/2000/svg', 'line');
                gridLine.setAttribute('x1', left);
                gridLine.setAttribute('x2', width - right);
                gridLine.setAttribute('y1', y);
                gridLine.setAttribute('y2', y);
                gridLine.setAttribute('stroke', '#e2e8f0');
                gridGroup.appendChild(gridLine);
                const yLabel = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                yLabel.setAttribute('x', left - 7);
                yLabel.setAttribute('y', y + 3);
                yLabel.textContent = formatAxisValue(scale.step * tick);
                yLabelsGroup.appendChild(yLabel);
            }
            const showEvery = Math.max(1, Math.ceil(chart.labels.length / 7));
            chart.values.forEach((value, index) => {
                const x = left + index * step + (step - barWidth) / 2;
                const barHeight = value > 0 ? Math.max(4, (value / scaleMax) * (baseline - top)) : 0;
                const y = baseline - barHeight;
                const bar = document.createElementNS('http://www.w3.org/2000/svg', 'rect');
                bar.setAttribute('x', x);
                bar.setAttribute('y', y);
                bar.setAttribute('width', barWidth);
                bar.setAttribute('height', '0');
                bar.setAttribute('data-target-height', barHeight);
                bar.setAttribute('rx', '2');
                bar.setAttribute('fill', '#e94560');
                bar.setAttribute('class', 'revenue-bar cursor-pointer');
                bar.addEventListener('mouseenter', () => {
                    const svgRect = document.getElementById('revenue-chart').getBoundingClientRect(), wrapRect = chartWrap.getBoundingClientRect();
                    tooltip.style.left = `${((x + barWidth / 2) / width) * svgRect.width + svgRect.left - wrapRect.left}px`;
                    tooltip.style.top = `${(y / height) * svgRect.height + svgRect.top - wrapRect.top - 10}px`;
                    tooltipDate.textContent = chart.labels[index];
                    tooltipValue.textContent = `${new Intl.NumberFormat('vi-VN').format(chart.values[index])} ₫`;
                    tooltip.style.opacity = '1';
                    bar.setAttribute('opacity', '0.75');
                });
                bar.addEventListener('mouseleave', () => { tooltip.style.opacity = '0'; bar.removeAttribute('opacity'); });
                barsGroup.appendChild(bar);
                requestAnimationFrame(() => {
                    const duration = 400, startedAt = performance.now();
                    const animateBar = (now) => {
                        const progress = Math.min(1, (now - startedAt) / duration);
                        const eased = 1 - Math.pow(1 - progress, 4);
                        const currentHeight = barHeight * eased;
                        bar.setAttribute('y', baseline - currentHeight);
                        bar.setAttribute('height', currentHeight);
                        if (progress < 1) requestAnimationFrame(animateBar);
                    };
                    requestAnimationFrame(animateBar);
                });
                if (index % showEvery === 0 || index === chart.labels.length - 1) {
                    const label = document.createElementNS('http://www.w3.org/2000/svg', 'text');
                    label.setAttribute('x', x + barWidth / 2);
                    label.setAttribute('y', height - 15);
                    label.textContent = chart.labels[index];
                    labelsGroup.appendChild(label);
                }
            });
        })();
    </script>
@endsection
