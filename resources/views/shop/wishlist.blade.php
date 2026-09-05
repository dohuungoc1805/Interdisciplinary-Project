@extends('layouts.shop')

@section('title', 'Yêu thích — '.config('app.name'))

@section('content')
    <div class="nd-shop-page-head">
        <h1 class="nd-shop-page-title">Danh sách yêu thích</h1>
        <p class="nd-shop-page-lead">Sản phẩm đã lưu — giá cập nhật khi bạn mở chi tiết sản phẩm.</p>
    </div>

    <div class="nd-product-grid">
        @forelse($items as $w)
            @php $p = $w->product; @endphp
            <div class="nd-product-card flex flex-col overflow-hidden">
                <a href="{{ route('products.show', $p->slug) }}" class="relative block aspect-[3/4] overflow-hidden bg-[#f7f7f9]">
                    <img src="{{ $p->mainImageUrl() }}" alt="" class="h-full w-full object-cover transition duration-500 hover:scale-[1.03]" loading="lazy" />
                </a>
                <div class="flex flex-1 flex-col p-4">
                    <a href="{{ route('products.show', $p->slug) }}" class="line-clamp-2 text-sm font-semibold text-[color:var(--primary)] hover:text-[color:var(--accent)]">{{ $p->name }}</a>
                    <div class="mt-auto flex items-center justify-between gap-2 pt-3">
                        <span class="text-sm font-bold text-[color:var(--accent)]">{{ number_format((float) $p->price, 0, ',', '.') }} ₫</span>
                        <form method="post" action="{{ route('wishlist.toggle', $p) }}">@csrf
                            <button class="text-xs font-semibold text-red-600 hover:underline" type="submit">Bỏ lưu</button>
                        </form>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full rounded-[var(--radius-ui)] border border-dashed border-[color:var(--line-soft)] bg-[#f7f7f9] py-14 text-center text-[color:var(--text-muted)]">
                Bạn chưa lưu sản phẩm nào. <a href="{{ route('products.index') }}" class="font-semibold text-[color:var(--accent)] hover:underline">Xem cửa hàng</a>
            </div>
        @endforelse
    </div>
@endsection
