@extends('layouts.shop')

@section('title', $product->name.' — '.config('app.name'))

@section('content')
    @php
        $img = $product->mainImageUrl();
        $availableVariants = $product->variants->where('stock', '>', 0);
        $colorVariantImages = $availableVariants
            ->filter(fn ($variant) => $variant->imageUrl())
            ->unique('color')
            ->values();
    @endphp
    <a href="{{ route('products.index') }}" class="text-sm font-medium text-[color:var(--accent)] hover:underline">&larr; Quay lại cửa hàng</a>
    <div class="mt-6 grid gap-10 md:grid-cols-2">
        <div class="shop-shell overflow-hidden p-2">
            <img id="product-main-image" src="{{ $img }}" data-default-src="{{ $img }}" alt="{{ $product->name }}" class="max-h-[560px] w-full rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] object-cover" />
            @if($colorVariantImages->isNotEmpty())
                <div class="mt-3 flex flex-wrap gap-2" aria-label="Ảnh theo màu sản phẩm">
                    @foreach($colorVariantImages as $colorVariant)
                        <button type="button" class="product-gallery-thumb overflow-hidden rounded-lg border-2 border-[color:var(--line-soft)] transition hover:border-[color:var(--accent)]" data-variant-color="{{ $colorVariant->color }}" data-gallery-image="{{ $colorVariant->imageUrl() }}" aria-label="Chọn màu {{ $colorVariant->color }}">
                            <img src="{{ $colorVariant->imageUrl() }}" class="h-16 w-16 object-cover" alt="Màu {{ $colorVariant->color }}" />
                        </button>
                    @endforeach
                </div>
            @elseif($product->images->count())
                <div class="mt-3 flex flex-wrap gap-2" aria-label="Thư viện ảnh sản phẩm">
                    @foreach($product->images as $pi)
                        <button type="button" class="product-gallery-thumb overflow-hidden rounded-lg border-2 border-[color:var(--line-soft)] transition hover:border-[color:var(--accent)]" data-gallery-image="{{ $pi->publicUrl() }}" aria-label="Xem ảnh sản phẩm">
                            <img src="{{ $pi->publicUrl() }}" class="h-16 w-16 object-cover" alt="" />
                        </button>
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
                    <label for="product-quantity" class="text-sm font-semibold text-[color:var(--primary)]">Số lượng</label>
                    <div class="mt-1 flex w-fit items-center overflow-hidden rounded-[var(--radius-ui)] border border-[color:var(--line-soft)]">
                        <button type="button" class="px-3 py-2 text-lg text-[color:var(--primary)] transition hover:bg-slate-50 disabled:opacity-40" data-quantity-step="down" aria-label="Giảm số lượng">−</button>
                        <input id="product-quantity" type="number" name="quantity" value="1" min="1" max="{{ $availableVariants->first()?->stock ?? 1 }}" class="w-14 border-0 px-1 py-2 text-center outline-none focus:ring-0" {{ $availableVariants->isEmpty() ? 'disabled' : '' }} />
                        <button type="button" class="px-3 py-2 text-lg text-[color:var(--primary)] transition hover:bg-slate-50 disabled:opacity-40" data-quantity-step="up" aria-label="Tăng số lượng">+</button>
                    </div>
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
                    const galleryThumbs = [...document.querySelectorAll('[data-gallery-image]')];
                    const updateGalleryState = (src) => galleryThumbs.forEach((thumb) => {
                        const active = thumb.dataset.galleryImage === src;
                        thumb.classList.toggle('border-[color:var(--accent)]', active);
                        thumb.classList.toggle('border-2', true);
                    });
                    const syncVariant = () => {
                        const variant = variants.find(item => item.size === size.value && item.color === color.value);
                        const colorVariant = variants.find(item => item.color === color.value && item.image);
                        const stock = variant?.stock ?? 0;
                        variantId.value = variant?.id ?? '';
                        quantity.max = stock || 1;
                        if (Number(quantity.value) > stock) quantity.value = stock || 1;
                        stockText.textContent = stock > 0 ? `Còn ${stock} sản phẩm cho lựa chọn này.` : 'Size và màu này hiện đã hết hàng.';
                        stockText.className = stock > 0 ? 'text-sm font-medium text-emerald-700' : 'text-sm font-medium text-rose-600';
                        if (stock < 1 && colorVariant) {
                            const colorSizes = variants
                                .filter(item => item.color === color.value)
                                .map(item => item.size)
                                .filter((item, index, items) => items.indexOf(item) === index)
                                .join(', ');
                            stockText.textContent = `Mau ${color.value} hien co o size: ${colorSizes || 'khong co'}. Vui long chon dung size.`;
                        }
                        button.disabled = stock < 1;
                        quantity.disabled = stock < 1;
                        if (mainImage) {
                            mainImage.src = colorVariant?.image || variant?.image || mainImage.dataset.defaultSrc;
                            updateGalleryState(mainImage.src);
                        }
                    };
                    galleryThumbs.forEach((thumb) => thumb.addEventListener('click', () => {
                        if (thumb.dataset.variantColor) {
                            color.value = thumb.dataset.variantColor;
                            syncVariant();
                            return;
                        }
                        if (mainImage) mainImage.src = thumb.dataset.galleryImage;
                        updateGalleryState(thumb.dataset.galleryImage);
                    }));
                    document.querySelectorAll('[data-quantity-step]').forEach((step) => step.addEventListener('click', () => {
                        const max = Number(quantity.max) || 1;
                        const next = step.dataset.quantityStep === 'up' ? Number(quantity.value) + 1 : Number(quantity.value) - 1;
                        quantity.value = Math.min(max, Math.max(1, next));
                    }));
                    size.addEventListener('change', syncVariant);
                    color.addEventListener('change', syncVariant);
                    const requestedSize = @json(request('size'));
                    const requestedColor = @json(request('color'));
                    const preferredVariant = variants.find(item => item.size === requestedSize && item.color === requestedColor)
                        || variants.find(item => item.size === requestedSize)
                        || variants.find(item => item.color === requestedColor)
                        || variants[0];
                    if (preferredVariant) {
                        size.value = preferredVariant.size;
                        color.value = preferredVariant.color;
                    }
                    syncVariant();
                })();
            </script>
            <section class="shop-shell mt-6 p-5" aria-labelledby="size-guide-title">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 id="size-guide-title" class="font-semibold text-[color:var(--primary)]">{{ $sizeGuide['title'] }}</h2>
                        <p class="mt-1 text-xs leading-relaxed text-[color:var(--text-muted)]">{{ $sizeGuide['note'] }}</p>
                    </div>
                    <span class="rounded-full bg-amber-50 px-3 py-1 text-xs font-semibold text-amber-800">Tham khảo</span>
                </div>
                @if($sizeGuide['audience'] === 'footwear')
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[420px] text-left text-sm">
                            <thead class="border-b border-[color:var(--line-soft)] text-xs uppercase tracking-wide text-[color:var(--text-muted)]">
                                <tr><th class="px-3 py-2">Size giày</th><th class="px-3 py-2">Dài bàn chân (cm)</th><th class="px-3 py-2">Ngang bàn chân (cm)</th></tr>
                            </thead>
                            <tbody class="divide-y divide-[color:var(--line-soft)]">
                                @foreach($sizeGuide['rows'] as $row)
                                    <tr><td class="px-3 py-2 font-semibold text-[color:var(--primary)]">{{ $row['size'] }}</td><td class="px-3 py-2">{{ $row['length'] }}</td><td class="px-3 py-2">{{ $row['width'] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <p class="mt-3 text-xs text-[color:var(--text-muted)]">Mẹo đo: đặt bàn chân lên giấy, đánh dấu từ gót đến đầu ngón dài nhất rồi đo bằng thước.</p>
                @elseif($sizeGuide['audience'] === 'children')
                    <div class="mt-4 overflow-x-auto">
                        <table class="w-full min-w-[520px] text-left text-sm">
                            <thead class="border-b border-[color:var(--line-soft)] text-xs uppercase tracking-wide text-[color:var(--text-muted)]">
                                <tr><th class="px-3 py-2">Size</th><th class="px-3 py-2">Chiều cao</th><th class="px-3 py-2">Cân nặng</th><th class="px-3 py-2">Độ tuổi</th></tr>
                            </thead>
                            <tbody class="divide-y divide-[color:var(--line-soft)]">
                                @foreach($sizeGuide['rows'] as $row)
                                    <tr><td class="px-3 py-2 font-semibold text-[color:var(--primary)]">{{ $row['size'] }}</td><td class="px-3 py-2">{{ $row['height'] }}</td><td class="px-3 py-2">{{ $row['weight'] }}</td><td class="px-3 py-2">{{ $row['age'] }}</td></tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @elseif($sizeGuide['audience'] === 'adult')
                    <div class="mt-4 grid gap-5 lg:grid-cols-2">
                        @foreach(['male' => 'Nam', 'female' => 'Nữ'] as $key => $label)
                            <div>
                                <h3 class="mb-2 text-sm font-semibold text-[color:var(--primary)]">{{ $label }}</h3>
                                <div class="overflow-x-auto">
                                    <table class="w-full min-w-[360px] text-left text-sm">
                                        <thead class="border-b border-[color:var(--line-soft)] text-xs uppercase tracking-wide text-[color:var(--text-muted)]"><tr><th class="px-2 py-2">Size</th><th class="px-2 py-2">Chiều cao</th><th class="px-2 py-2">Cân nặng</th></tr></thead>
                                        <tbody class="divide-y divide-[color:var(--line-soft)]">
                                            @foreach($sizeGuide[$key] as $row)
                                                <tr><td class="px-2 py-2 font-semibold text-[color:var(--primary)]">{{ $row['size'] }}</td><td class="px-2 py-2">{{ $row['height'] }}</td><td class="px-2 py-2">{{ $row['weight'] }}</td></tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="mt-4 rounded-lg bg-slate-50 px-3 py-2 text-sm text-[color:var(--text-muted)]">{{ $sizeGuide['note'] }}</p>
                @endif
            </section>
            <section class="shop-shell mt-6 p-5" aria-labelledby="review-summary-title">
                <div class="flex flex-wrap items-center justify-between gap-4">
                    <div>
                        <h2 id="review-summary-title" class="font-semibold text-[color:var(--primary)]">Đánh giá sản phẩm</h2>
                        <div class="mt-2 flex items-center gap-3">
                            <span class="text-3xl font-bold text-[color:var(--accent)]">{{ number_format($reviewStats['average'], 1, ',', '.') }}</span>
                            <div>
                                <div class="text-lg tracking-wide text-amber-500" aria-label="{{ $reviewStats['average'] }} trên 5 sao">★★★★★</div>
                                <p class="text-xs text-[color:var(--text-muted)]">{{ $reviewStats['total'] }} đánh giá đã được duyệt</p>
                            </div>
                        </div>
                    </div>
                    <div class="w-full max-w-md space-y-1.5">
                        @foreach($reviewStats['breakdown'] as $rating => $count)
                            @php($percentage = $reviewStats['total'] > 0 ? round(($count / $reviewStats['total']) * 100) : 0)
                            <div class="flex items-center gap-2 text-xs text-[color:var(--text-muted)]">
                                <span class="w-10 shrink-0">{{ $rating }} sao</span>
                                <div class="h-2 flex-1 overflow-hidden rounded-full bg-slate-100"><span class="block h-full rounded-full bg-amber-400" style="width: {{ $percentage }}%"></span></div>
                                <span class="w-8 text-right">{{ $count }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
            @auth
                <form method="post" action="{{ route('wishlist.toggle', $product) }}" class="mt-3">@csrf
                    <button type="submit" class="secondary-cta">Thêm vào yêu thích</button>
                </form>
            @endauth

            @auth
                @if($userReview)
                    <form method="post" action="{{ route('reviews.update', $userReview) }}" enctype="multipart/form-data" class="shop-shell mt-10 space-y-3 border-t border-[color:var(--line-soft)] p-5 pt-8">
                        @csrf
                        @method('PATCH')
                        <label class="block text-sm font-medium text-[color:var(--primary)]">Ảnh hoặc video trải nghiệm <span class="font-normal text-[color:var(--text-muted)]">(tùy chọn, tối đa 20MB)</span><input name="media" type="file" accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm" class="mt-1 block w-full text-sm" /></label>
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
                    <form method="post" action="{{ route('reviews.store', $product) }}" enctype="multipart/form-data" class="shop-shell mt-10 space-y-3 border-t border-[color:var(--line-soft)] p-5 pt-8">
                        @csrf
                        <label class="block text-sm font-medium text-[color:var(--primary)]">Ảnh hoặc video trải nghiệm <span class="font-normal text-[color:var(--text-muted)]">(tùy chọn, tối đa 20MB)</span><input name="media" type="file" accept="image/jpeg,image/png,image/webp,video/mp4,video/quicktime,video/webm" class="mt-1 block w-full text-sm" /></label>
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
                            @if($r->admin_reply)
                                <div class="mt-3 border-l-2 border-[color:var(--accent)] bg-rose-50 px-3 py-2 text-sm">
                                    <p class="font-semibold text-[color:var(--primary)]">Phản hồi từ QUDENA</p>
                                    <p class="mt-1 text-[color:var(--text-muted)]">{{ $r->admin_reply }}</p>
                                    @if($r->admin_replied_at)<p class="mt-1 text-xs text-[color:var(--text-muted)]">{{ $r->admin_replied_at->format('d/m/Y H:i') }}</p>@endif
                                </div>
                            @endif
                            @if($r->mediaUrl())
                                @if($r->isVideo())
                                    <video src="{{ $r->mediaUrl() }}" controls preload="metadata" playsinline class="mt-3 h-40 w-72 rounded-lg border border-[color:var(--line-soft)] bg-slate-900 object-contain">Trình duyệt không hỗ trợ phát video.</video>
                                @else
                                    <button type="button" class="mt-3 block cursor-zoom-in" data-review-image="{{ $r->mediaUrl() }}" aria-label="Phóng to ảnh đánh giá">
                                        <img src="{{ $r->mediaUrl() }}" alt="Ảnh đánh giá của {{ $r->user->name }}" loading="lazy" class="h-32 w-28 rounded-lg border border-[color:var(--line-soft)] object-cover transition hover:opacity-80" />
                                    </button>
                                @endif
                            @endif
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
    <div id="review-image-modal" class="fixed inset-0 z-[200] hidden items-center justify-center bg-black/80 p-5" role="dialog" aria-modal="true" aria-label="Ảnh đánh giá phóng to">
        <button type="button" id="review-image-close" class="absolute right-5 top-5 text-3xl text-white" aria-label="Đóng ảnh">&times;</button>
        <img id="review-image-preview" src="" alt="Ảnh đánh giá phóng to" class="max-h-[90vh] max-w-[92vw] rounded-lg object-contain shadow-2xl" />
    </div>
    <script>
        (() => {
            const modal = document.getElementById('review-image-modal');
            const preview = document.getElementById('review-image-preview');
            const close = () => { modal?.classList.add('hidden'); modal?.classList.remove('flex'); if (preview) preview.src = ''; };
            document.querySelectorAll('[data-review-image]').forEach((button) => button.addEventListener('click', () => {
                if (!modal || !preview) return;
                preview.src = button.dataset.reviewImage || '';
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }));
            document.getElementById('review-image-close')?.addEventListener('click', close);
            modal?.addEventListener('click', (event) => { if (event.target === modal) close(); });
            document.addEventListener('keydown', (event) => { if (event.key === 'Escape') close(); });
        })();
    </script>
@endsection
