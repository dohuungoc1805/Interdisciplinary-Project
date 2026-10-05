<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $period = request()->query('period', '30');
        $periods = ['7' => '7 ngày', '30' => '30 ngày', 'month' => 'Tháng này', 'year' => 'Năm nay'];
        $period = array_key_exists($period, $periods) ? $period : '30';
        [$currentStart, $currentEnd, $previousStart, $previousEnd] = $this->periodWindow($period);

        $currentRevenue = $this->revenueBetween($currentStart, $currentEnd);
        $previousRevenue = $this->revenueBetween($previousStart, $previousEnd);
        $currentOrders = $this->ordersBetween($currentStart, $currentEnd);
        $previousOrders = $this->ordersBetween($previousStart, $previousEnd);
        $currentProfit = $this->grossProfitBetween($currentStart, $currentEnd);
        $previousProfit = $this->grossProfitBetween($previousStart, $previousEnd);

        $ordersByStatus = Order::query()
            ->whereBetween('created_at', [$currentStart, $currentEnd])
            ->select('status', DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');

        $chart = $this->revenueChart($period, $currentStart, $currentEnd);
        $threshold = (int) config('shop.low_stock_threshold', 5);
        $lowStock = ProductVariant::query()->with('product')
            ->where('stock', '<=', $threshold)->orderBy('stock')->limit(10)->get();

        $topProducts = DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->where('orders.status', OrderStatus::Completed->value)
            ->whereBetween('orders.created_at', [$currentStart, $currentEnd])
            ->select('order_items.name', DB::raw('SUM(order_items.quantity) as sold'))
            ->groupBy('order_items.name')->orderByDesc('sold')->limit(5)->get();
        $recentOrders = Order::query()->with('user:id,name,email')
            ->latest('created_at')->limit(5)->get(['id', 'order_number', 'user_id', 'total', 'status', 'created_at']);

        $slowProducts = ProductVariant::query()
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->leftJoin('order_items', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('orders', function ($join) use ($currentStart, $currentEnd) {
                $join->on('orders.id', '=', 'order_items.order_id')
                    ->where('orders.status', OrderStatus::Completed->value)
                    ->whereBetween('orders.created_at', [$currentStart, $currentEnd]);
            })
            ->where('product_variants.stock', '>', 0)
            ->select(
                'product_variants.id',
                'products.name',
                'product_variants.size',
                'product_variants.color',
                'product_variants.stock',
                DB::raw('COALESCE(SUM(CASE WHEN orders.id IS NOT NULL THEN order_items.quantity ELSE 0 END), 0) as sold_in_period')
            )
            ->groupBy('product_variants.id', 'products.name', 'product_variants.size', 'product_variants.color', 'product_variants.stock')
            ->orderBy('sold_in_period')
            ->orderByDesc('product_variants.stock')
            ->limit(10)
            ->get();

        $totalInventoryUnits = (int) ProductVariant::query()->sum('stock');
        $outOfStockCount = (int) ProductVariant::query()->where('stock', '<=', 0)->count();
        $userCount = User::query()->where('is_admin', false)->count();
        $averageOrderValue = $currentOrders > 0 ? $currentRevenue / $currentOrders : 0;

        return view('admin.dashboard', [
            'period' => $period, 'periods' => $periods,
            'currentStart' => $currentStart, 'currentEnd' => $currentEnd,
            'currentRevenue' => $currentRevenue, 'currentOrders' => $currentOrders,
            'currentProfit' => $currentProfit, 'averageOrderValue' => $averageOrderValue,
            'revenueChange' => $this->percentageChange($currentRevenue, $previousRevenue),
            'ordersChange' => $this->percentageChange($currentOrders, $previousOrders),
            'profitChange' => $this->percentageChange($currentProfit, $previousProfit),
            'ordersByStatus' => $ordersByStatus, 'chart' => $chart,
            'threshold' => $threshold, 'lowStock' => $lowStock, 'topProducts' => $topProducts, 'recentOrders' => $recentOrders, 'slowProducts' => $slowProducts,
            'totalInventoryUnits' => $totalInventoryUnits, 'outOfStockCount' => $outOfStockCount,
            'userCount' => $userCount,
        ]);
    }

    private function periodWindow(string $period): array
    {
        $now = now();
        return match ($period) {
            '7' => [$now->copy()->subDays(6)->startOfDay(), $now, $now->copy()->subDays(13)->startOfDay(), $now->copy()->subDays(7)->endOfDay()],
            'month' => [$now->copy()->startOfMonth(), $now, $now->copy()->subMonthNoOverflow()->startOfMonth(), $now->copy()->subMonthNoOverflow()->endOfMonth()],
            'year' => [$now->copy()->startOfYear(), $now, $now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            default => [$now->copy()->subDays(29)->startOfDay(), $now, $now->copy()->subDays(59)->startOfDay(), $now->copy()->subDays(30)->endOfDay()],
        };
    }

    private function revenueBetween(Carbon $start, Carbon $end): float
    {
        return (float) Order::query()->where('status', OrderStatus::Completed->value)
            ->whereBetween('created_at', [$start, $end])->sum('total');
    }

    private function ordersBetween(Carbon $start, Carbon $end): int
    {
        return (int) Order::query()->whereBetween('created_at', [$start, $end])->count();
    }

    private function grossProfitBetween(Carbon $start, Carbon $end): float
    {
        $cogs = (float) DB::table('order_items')->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->where('orders.status', OrderStatus::Completed->value)
            ->whereBetween('orders.created_at', [$start, $end])
            ->sum(DB::raw('order_items.quantity * COALESCE(product_variants.avg_cost, 0)'));
        return $this->revenueBetween($start, $end) - $cogs;
    }

    private function revenueChart(string $period, Carbon $start, Carbon $end): array
    {
        $isYear = $period === 'year';
        $format = $isYear ? '%Y-%m' : '%Y-%m-%d';
        $raw = Order::query()->where('status', OrderStatus::Completed->value)
            ->whereBetween('created_at', [$start, $end])
            ->selectRaw("DATE_FORMAT(created_at, '{$format}') as bucket, SUM(total) as total")
            ->groupBy('bucket')->pluck('total', 'bucket')->map(fn ($value) => (float) $value);
        $labels = [];
        $values = [];
        $cursor = $start->copy();
        while ($cursor->lessThanOrEqualTo($end)) {
            $key = $cursor->format($isYear ? 'Y-m' : 'Y-m-d');
            $labels[] = $isYear ? $cursor->format('m/Y') : $cursor->format('d/m');
            $values[] = $raw->get($key, 0.0);
            $cursor = $isYear ? $cursor->addMonth() : $cursor->addDay();
        }
        return ['labels' => $labels, 'values' => $values];
    }

    private function percentageChange(float|int $current, float|int $previous): float
    {
        if ((float) $previous === 0.0) return $current > 0 ? 100.0 : 0.0;
        return round((((float) $current - (float) $previous) / (float) $previous) * 100, 1);
    }
}
