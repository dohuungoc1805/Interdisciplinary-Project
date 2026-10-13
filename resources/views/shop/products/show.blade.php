@extends('layouts.shop')

@section('title', $product->name.' — '.config('app.name'))

@section('content')
    @php
        $img = $product->mainImageUrl();
    @endphp
    <a href="{{ route('products.index') }}" class="text-sm font-medium text-[color:var(--accent)] hover:underline">&larr; Quay lại cửa hàng</a>
    <div class="mt-6 grid gap-10 md:grid-cols-2">
        <div class="shop-shell overflow-hidden p-2">
            <img id="product-main-image" src="{{ $img }}" data-default-src="{{ $img }}" alt="{{ $product->name }}" class="max-h-[560px] w-full rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] object-cover" />
            @if($product->images->count())
                <div class="mt-3 flex flex-wrap gap-2">
                    @foreach($product->images as $pi)
                        <img src="{{ $pi->publicUrl() }}" class="h-16 w-16 rounded-lg border border-[color:var(--line-soft)] object-cover" alt="" />
                    @endforeach
                </div>
            @endif
        </div>
        <div>
            <h1 class="shop-heading text-3xl font-bold text-[color:var(--primary)]">{{ $product->name }}</h1>
            <div class="mt-3 flex flex-wrap gap-2">
                @if($product->is_on_sale || $product->discountPercent())
                    @if($product->discountPercent())
                        <span class="rounded-md bg-red-600 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white">-{{ $product->discountPercent() }}%</span>
                    @else
                        <span class="rounded-md bg-red-600 px-3 py-1 text-xs font-bold uppercase tracking-wide text-white">SALE</span>
                    @endif
                @endif
                @if($product->is_new)
                    <span class="rounded-md bg-[color:var(--accent)] px-3 py-1 text-xs font-bold uppercase tracking-wide text-white">Mới</span>
                @endif
                @if($product->is_hot)
                    <span class="rounded-md bg-[color:var(--gold)] px-3 py-1 text-xs font-bold uppercase tracking-wide text-[color:var(--primary)]">Hot</span>
                @endif
            </div>
            <p class="mt-2 text-sm font-medium uppercase tracking-wide text-[color:var(--text-muted)]">{{ $product->category?->name }}</p>
            <p class="mt-5 text-xl font-bold text-[color:var(--accent)]">{{ number_format((float) $product->price, 0, ',', '.') }} ₫
                @if($product->compare_price)
                    <span class="ml-2 text-base font-normal text-[color:var(--text-muted)] line-through">{{ number_format((float) $product->compare_price, 0, ',', '.') }} ₫</span>
                @endif
            </p>
            <div class="prose prose-sm mt-5 max-w-none text-[color:var(--text-main)]">{!! nl2br(e($product->description)) !!}</div>

            <form id="product-add-form" class="shop-shell mt-8 space-y-4 p-5" method="post" action="{{ route('cart.add') }}">@csrf
                @php
                    $availableVariants = $product->variants->where('stock', '>', 0);
                    $variantSizes = $availableVariants->pluck('size')->filter()->unique()->values();
                    $variantColors = $availableVariants->pluck('color')->filter()->unique()->values();
                @endphp
                <input id="product-variant-id" type="hidden" name="product_variant_id" value="{{ $availableVariants->first()?->id }}" required />
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="product-size" class="text-sm font-semibold text-[color:var(--primary)]">Kích cỡ</label>
                        <select id="product-size" class="field-control mt-1" {{ $variantSizes->isEmpty() ? 'disabled' : '' }}>
                            @foreach($variantSizes as $size)<option value="{{ $size }}">{{ $size }}</option>@endforeach
                        </select>
                    </div>
                    <div>
                        <label for="product-color" class="text-sm font-semibold text-[color:var(--primary)]">Màu sắc</label>
                        <select id="product-color" class="field-control mt-1" {{ $variantColors->isEmpty() ? 'disabled' : '' }}>
                            @foreach($variantColors as $color)<option value="{{ $color }}">{{ $color }}</option>@endforeach
                        </select>
                    </div>
                </div>
                <p id="variant-stock" class="text-sm font-medium text-emerald-700" aria-live="polite"></p>
                @if($availableVariants->isEmpty())<p class="rounded-lg bg-rose-50 px-3 py-2 text-sm font-medium text-rose-700">Sản phẩm hiện đã hết hàng.</p>@endif
                <div>
                    <label class="text-sm font-semibold text-[color:var(--primary)]">Số lượng</label>
                    <input id="product-quantity" type="number" name="quantity" value="1" min="1" max="{{ $availableVariants->first()?->stock ?? 1 }}" class="field-control mt-1 w-28" {{ $availableVariants->isEmpty() ? 'disabled' : '' }} />
                </div>
                <div class="flex flex-wrap gap-3">
                    <button id="product-add-button" class="primary-cta disabled:cursor-not-allowed disabled:opacity-50" type="submit" {{ $availableVariants->isEmpty() ? 'disabled' : '' }}>Thêm vào giỏ</button>
                </div>
            </form>
            @php
                $variantPayload = $availableVariants->map(fn ($variant) => [
                    'id' => $variant->id,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'stock' => (int) $variant->stock,
                    'image' => $variant->imageUrl(),
                ])->values();
            @endphp
            <script>
                (() => {
                    const variants = {{ \Illuminate\Support\Js::from($variantPayload) }};
                    const size = document.getElementById('product-size');
                    const color = document.getElementById('product-color');
                    const variantId = document.getElementById('product-variant-id');
                    const quantity = document.getElementById('product-quantity');
                    const stockText = document.getElementById('variant-stock');
                    const button = document.getElementById('product-add-button');
                    const mainImage = document.getElementById('product-main-image');
                    if (!size || !color || !variantId || !quantity || !stockText || !button) return;
                    const syncVariant = (changedBy = null) => {
                        // Always keep both selects usable and synchronize the other field.
                        if (changedBy === 'size') {
                            const matchingVariant = variants.find(item => item.size === size.value);
                            if (matchingVariant) color.value = matchingVariant.color;
                        } else if (changedBy === 'color') {
                            const matchingVariant = variants.find(item => item.color === color.value);
                            if (matchingVariant) size.value = matchingVariant.size;
                        }
                        const variant = variants.find(item => item.size === size.value && item.color === color.value);
                        const stock = variant?.stock ?? 0;
                        variantId.value = variant?.id ?? '';
                        quantity.max = stock || 1;
                        if (Number(quantity.value) > stock) quantity.value = stock || 1;
                        stockText.textContent = stock > 0 ? `Còn ${stock} sản phẩm cho lựa chọn này.` : 'Size và màu này hiện đã hết hàng.';
                        stockText.className = stock > 0 ? 'text-sm font-medium text-emerald-700' : 'text-sm font-medium text-rose-600';
                        button.disabled = stock < 1;
                        quantity.disabled = stock < 1;
                        const colorImage = variants.find(item => item.color === color.value && item.image)?.image;
                        if (mainImage) mainImage.src = variant?.image || colorImage || mainImage.dataset.defaultSrc;
                    };
                    size.addEventListener('change', () => syncVariant('size'));
                    color.addEventListener('change', () => syncVariant('color'));
                    if (variants[0]) {
                        size.value = variants[0].size;
                        color.value = variants[0].color;
                    }
                    syncVariant();
                })();
            </script>
            @auth
                <form method="post" action="{{ route('wishlist.toggle', $product) }}" class="mt-3">@csrf
                    <button type="submit" class="secondary-cta">Thêm vào yêu thích</button>
                </form>
            @endauth

            @auth
                @if($userReview)
                    <form method="post" action="{{ route('reviews.update', $userReview) }}" class="shop-shell mt-10 space-y-3 border-t border-[color:var(--line-soft)] p-5 pt-8">
                        @csrf
                        @method('PATCH')
                        <h3 class="font-semibold text-[color:var(--primary)]">Đánh giá của bạn</h3>
                        @if(! $userReview->is_approved)
                            <p class="rounded-[var(--radius-ui)] border border-amber-200 bg-amber-50 px-3 py-2 text-xs text-amber-900">Đánh giá đang chờ duyệt. Sau khi sửa, bản cập nhật cũng cần được duyệt trước khi hiển thị công khai.</p>
                        @else
                            <p class="text-xs text-[color:var(--text-muted)]">Bạn có thể chỉnh sửa điểm hoặc nội dung. Sau khi lưu, đánh giá sẽ được kiểm duyệt lại trước khi hiển thị.</p>
                        @endif
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-sm font-medium text-[color:var(--primary)]">Điểm</span>
                            <select name="rating" class="field-control w-auto py-2" required>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" @selected((int) old('rating', $userReview->rating) === $i)>{{ $i }} sao</option>
                                @endfor
                            </select>
                        </div>
                        <textarea name="comment" class="field-control" rows="3" placeholder="Nội dung (tuỳ chọn)">{{ old('comment', $userReview->comment) }}</textarea>
                        <button class="rounded-[var(--radius-ui)] bg-[color:var(--primary)] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#16213e]" type="submit">Cập nhật đánh giá</button>
                    </form>
                @elseif($reviewableOrders->isNotEmpty())
                    <form method="post" action="{{ route('reviews.store', $product) }}" class="shop-shell mt-10 space-y-3 border-t border-[color:var(--line-soft)] p-5 pt-8">
                        @csrf
                        <h3 class="font-semibold text-[color:var(--primary)]">Viết đánh giá</h3>
                        <p class="text-xs text-[color:var(--text-muted)]">Chỉ các đơn hàng <strong>Hoàn thành</strong> có sản phẩm này mới được dùng để đánh giá. Chọn đơn tương ứng bên dưới.</p>
                        <div>
                            <label class="text-sm font-semibold text-[color:var(--primary)]">Đơn hàng</label>
                            <select name="order_id" class="field-control mt-1" required>
                                <option value="" disabled @selected(! old('order_id'))>— Chọn đơn hàng —</option>
                                @foreach($reviewableOrders as $ord)
                                    <option value="{{ $ord->id }}" @selected((string) old('order_id') === (string) $ord->id)>#{{ $ord->order_number }} — {{ $ord->created_at->format('d/m/Y') }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex flex-wrap items-center gap-3">
                            <span class="text-sm font-medium text-[color:var(--primary)]">Điểm</span>
                            <select name="rating" class="field-control w-auto py-2" required>
                                @for($i = 5; $i >= 1; $i--)
                                    <option value="{{ $i }}" @selected((int) old('rating', 5) === $i)>{{ $i }} sao</option>
                                @endfor
                            </select>
                        </div>
                        <textarea name="comment" class="field-control" rows="3" placeholder="Nội dung (tuỳ chọn)">{{ old('comment') }}</textarea>
                        <button class="rounded-[var(--radius-ui)] bg-[color:var(--primary)] px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-[#16213e]" type="submit">Gửi đánh giá</button>
                    </form>
                @else
                    <div class="shop-shell mt-10 space-y-2 border-t border-[color:var(--line-soft)] p-5 pt-8 text-sm text-[color:var(--text-muted)]">
                        <p class="font-medium text-[color:var(--primary)]">Chưa thể đánh giá</p>
                        <p>Bạn cần <strong>mua sản phẩm này</strong> và đơn hàng ở trạng thái <strong>Hoàn thành</strong> thì mới gửi đánh giá được.</p>
                        <a href="{{ route('purchases.index') }}" class="inline-block font-medium text-[color:var(--accent)] underline">Xem đơn mua của tôi</a>
                    </div>
                @endif
            @endauth

            <div class="mt-10">
                <h3 class="mb-3 font-semibold text-[color:var(--primary)]">Đánh giá ({{ $reviews->count() }})</h3>
                <div class="space-y-3">
                    @forelse($reviews as $r)
                        <div class="shop-shell p-4 text-sm">
                            <div class="flex justify-between">
                                <span class="font-semibold text-[color:var(--primary)]">{{ $r->user->name }}</span>
                                <span class="text-[color:var(--accent)]">{{ $r->rating }}/5</span>
                            </div>
                            <p class="mt-2 text-[color:var(--text-muted)]">{{ $r->comment }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-[color:var(--text-muted)]">Chưa có đánh giá công khai.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>

    @if($relatedProducts->isNotEmpty())
        <section class="mt-14 border-t border-[color:var(--line-soft)] pt-10" aria-labelledby="related-products-title">
            <div class="mb-6 flex flex-wrap items-end justify-between gap-3">
                <div>
                    <h2 id="related-products-title" class="shop-heading text-2xl font-bold text-[color:var(--primary)]">Khám phá thêm</h2>
                    <p class="mt-1 text-sm text-[color:var(--text-muted)]">Các sản phẩm khác trong cùng nhóm dành cho bạn.</p>
                </div>
                @if($product->category)
                    <a href="{{ route('products.index', ['category' => $product->category->id]) }}" class="text-sm font-semibold text-[color:var(--accent)] hover:underline">Xem tất cả</a>
                @endif
            </div>
            <div class="nd-product-grid">
                @foreach($relatedProducts as $relatedProduct)
                    <x-product-card :product="$relatedProduct" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
