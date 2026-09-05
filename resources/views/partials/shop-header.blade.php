<header
    class="sticky top-0 z-40 overflow-visible border-b border-[color:var(--line-soft)] bg-white/95 shadow-sm backdrop-blur-md transition-shadow"
    x-data="{ mobileNav: false }"
    @keydown.escape.window="mobileNav = false"
>
    <div class="bg-[color:var(--primary)] py-2 text-[0.78rem] text-white">
        <div class="nd-container flex flex-wrap items-center justify-center gap-x-6 gap-y-1">
            <span><i class="fas fa-phone-alt mr-1.5 text-[color:var(--gold)]"></i> Hotline: {{ config('app.support_phone', '0123 456 789') }}</span>
            <span class="hidden sm:inline"><i class="fas fa-shipping-fast mr-1.5 text-[color:var(--gold)]"></i> Miễn phí vận chuyển từ 500.000 ₫</span>
            <span class="hidden md:inline"><i class="fas fa-undo mr-1.5 text-[color:var(--gold)]"></i> Đổi trả dễ dàng trong 30 ngày</span>
        </div>
    </div>
    <div class="nd-container flex flex-wrap items-center justify-between gap-3 overflow-visible py-3 sm:flex-nowrap sm:py-3.5">
        <x-shop-brand-logo />

        <nav
            class="order-last flex w-full basis-full flex-col gap-3 pt-2 sm:order-none sm:flex sm:w-auto sm:basis-auto sm:flex-row sm:items-center sm:gap-6 sm:pt-0"
            :class="{ 'hidden sm:flex': !mobileNav, 'flex sm:flex': mobileNav }"
            id="shop-main-nav"
        >
            <a href="{{ route('home') }}" class="nd-nav-link @if(request()->routeIs('home')) nd-nav-link--active @endif">Trang chủ</a>
            <a href="{{ route('products.index', ['new' => 1]) }}" class="nd-nav-link @if(request()->routeIs('products.index') && request()->boolean('new')) nd-nav-link--active @endif">Hàng mới</a>
            <a href="{{ route('products.index') }}" class="nd-nav-link @if(request()->routeIs('products.index') && ! request()->boolean('new')) nd-nav-link--active @endif">Cửa hàng</a>
            @auth
                <a href="{{ route('purchases.index') }}" class="nd-nav-link @if(request()->routeIs('purchases.index') || request()->routeIs('orders.index') || request()->routeIs('orders.show')) nd-nav-link--active @endif">Đơn mua</a>
            @endauth
            <a href="{{ route('home') }}#nd-bestsellers" class="nd-nav-link">Bán chạy</a>
            <a href="{{ route('products.index') }}" class="nd-nav-link"><span class="rounded-full bg-[color:var(--accent)] px-2.5 py-0.5 text-[0.75rem] font-semibold text-white">GIẢM GIÁ</span></a>
            <a href="{{ route('home') }}#nd-newsletter" class="nd-nav-link">Ưu đãi</a>
        </nav>

        <div class="flex items-center gap-1 sm:gap-2">
            <details id="shop-search-details" class="group relative z-[50]">
                <summary
                    class="nd-icon-btn flex cursor-pointer list-none items-center justify-center [&::-webkit-details-marker]:hidden"
                    title="Tìm kiếm"
                    aria-label="Mở tìm kiếm"
                >
                    <i class="fas fa-search"></i>
                </summary>
                <div
                    class="absolute right-0 top-full z-[60] mt-2 w-[min(calc(100vw-2rem),28rem)] rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-white p-3 shadow-[var(--shadow-card-lg)]"
                    role="dialog"
                    aria-label="Tìm kiếm sản phẩm"
                >
                    <form method="get" action="{{ route('products.index') }}" class="flex items-center gap-2">
                        <input
                            type="search"
                            name="q"
                            value="{{ request('q') }}"
                            placeholder="Tìm kiếm sản phẩm…"
                            class="min-w-0 flex-1 rounded-[var(--radius-ui)] border-2 border-[color:var(--line-soft)] px-3 py-2.5 text-sm transition focus:border-[color:var(--accent)] focus:outline-none"
                            autocomplete="off"
                            enterkeyhint="search"
                        />
                        <button type="submit" class="nd-icon-btn shrink-0 text-[color:var(--accent)]" title="Tìm" aria-label="Tìm kiếm"><i class="fas fa-search"></i></button>
                        <button
                            type="button"
                            class="text-lg text-[color:var(--text-muted)] transition hover:text-[color:var(--accent)]"
                            aria-label="Đóng"
                            onclick="document.getElementById('shop-search-details')?.removeAttribute('open')"
                        >
                            <i class="fas fa-times"></i>
                        </button>
                    </form>
                    <p class="mt-2 text-[0.7rem] text-[color:var(--text-muted)]">Hoặc <a href="{{ route('products.index') }}" class="font-medium text-[color:var(--accent)] underline">vào cửa hàng</a> để lọc chi tiết.</p>
                </div>
            </details>
            @auth
                <details id="shop-account-details" class="relative z-[50]">
                    <summary
                        class="nd-icon-btn flex cursor-pointer list-none items-center justify-center [&::-webkit-details-marker]:hidden"
                        title="Tài khoản"
                        aria-label="Menu tài khoản"
                        aria-haspopup="true"
                    >
                        <i class="fas fa-user"></i>
                    </summary>
                    <div
                        class="absolute right-0 top-full z-[60] mt-2 min-w-[220px] rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-white py-2 shadow-[var(--shadow-card-lg)]"
                        role="menu"
                        aria-label="Tài khoản"
                    >
                        <p class="border-b border-[color:var(--line-soft)] px-4 py-2 text-xs text-[color:var(--text-muted)]">{{ auth()->user()->name }}</p>
                        <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm hover:bg-[#f7f7f9] hover:text-[color:var(--accent)]">Tài khoản</a>
                        <a href="{{ route('purchases.index') }}" class="block px-4 py-2 text-sm hover:bg-[#f7f7f9] hover:text-[color:var(--accent)]">Đơn mua</a>
                        <a href="{{ route('wishlist.index') }}" class="block px-4 py-2 text-sm hover:bg-[#f7f7f9] hover:text-[color:var(--accent)]">Yêu thích</a>
                        @if(auth()->user()->is_admin)
                            <a href="{{ route('admin.dashboard') }}" class="block px-4 py-2 text-sm hover:bg-[#f7f7f9] hover:text-[color:var(--accent)]">Quản trị</a>
                        @endif
                        <form method="post" action="{{ route('logout') }}" class="border-t border-[color:var(--line-soft)] px-2 pt-2">@csrf
                            <button type="submit" class="w-full rounded-lg px-2 py-2 text-left text-sm hover:bg-[#f7f7f9] hover:text-[color:var(--accent)]">Đăng xuất</button>
                        </form>
                    </div>
                </details>
            @else
                <a href="{{ route('login') }}" class="nd-icon-btn" title="Đăng nhập"><i class="fas fa-user"></i></a>
            @endauth
            <a href="{{ route('cart.index') }}" class="nd-icon-btn" title="Giỏ hàng">
                <i class="fas fa-shopping-bag"></i>
                @if(($cartItemCount ?? 0) > 0)
                    <span class="nd-cart-badge">{{ $cartItemCount > 99 ? '99+' : $cartItemCount }}</span>
                @endif
            </a>
            <button
                type="button"
                class="nd-icon-btn flex sm:hidden"
                @click="mobileNav = !mobileNav"
                :aria-expanded="mobileNav"
                aria-controls="shop-main-nav"
                aria-label="Mở menu"
            >
                <i class="fas" :class="mobileNav ? 'fa-times' : 'fa-bars'"></i>
            </button>
        </div>
    </div>
</header>
