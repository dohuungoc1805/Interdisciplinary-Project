@props(['product'])
@php
    $availableStock = $product->relationLoaded('variants') ? (int) $product->variants->sum('stock') : null;
@endphp
<div {{ $attributes->merge(['class' => 'group nd-product-card']) }}>
    <div class="relative aspect-[3/4] overflow-hidden bg-[#f7f7f9]">
        <a href="{{ route('products.show', $product->slug) }}" class="absolute inset-0 z-0 block">
            <img src="{{ $product->mainImageUrl() }}" alt="{{ $product->name }}" class="h-full w-full object-cover transition duration-500 group-hover:scale-[1.06]" loading="lazy" />
        </a>
        @php
            $showSale = $product->is_on_sale || $product->discountPercent();
            $showPct = $product->discountPercent();
        @endphp
        <div class="pointer-events-none absolute left-3 top-3 z-[2] flex max-w-[70%] flex-col gap-1.5">
            @if($showSale)
                @if($showPct)
                    <span class="nd-product-badge nd-product-badge--sale w-fit">-{{ $showPct }}%</span>
                @else
                    <span class="nd-product-badge nd-product-badge--sale w-fit">SALE</span>
                @endif
            @elseif($product->is_hot)
                <span class="nd-product-badge nd-product-badge--hot w-fit">Hot</span>
            @elseif($product->is_new)
                <span class="nd-product-badge nd-product-badge--new w-fit">Mới</span>
            @endif
        </div>
        <div class="nd-product-actions">
            <a href="{{ route('products.show', $product->slug) }}" class="nd-pa-btn" title="Xem chi tiết"><i class="fas fa-eye"></i></a>
            @auth
                <form method="post" action="{{ route('wishlist.toggle', $product) }}" class="inline">
                    @csrf
                    <button type="submit" class="nd-pa-btn" title="Yêu thích"><i class="fas fa-heart"></i></button>
                </form>
            @endauth
            <a href="{{ route('products.show', $product->slug) }}" class="nd-pa-btn" title="Mua hàng"><i class="fas fa-shopping-bag"></i></a>
        </div>
        @if($availableStock !== null)
            <span class="absolute bottom-3 left-3 z-[2] rounded-full px-2.5 py-1 text-[0.68rem] font-semibold {{ $availableStock > 0 ? 'bg-white/90 text-emerald-700' : 'bg-red-600 text-white' }}">
                {{ $availableStock > 0 ? 'Còn '.$availableStock.' sản phẩm' : 'Hết hàng' }}
            </span>
        @endif
    </div>
    <a href="{{ route('products.show', $product->slug) }}" class="flex flex-1 flex-col p-4">
        @if($product->category)
            <p class="text-[0.75rem] font-medium uppercase tracking-wide text-[color:var(--text-muted)]">{{ $product->category->name }}</p>
        @endif
        <p class="mt-1 line-clamp-2 min-h-[2.5rem] text-[0.95rem] font-semibold leading-snug text-[color:var(--primary)]">{{ $product->name }}</p>
        <div class="mt-auto flex flex-wrap items-baseline gap-2 pt-3">
            <span class="text-base font-bold text-[color:var(--accent)]">{{ number_format((float) $product->price, 0, ',', '.') }} ₫</span>
            @if($product->compare_price && (float) $product->compare_price > (float) $product->price)
                <span class="text-sm text-[color:var(--text-muted)] line-through">{{ number_format((float) $product->compare_price, 0, ',', '.') }} ₫</span>
            @endif
        </div>
        @if($product->review_count > 0)
            <p class="mt-2 text-xs text-[color:var(--text-muted)]">★ {{ number_format((float) $product->average_rating, 1) }} ({{ $product->review_count }})</p>
        @endif
        <span class="mt-3 inline-flex items-center gap-1 text-xs font-semibold text-[color:var(--accent)] transition group-hover:gap-2">Xem chi tiết <i class="fas fa-arrow-right text-[0.65rem]" aria-hidden="true"></i></span>
    </a>
</div>
