@extends('layouts.shop')

@section('title', ($onlyNew ? 'Hàng mới — ' : 'Cửa hàng — ').config('app.name'))

@section('content')
    @if($selectedCategory)
        <div class="mb-6 flex flex-wrap items-center justify-between gap-3 rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-white px-4 py-3 shadow-[var(--shadow-card)]">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.16em] text-[color:var(--text-muted)]">Danh mục đang xem</p>
                <h1 class="mt-1 text-2xl font-bold text-[color:var(--primary)]">{{ $selectedCategory->name }}</h1>
                @if($selectedCategory->parent)
                    <p class="mt-1 text-sm text-[color:var(--text-muted)]">Thuộc {{ $selectedCategory->parent->name }}</p>
                @endif
            </div>
            <a href="{{ route('products.index') }}" class="text-sm font-semibold text-[color:var(--accent)] hover:underline">Xem tất cả sản phẩm</a>
        </div>
    @endif
    @if(! $onlyNew)
        <div class="nd-shop-page-head">
            <span class="soft-chip mb-3">Khám phá</span>
            <h1 class="nd-shop-page-title">Tất cả sản phẩm</h1>
            <p class="nd-shop-page-lead">Tìm theo tên hoặc SKU, lọc theo danh mục và giá — sắp xếp theo nhu cầu của bạn.</p>
        </div>
    @endif

    @if($categories->isNotEmpty())
        <section class="mb-8 border-y border-[color:var(--line-soft)] py-5" aria-label="Khám phá theo danh mục">
            <div class="catalog-category-scroller flex items-center gap-2 overflow-x-auto pb-1">
                <a href="{{ $onlyNew ? route('products.index', ['new' => 1]) : route('products.index') }}" class="catalog-category-tab border px-3 py-2 text-sm font-semibold transition {{ ! request('category') ? 'border-[color:var(--accent)] bg-[color:var(--accent)] text-white' : 'border-[color:var(--line-soft)] text-[color:var(--text-main)] hover:border-[color:var(--accent)]' }}"><i class="fas fa-th-large mr-1.5 text-xs" aria-hidden="true"></i>Tất cả</a>
                @foreach($categories as $category)
                    @php
                        $categoryName = \Illuminate\Support\Str::lower($category->name);
                        $categoryIcon = \Illuminate\Support\Str::contains($categoryName, ['giày', 'túi', 'phụ kiện'])
                            ? 'fa-shoe-prints'
                            : (\Illuminate\Support\Str::contains($categoryName, ['đẹp', 'mỹ phẩm']) ? 'fa-sparkles' : 'fa-shirt');
                    @endphp
                    <a href="{{ route('products.index', array_filter(['category' => $category->id, 'new' => $onlyNew ? 1 : null])) }}" class="catalog-category-tab border px-3 py-2 text-sm font-semibold transition {{ (int) request('category') === $category->id ? 'border-[color:var(--primary)] bg-[color:var(--primary)] text-white' : 'border-[color:var(--line-soft)] text-[color:var(--primary)] hover:border-[color:var(--primary)]' }}"><i class="fas {{ $categoryIcon }} mr-1.5 text-xs" aria-hidden="true"></i>{{ $category->name }} <span class="ml-1 text-xs opacity-60">{{ $category->active_products_count }}</span></a>
                    @foreach($category->children as $child)
                        <a href="{{ route('products.index', array_filter(['category' => $child->id, 'new' => $onlyNew ? 1 : null])) }}" class="px-2 py-2 text-xs font-medium text-[color:var(--text-muted)] transition hover:text-[color:var(--accent)] {{ (int) request('category') === $child->id ? 'text-[color:var(--accent)] underline' : '' }}">{{ $child->name }}</a>
                    @endforeach
                @endforeach
            </div>
        </section>
    @endif

    <div class="catalog-layout flex flex-col gap-5 md:flex-row md:gap-8">
        <button type="button" class="catalog-filter-toggle flex items-center justify-between rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-white px-4 py-3 text-sm font-semibold text-[color:var(--primary)] shadow-[var(--shadow-card)] md:hidden" data-filter-toggle aria-expanded="false">
            <span><i class="fas fa-sliders-h mr-2 text-[color:var(--accent)]" aria-hidden="true"></i>Bộ lọc sản phẩm</span>
            <span class="rounded-full bg-rose-50 px-2 py-0.5 text-xs text-[color:var(--accent)]">{{ $products->total() }}</span>
        </button>
        <aside class="catalog-filter-panel w-full shrink-0 md:w-64" data-filter-panel>
            <div class="shop-shell space-y-4 p-4 md:sticky md:top-28">
                <div class="flex items-center justify-between">
                    <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--text-muted)]">Bộ lọc</h2>
                    <button type="button" class="catalog-filter-close text-lg text-[color:var(--text-muted)] hover:text-[color:var(--accent)] md:hidden" data-filter-close aria-label="Đóng bộ lọc"><i class="fas fa-times"></i></button>
                </div>
                <form method="get" class="space-y-3 text-sm" data-catalog-filter-form>
                    @if($onlyNew)
                        <input type="hidden" name="new" value="1" />
                    @endif
                    <label class="block font-medium text-[color:var(--primary)]">Tìm theo tên hoặc SKU
                        <input
                            type="search"
                            name="q"
                            class="field-control mt-1"
                            value="{{ request('q') }}"
                            placeholder="Ví dụ: áo, váy, linen…"
                            autocomplete="off"
                            enterkeyhint="search"
                        />
                    </label>
                    <label class="block font-medium text-[color:var(--primary)]">Danh mục
                        <select name="category" class="field-control" onchange="this.form.submit()">
                            <option value="">Tất cả</option>
                            @foreach($categories as $c)
                                <option value="{{ $c->id }}" @selected((int) request('category') === $c->id)>{{ $c->name }}</option>
                                @foreach($c->children as $child)
                                    <option value="{{ $child->id }}" @selected((int) request('category') === $child->id)>— {{ $child->name }}</option>
                                @endforeach
                            @endforeach
                        </select>
                    </label>
                    <label class="block font-medium text-[color:var(--primary)]">Giá tối thiểu <input name="min" type="number" class="field-control" value="{{ request('min') }}" placeholder="0" /></label>
                    <label class="block font-medium text-[color:var(--primary)]">Giá tối đa <input name="max" type="number" class="field-control" value="{{ request('max') }}" placeholder="999000" /></label>
                    <label class="block font-medium text-[color:var(--primary)]">Sắp xếp
                        <select name="sort" class="field-control" onchange="this.form.submit()">
                            <option value="newest" @selected(request('sort')==='newest'||!request('sort'))>Mới nhất</option>
                            <option value="price_asc" @selected(request('sort')==='price_asc')>Giá tăng dần</option>
                            <option value="price_desc" @selected(request('sort')==='price_desc')>Giá giảm dần</option>
                            <option value="name" @selected(request('sort')==='name')>Tên A–Z</option>
                        </select>
                    </label>
                    <label class="block font-medium text-[color:var(--primary)]">Kích cỡ
                        <select name="size" class="field-control" onchange="this.form.submit()">
                            <option value="">Tất cả kích cỡ</option>
                            @foreach($sizes as $size)
                                <option value="{{ $size }}" @selected(request('size') === $size)>{{ $size }}</option>
                            @endforeach
                        </select>
                    </label>
                    <div class="space-y-2 pt-1">
                        <label class="flex cursor-pointer items-center gap-2 font-medium text-[color:var(--primary)]">
                            <input type="checkbox" name="in_stock" value="1" class="rounded border-[color:var(--line-soft)] text-[color:var(--accent)]" @checked(request()->boolean('in_stock'))>
                            Chỉ còn hàng
                        </label>
                        <label class="flex cursor-pointer items-center gap-2 font-medium text-[color:var(--primary)]">
                            <input type="checkbox" name="sale" value="1" class="rounded border-[color:var(--line-soft)] text-[color:var(--accent)]" @checked(request()->boolean('sale'))>
                            Đang giảm giá
                        </label>
                    </div>
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button class="rounded-[var(--radius-ui)] border border-[color:var(--accent)] bg-[color:var(--accent)] px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-[color:var(--accent-hover)]" type="submit">Áp dụng ({{ $products->total() }})</button>
                        <a href="{{ $onlyNew ? route('products.index', ['new' => 1]) : route('products.index') }}" class="rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] px-3 py-2.5 text-center text-sm font-semibold text-[color:var(--text-main)] transition hover:bg-[#f7f7f9]">Đặt lại</a>
                    </div>
                </form>
                @if($onlyNew)
                    <a href="{{ route('products.index') }}" class="block text-center text-xs font-semibold text-[color:var(--accent)] hover:underline">Xem tất cả sản phẩm</a>
                @endif
            </div>
        </aside>
        <div class="min-w-0 flex-1">
            <div class="mb-4 flex flex-wrap items-center justify-between gap-2 rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-[color:var(--surface)] px-4 py-3 shadow-[var(--shadow-card)]">
                <p class="text-sm text-[color:var(--text-muted)]">
                    Đang hiển thị <span class="font-semibold text-[color:var(--primary)]">{{ $products->total() }}</span> sản phẩm
                    @if($onlyNew)
                        <span class="ml-1 rounded-full bg-pink-100 px-2 py-0.5 text-[0.7rem] font-semibold text-pink-900">Hàng mới</span>
                    @endif
                    @if(filled(trim((string) request('q'))))
                        <span class="mt-1 block text-[0.8rem] sm:mt-0 sm:ml-2 sm:inline-block">— từ khóa: <span class="font-semibold text-[color:var(--primary)]">{{ request('q') }}</span></span>
                    @endif
                </p>
                <a href="{{ route('products.index', array_filter(['new' => $onlyNew ? 1 : null, 'sort' => 'newest', 'q' => request('q')])) }}" class="text-xs font-semibold text-[color:var(--accent)] hover:underline">Mới thêm gần đây</a>
            </div>
            @if($hasFuzzyResults)
                <p class="mb-4 rounded-[var(--radius-ui)] border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-900">
                    Có một số kết quả gần với từ khóa bạn nhập. Hãy kiểm tra tên sản phẩm trước khi chọn.
                </p>
            @endif
            <div class="nd-product-grid nd-catalog-grid">
                @forelse($products as $p)
                    <x-product-card :product="$p" />
                @empty
                    <p class="col-span-full rounded-[var(--radius-ui)] border border-dashed border-[color:var(--line-soft)] bg-[#f7f7f9] p-10 text-center text-[color:var(--text-muted)]">
                        @if($onlyNew)
                            Không có sản phẩm nào được đánh dấu hàng mới phù hợp bộ lọc. <a href="{{ route('products.index') }}" class="font-semibold text-[color:var(--accent)] hover:underline">Xem cửa hàng</a>
                        @else
                            Không tìm thấy sản phẩm.
                        @endif
                    </p>
                @endforelse
            </div>
            <div class="mt-8">{{ $products->links() }}</div>
        </div>
    </div>
    <script>
        (() => {
            const panel = document.querySelector('[data-filter-panel]');
            const toggle = document.querySelector('[data-filter-toggle]');
            const close = document.querySelector('[data-filter-close]');
            const setOpen = (open) => {
                panel?.classList.toggle('is-open', open);
                toggle?.setAttribute('aria-expanded', open ? 'true' : 'false');
                document.body.classList.toggle('overflow-hidden', open);
            };
            toggle?.addEventListener('click', () => setOpen(true));
            close?.addEventListener('click', () => setOpen(false));
            panel?.addEventListener('click', (event) => { if (event.target === panel) setOpen(false); });
            window.addEventListener('resize', () => { if (window.innerWidth >= 768) setOpen(false); });
        })();
    </script>
@endsection
