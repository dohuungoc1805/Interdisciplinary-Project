<?php

namespace App\Http\Controllers\Admin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $ordersByStatus = Order::query()
            ->select('status', DB::raw('count(*) as c'))
            ->groupBy('status')
            ->pluck('c', 'status');
        $completedRevenueQuery = Order::query()
            ->where('status', OrderStatus::Completed->value);

        $revenue7 = (float) (clone $completedRevenueQuery)
            ->where('created_at', '>=', now()->subDays(7))
            ->sum('total');
        $revenue30 = (float) (clone $completedRevenueQuery)
            ->where('created_at', '>=', now()->subDays(30))
            ->sum('total');
        $dailyRevenueRaw = (clone $completedRevenueQuery)
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(total) as total')
            ->groupByRaw('DATE(created_at)')
            ->orderBy('day')
            ->get();
        $dailyRevenue = collect(range(0, 29))
            ->mapWithKeys(fn (int $dayOffset) => [now()->subDays(29 - $dayOffset)->toDateString() => 0.0])
            ->merge($dailyRevenueRaw->pluck('total', 'day')->map(fn ($value) => (float) $value));
        $threshold = (int) config('shop.low_stock_threshold', 5);
        $lowStock = ProductVariant::query()->with('product')
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->limit(10)
            ->get();
        $topProducts = DB::table('order_items')
            ->select('name', DB::raw('sum(quantity) as sold'))
            ->groupBy('name')
            ->orderByDesc('sold')
            ->limit(5)
            ->get();
        $totalInventoryUnits = (int) ProductVariant::query()->sum('stock');
        $outOfStockCount = (int) ProductVariant::query()->where('stock', '<=', 0)->count();

        $slowProducts = ProductVariant::query()
            ->leftJoin('order_items', 'order_items.product_variant_id', '=', 'product_variants.id')
            ->leftJoin('orders', 'orders.id', '=', 'order_items.order_id')
            ->join('products', 'products.id', '=', 'product_variants.product_id')
            ->where('product_variants.stock', '>', 0)
            ->select(
                'product_variants.id',
                'products.name',
                'product_variants.size',
                'product_variants.color',
                'product_variants.stock',
                DB::raw('COALESCE(SUM(CASE WHEN orders.status = "completed" AND orders.created_at >= "'.now()->subDays(30)->startOfDay()->toDateTimeString().'" THEN order_items.quantity ELSE 0 END), 0) as sold_30d')
            )
            ->groupBy('product_variants.id', 'products.name', 'product_variants.size', 'product_variants.color', 'product_variants.stock')
            ->orderBy('sold_30d')
            ->orderByDesc('product_variants.stock')
            ->limit(10)
            ->get();

        $cogs30 = (float) DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('product_variants', 'product_variants.id', '=', 'order_items.product_variant_id')
            ->where('orders.status', OrderStatus::Completed->value)
            ->where('orders.created_at', '>=', now()->subDays(30)->startOfDay())
            ->sum(DB::raw('order_items.quantity * COALESCE(product_variants.avg_cost, 0)'));
        $grossProfit30 = $revenue30 - $cogs30;

        $userCount = User::query()->where('is_admin', false)->count();

        return view('admin.dashboard', compact(
            'ordersByStatus',
            'revenue7',
            'revenue30',
            'grossProfit30',
            'totalInventoryUnits',
            'outOfStockCount',
            'lowStock',
            'slowProducts',
            'topProducts',
            'dailyRevenue',
            'userCount'
        ));
    }
}
