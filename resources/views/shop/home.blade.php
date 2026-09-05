@extends('layouts.shop')

@section('title', 'Trang chủ — '.config('app.name'))

@section('content')
    @php
        $heroBgPath = '/images/shop/hero.png';

        $heroSlides = $banners->isEmpty()
            ? [[
                'title' => 'Phong cách định nghĩa bạn',
                'sub' => 'BST mới',
                'link' => route('products.index'),
                'img' => $heroBgPath,
            ]]
            : $banners->map(function ($b) use ($heroBgPath) {
                $product = $b->product_id ? $b->product : null;

                return [
                    'title' => $b->title,
                    'sub' => $product ? $product->name : 'Khám phá',
                    'link' => $product
                        ? route('products.show', $product->slug)
                        : ($b->link_url ?: route('products.index')),
                    'img' => $heroBgPath,
                ];
            })->values()->all();

        $categoryImageFor = static function ($category) {
            if ($category->image_path) {
                return $category->imagePublicUrl();
            }
            $slug = \Illuminate\Support\Str::lower($category->slug);
            $name = \Illuminate\Support\Str::lower($category->name);
            if (\Illuminate\Support\Str::contains($slug, 'women') || \Illuminate\Support\Str::contains($slug, 'nu') || \Illuminate\Support\Str::contains($name, 'nữ')) {
                return asset('images/shop/cat_women.png');
            }
            if (\Illuminate\Support\Str::contains($slug, 'men') || (\Illuminate\Support\Str::contains($slug, 'nam') && ! \Illuminate\Support\Str::contains($slug, 'women')) || \Illuminate\Support\Str::contains($name, 'nam')) {
                return asset('images/shop/cat_men.png');
            }

            return null;
        };

        // Column "Bán chạy": always try to show 4 cards (rated first, then featured, then new)
        $homeCol1 = $topRated->take(4)->values();
        $usedCol1 = $homeCol1->pluck('id');
        foreach ($featured as $p) {
            if ($homeCol1->count() >= 4) {
                break;
            }
            if (! $usedCol1->contains($p->id)) {
                $homeCol1->push($p);
                $usedCol1->push($p->id);
            }
        }
        foreach ($newArrivals as $p) {
            if ($homeCol1->count() >= 4) {
                break;
            }
            if (! $usedCol1->contains($p->id)) {
                $homeCol1->push($p);
                $usedCol1->push($p->id);
            }
        }

        // Column 2: different products, fill to 4
        $homeCol2 = collect();
        $idsCol2 = collect($usedCol1->all());
        foreach ($featured as $p) {
            if ($homeCol2->count() >= 4) {
                break;
            }
            if (! $idsCol2->contains($p->id)) {
                $homeCol2->push($p);
                $idsCol2->push($p->id);
            }
        }
        foreach ($newArrivals as $p) {
            if ($homeCol2->count() >= 4) {
                break;
            }
            if (! $idsCol2->contains($p->id)) {
                $homeCol2->push($p);
                $idsCol2->push($p->id);
            }
        }
        foreach ($moreToLove as $p) {
            if ($homeCol2->count() >= 4) {
                break;
            }
            if (! $idsCol2->contains($p->id)) {
                $homeCol2->push($p);
                $idsCol2->push($p->id);
            }
        }
    @endphp

    {{-- Hero (full width) --}}
    <section class="nd-home-bleed mb-0" aria-label="Banner chính">
        <div
            class="relative isolate w-full min-h-[max(480px,82svh)] max-h-[min(960px,92svh)] overflow-hidden bg-gradient-to-br from-[#1a1a2e] via-[#24304d] to-[#16213e]"
            x-data="{
                slides: {{ \Illuminate\Support\Js::from($heroSlides) }},
                i: 0,
                get n() { return this.slides.length },
                next() { this.i = (this.i + 1) % this.n },
                go(to) { this.i = to },
                timer: null,
                start() {
                    if (this.timer) clearInterval(this.timer);
                    if (this.n > 1) this.timer = setInterval(() => this.next(), 5600);
                }
            }"
            x-init="start()"
            @mouseenter="clearInterval(timer)"
            @mouseleave="start()"
        >
            @foreach($heroSlides as $idx => $slide)
                <div
                    class="absolute inset-0 transition-opacity duration-500 ease-out {{ $idx === 0 ? 'z-[1] opacity-100' : 'z-0 opacity-0 pointer-events-none' }}"
                    x-bind:class="{
                        'z-[1] opacity-100': i === {{ $idx }},
                        'z-0 opacity-0 pointer-events-none': i !== {{ $idx }},
                    }"
                >
                    <a href="{{ $slide['link'] }}" class="absolute inset-0 block">
                        <div
                            class="nd-hero-bg absolute inset-0 bg-cover bg-top bg-no-repeat"
                            style="background-image: url({{ json_encode($slide['img']) }});"
                            aria-hidden="true"
                        ></div>
                        <img
                            src="{{ $slide['img'] }}"
                            alt="{{ e($slide['title']) }}"
                            class="nd-hero-img absolute inset-0 z-[1] h-full w-full object-cover object-top"
                            width="1600"
                            height="900"
                            loading="{{ $idx === 0 ? 'eager' : 'lazy' }}"
                            decoding="async"
                            fetchpriority="{{ $idx === 0 ? 'high' : 'low' }}"
                            onerror="this.style.display='none'"
                        />
                        <div class="nd-hero-overlay pointer-events-none absolute inset-0 z-[2]"></div>
                    </a>
                </div>
            @endforeach
            <div class="pointer-events-none absolute inset-0 z-[2] flex flex-col justify-center nd-container py-16 text-white">
                <p class="mb-3 text-xs font-semibold uppercase tracking-[0.35em] text-[color:var(--gold)] sm:text-sm" x-text="slides[i].sub"></p>
                <h1 class="shop-heading mb-4 max-w-xl text-4xl font-bold leading-[1.12] text-white sm:text-5xl lg:max-w-2xl lg:text-6xl" x-text="slides[i].title"></h1>
                <p class="mb-8 max-w-md text-sm text-white/85 sm:text-base">Khám phá bộ sưu tập với thiết kế tinh tế — chất liệu được chọn lọc cho phong cách hằng ngày.</p>
                <div class="pointer-events-auto flex flex-wrap gap-3">
                    <a :href="slides[i].link" class="primary-cta">Khám phá ngay</a>
                    <a href="{{ route('products.index') }}" class="secondary-cta--on-dark">Xem cửa hàng</a>
                </div>
            </div>

            @if(count($heroSlides) > 1)
                <div class="absolute bottom-5 left-0 right-0 z-[3] flex justify-center gap-2">
                    @foreach($heroSlides as $idx => $_)
                        <button
                            type="button"
                            @click="go({{ $idx }})"
                            class="h-2 rounded-full transition-all"
                            :class="i === {{ $idx }} ? 'w-8 bg-[color:var(--accent)]' : 'w-2 bg-white/50'"
                            aria-label="Slide {{ $idx + 1 }}"
                        ></button>
                    @endforeach
                </div>
            @endif
        </div>
    </section>

    {{-- Feature strip --}}
    <section class="nd-home-bleed nd-features-strip mb-12 sm:mb-16" aria-label="Cam kết dịch vụ">
        <div class="nd-features-grid">
            <div class="nd-feature-item">
                <i class="fas fa-truck" aria-hidden="true"></i>
                <div>
                    <strong>Giao hàng nhanh</strong>
                    <small>Toàn quốc 1–3 ngày làm việc</small>
                </div>
            </div>
            <div class="nd-feature-item">
                <i class="fas fa-shield-alt" aria-hidden="true"></i>
                <div>
                    <strong>Cam kết chất lượng</strong>
                    <small>Kiểm tra kỹ trước khi gửi hàng</small>
                </div>
            </div>
            <div class="nd-feature-item">
                <i class="fas fa-undo-alt" aria-hidden="true"></i>
                <div>
                    <strong>Đổi trả thuận tiện</strong>
                    <small>Hỗ trợ theo chính sách cửa hàng</small>
                </div>
            </div>
            <div class="nd-feature-item">
                <i class="fas fa-headset" aria-hidden="true"></i>
                <div>
                    <strong>Hỗ trợ tư vấn</strong>
                    <small>Đội ngũ sẵn sàng hỗ trợ bạn</small>
                </div>
            </div>
        </div>
    </section>

    {{-- All categories --}}
    <section class="nd-section mb-12 sm:mb-16" id="categories">
        <div class="nd-section-header mb-10">
            <p class="nd-section-sub">Danh mục</p>
            <h2 class="nd-section-title">Mua theo danh mục</h2>
        </div>
        @if($parentCategories->isEmpty())
            <p class="text-center text-sm text-[color:var(--text-muted)]">Chưa có danh mục — vui lòng thêm trong quản trị.</p>
        @else
            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 lg:gap-5">
                @foreach($parentCategories as $c)
                    @php $catImg = $categoryImageFor($c); @endphp
                    <a href="{{ route('products.index', ['category' => $c->id]) }}" class="group relative flex aspect-[3/4] flex-col overflow-hidden rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-[color:var(--primary)] shadow-[var(--shadow-card)] transition hover:-translate-y-1 hover:shadow-[var(--shadow-card-lg)]">
                        @if($catImg)
                            <img src="{{ $catImg }}" alt="{{ $c->name }}" class="absolute inset-0 h-full w-full object-cover transition duration-500 group-hover:scale-105" loading="lazy" />
                            <div class="absolute inset-0 bg-gradient-to-t from-[rgba(26,26,46,0.85)] via-[rgba(26,26,46,0.25)] to-transparent"></div>
                        @else
                            <div class="absolute inset-0 bg-gradient-to-br from-[color:var(--primary)] to-[#16213e] opacity-90"></div>
                        @endif
                        <div class="relative z-[1] mt-auto p-4 text-white">
                            <h3 class="shop-heading text-lg font-bold leading-snug text-white">{{ $c->name }}</h3>
                            <span class="mt-2 inline-flex items-center gap-1 text-xs font-semibold uppercase tracking-wide text-[color:var(--gold)]">Xem sản phẩm <i class="fas fa-arrow-right text-[10px]"></i></span>
                        </div>
                    </a>
                @endforeach
            </div>
        @endif
    </section>

    {{-- New arrivals --}}
    <section class="nd-section-gray mb-12 sm:mb-16" id="new-arrivals">
        <div class="nd-section-gray-inner">
            <div class="nd-section-header">
                <p class="nd-section-sub">Được shop đánh dấu</p>
                <h2 class="nd-section-title">Hàng mới về</h2>
                <p class="mx-auto mt-2 max-w-2xl text-center text-sm text-[color:var(--text-muted)]">Chỉ các sản phẩm được bật cờ <strong>Hàng mới</strong> trong quản trị.</p>
            </div>
            @if($newArrivals->isEmpty())
                <p class="text-center text-sm text-[color:var(--text-muted)]">Hiện chưa có sản phẩm được đánh dấu hàng mới.</p>
            @else
            <div class="nd-product-grid">
                @foreach($newArrivals->take(8) as $p)
                    <x-product-card :product="$p" />
                @endforeach
            </div>
            @endif
            <div class="mt-10 text-center">
                <a href="{{ route('products.index', ['new' => 1]) }}" class="primary-cta">Xem toàn bộ hàng mới</a>
            </div>
        </div>
    </section>

    {{-- Promo + flash deals --}}
    @if($flashDeals->isNotEmpty())
        <section class="nd-home-bleed nd-promo-banner mb-12 sm:mb-16" id="sale">
            <div class="nd-container flex flex-col items-center justify-between gap-10 lg:flex-row lg:items-center">
                <div class="max-w-xl text-center lg:text-left">
                    <p class="mb-2 text-sm font-semibold uppercase tracking-[0.25em] text-[color:var(--gold)]">Ưu đãi có hạn</p>
                    <h2 class="shop-heading text-3xl font-bold text-white sm:text-4xl">Giảm giá <span class="text-[color:var(--accent)]">nổi bật</span> trong tuần</h2>
                    <p class="mt-3 text-sm text-white/80 sm:text-base">Sản phẩm đang có giá so sánh tốt — nhanh tay chọn size.</p>
                    <a href="{{ route('products.index') }}" class="primary-cta mt-6 inline-flex">Mua ngay</a>
                </div>
                <div class="grid w-full max-w-lg grid-cols-2 gap-3 sm:grid-cols-3">
                    @foreach($flashDeals->take(6) as $p)
                        <x-product-card :product="$p" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- Featured --}}
    <section class="nd-section mb-12 sm:mb-16">
        <div class="nd-section-header mb-10">
            <p class="nd-section-sub">Tuyển chọn</p>
            <h2 class="nd-section-title">Nổi bật tuần này</h2>
        </div>
        <div class="nd-product-grid nd-product-grid--dense">
            @foreach($featured as $p)
                <x-product-card :product="$p" />
            @endforeach
        </div>
    </section>

    {{-- Two columns: balanced 2×2 grids --}}
    <div class="mb-12 grid gap-10 sm:mb-16 lg:grid-cols-2 lg:gap-12" id="nd-bestsellers">
        <section>
            <div class="mb-8 text-left">
                <p class="nd-section-sub">Được yêu thích</p>
                <h2 class="nd-section-title">Bán chạy &amp; đánh giá</h2>
            </div>
            <div class="nd-product-grid nd-product-grid--split">
                @foreach($homeCol1->take(4) as $p)
                    <x-product-card :product="$p" />
                @endforeach
            </div>
            @if($homeCol1->isEmpty())
                <p class="text-sm text-[color:var(--text-muted)]">Chưa có sản phẩm để hiển thị.</p>
            @endif
        </section>
        <section>
            <div class="mb-8 text-left">
                <p class="nd-section-sub">Gợi ý thêm</p>
                <h2 class="nd-section-title">Dành cho bạn</h2>
            </div>
            <div class="nd-product-grid nd-product-grid--split">
                @foreach($homeCol2->take(4) as $p)
                    <x-product-card :product="$p" />
                @endforeach
            </div>
            @if($homeCol2->isEmpty())
                <p class="text-sm text-[color:var(--text-muted)]">Đang cập nhật sản phẩm gợi ý.</p>
            @endif
        </section>
    </div>

    {{-- More to love --}}
    <section class="nd-section mb-6 sm:mb-10">
        <div class="nd-section-header mb-10">
            <p class="nd-section-sub">Khám phá</p>
            <h2 class="nd-section-title">Có thể bạn thích</h2>
        </div>
        <div class="nd-product-grid">
            @foreach($moreToLove as $p)
                <x-product-card :product="$p" />
            @endforeach
        </div>
    </section>

    {{-- Contact strip --}}
    <section class="shop-shell mb-4 p-6 sm:p-8" id="nd-contact">
        <div class="nd-section-header mb-8">
            <p class="nd-section-sub">Liên hệ</p>
            <h2 class="nd-section-title">Kết nối với chúng tôi</h2>
        </div>
        <div class="grid gap-6 text-sm sm:grid-cols-2 lg:grid-cols-4">
            <div class="flex gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#f7f7f9] text-[color:var(--accent)]"><i class="fas fa-map-marker-alt"></i></span>
                <div>
                    <strong class="text-[color:var(--primary)]">Địa chỉ</strong>
                    <p class="mt-1 text-[color:var(--text-muted)]">Giao hàng toàn quốc — xem chi tiết khi thanh toán.</p>
                </div>
            </div>
            <div class="flex gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#f7f7f9] text-[color:var(--accent)]"><i class="fas fa-phone-alt"></i></span>
                <div>
                    <strong class="text-[color:var(--primary)]">Hotline</strong>
                    <p class="mt-1 text-[color:var(--text-muted)]">{{ config('app.support_phone', '0123 456 789') }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#f7f7f9] text-[color:var(--accent)]"><i class="fas fa-envelope"></i></span>
                <div>
                    <strong class="text-[color:var(--primary)]">Email</strong>
                    <p class="mt-1 text-[color:var(--text-muted)]">{{ config('mail.from.address') ?: 'support@shop' }}</p>
                </div>
            </div>
            <div class="flex gap-3">
                <span class="flex h-11 w-11 shrink-0 items-center justify-center rounded-full bg-[#f7f7f9] text-[color:var(--accent)]"><i class="fas fa-clock"></i></span>
                <div>
                    <strong class="text-[color:var(--primary)]">Giờ hỗ trợ</strong>
                    <p class="mt-1 text-[color:var(--text-muted)]">8:00 – 22:00 hằng ngày</p>
                </div>
            </div>
        </div>
    </section>
@endsection
