<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Quản trị') — {{ config('app.name') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-gradient-to-br from-slate-100 via-slate-50 to-orange-50/30 antialiased text-slate-900">
    <div class="flex min-h-screen">
        <aside class="sticky top-0 flex h-screen max-h-screen w-64 shrink-0 flex-col overflow-y-auto border-r border-slate-800/90 bg-gradient-to-b from-slate-950 via-slate-950 to-slate-900 text-slate-200 shadow-xl">
            <div class="border-b border-slate-800/80 px-4 py-6">
                <a href="{{ route('admin.dashboard') }}" class="block text-base font-bold leading-snug tracking-tight text-white">
                    <span class="bg-gradient-to-r from-orange-400 to-amber-400 bg-clip-text text-transparent">{{ config('app.name') }}</span>
                    <span class="block text-sm font-semibold text-slate-300">Quản trị</span>
                </a>
                <p class="mt-2 text-xs leading-relaxed text-slate-400">Bảng điều khiển cửa hàng</p>
            </div>
            <nav class="flex-1 space-y-1 overflow-y-auto px-3 py-4">
                <x-admin.nav-link :href="route('admin.dashboard')" :active="request()->routeIs('admin.dashboard')">
                    <span class="text-base leading-none" aria-hidden="true">📊</span> Tổng quan
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.categories.index')" :active="request()->routeIs('admin.categories.*')">
                    <span class="text-base leading-none" aria-hidden="true">📁</span> Danh mục
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.products.index')" :active="request()->routeIs('admin.products.*')">
                    <span class="text-base leading-none" aria-hidden="true">🏷️</span> Sản phẩm
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.orders.index')" :active="request()->routeIs('admin.orders.index') || request()->routeIs('admin.orders.show') || request()->routeIs('admin.orders.update') || request()->routeIs('admin.orders.bulk')">
                    <span class="text-base leading-none" aria-hidden="true">📦</span> Đơn hàng
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.purchases.index')" :active="request()->routeIs('admin.purchases.*')">
                    <span class="text-base leading-none" aria-hidden="true">🧾</span> Nhập hàng
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.users.index')" :active="request()->routeIs('admin.users.*')">
                    <span class="text-base leading-none" aria-hidden="true">👤</span> Người dùng
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.coupons.index')" :active="request()->routeIs('admin.coupons.*')">
                    <span class="text-base leading-none" aria-hidden="true">🎟️</span> Mã giảm giá
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.promotions.index')" :active="request()->routeIs('admin.promotions.*')">
                    <span class="text-base leading-none" aria-hidden="true">🏷️</span> Khuyến mãi SP
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.banners.index')" :active="request()->routeIs('admin.banners.*')">
                    <span class="text-base leading-none" aria-hidden="true">🖼️</span> Banner
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.reviews-moderation.index')" :active="request()->routeIs('admin.reviews-moderation.*')">
                    <span class="text-base leading-none" aria-hidden="true">⭐</span> Đánh giá
                </x-admin.nav-link>
                <x-admin.nav-link :href="route('admin.orders.export')" :active="request()->routeIs('admin.orders.export')">
                    <span class="text-base leading-none" aria-hidden="true">⬇️</span> Xuất CSV
                </x-admin.nav-link>
            </nav>
            <div class="border-t border-slate-800/80 p-4">
                <a href="{{ route('home') }}" class="flex items-center justify-center gap-2 rounded-xl border border-slate-600/80 bg-slate-900/50 px-3 py-2.5 text-xs font-medium text-slate-200 transition hover:border-orange-500/40 hover:bg-slate-800 hover:text-white">
                    ← Về cửa hàng
                </a>
            </div>
        </aside>

        <div class="flex min-w-0 flex-1 flex-col">
            <header class="sticky top-0 z-10 flex items-center justify-end border-b border-slate-200/80 bg-white/90 px-6 py-4 shadow-sm backdrop-blur-md supports-[backdrop-filter]:bg-white/75">
                <div class="flex items-center gap-4 text-sm">
                    <span class="hidden max-w-[220px] truncate text-slate-500 sm:inline" title="{{ auth()->user()->email }}">{{ auth()->user()->email }}</span>
                    <form method="post" action="{{ route('logout') }}" class="inline">
                        @csrf
                        <button type="submit" class="rounded-xl bg-slate-900 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-slate-800">Đăng xuất</button>
                    </form>
                </div>
            </header>

            <main class="flex-1 p-6 lg:p-8">
                @if(session('status'))
                    <div class="mb-6 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-900 shadow-sm">{{ session('status') }}</div>
                @endif
                @if(session('error'))
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-medium text-red-900 shadow-sm">{{ session('error') }}</div>
                @endif
                @if ($errors->any())
                    <div class="mb-6 rounded-2xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-900 shadow-sm">
                        <p class="font-semibold">Vui lòng sửa các lỗi sau:</p>
                        <ul class="mt-2 list-inside list-disc space-y-0.5">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                    </div>
                @endif
                @yield('content')
            </main>
        </div>
    </div>
</body>
</html>
