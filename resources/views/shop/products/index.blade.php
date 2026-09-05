@extends('layouts.shop')

@section('title', ($onlyNew ? 'Hàng mới — ' : 'Cửa hàng — ').config('app.name'))

@section('content')
    @if(! $onlyNew)
        <div class="nd-shop-page-head">
            <span class="soft-chip mb-3">Khám phá</span>
            <h1 class="nd-shop-page-title">Tất cả sản phẩm</h1>
            <p class="nd-shop-page-lead">Tìm theo tên hoặc SKU, lọc theo danh mục và giá — sắp xếp theo nhu cầu của bạn.</p>
        </div>
    @endif

    <div class="flex flex-col gap-8 md:flex-row md:gap-10">
        <aside class="w-full shrink-0 md:w-64">
            <div class="shop-shell space-y-4 p-4 md:sticky md:top-28">
                <h2 class="text-sm font-semibold uppercase tracking-[0.18em] text-[color:var(--text-muted)]">Bộ lọc</h2>
                <form method="get" class="space-y-3 text-sm">
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
                                <option value="{{ $c->id }}" @selected(request('category')==$c->id)>{{ $c->name }}</option>
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
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button class="rounded-[var(--radius-ui)] border border-[color:var(--accent)] bg-[color:var(--accent)] px-3 py-2.5 text-sm font-semibold text-white transition hover:bg-[color:var(--accent-hover)]" type="submit">Áp dụng</button>
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
            <div class="nd-product-grid">
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
@endsection
