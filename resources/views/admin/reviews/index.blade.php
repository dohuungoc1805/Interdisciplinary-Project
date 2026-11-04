@extends('layouts.admin')
@section('title', 'Đánh giá sản phẩm')
@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Đánh giá sản phẩm</h1>
                <p class="admin-page-lead">Chọn một sản phẩm để lọc nhanh các đánh giá cần xử lý.</p>
            </div>
        </div>

        <section class="mb-8 space-y-4" aria-labelledby="review-product-summary-title">
            <div>
                <h2 id="review-product-summary-title" class="text-lg font-semibold text-slate-900">Tổng quan theo sản phẩm</h2>
                <p class="mt-1 text-sm text-slate-500">Bấm vào thẻ sản phẩm để xem và phản hồi đánh giá bên dưới.</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-3">
                <button type="button" data-review-filter="all" class="admin-card text-left transition hover:-translate-y-0.5 hover:border-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-300">
                    <div class="flex items-start justify-between gap-3">
                        <h3 class="font-semibold text-slate-900">Tất cả đánh giá</h3>
                        <span class="text-lg font-bold text-orange-500">⌕</span>
                    </div>
                    <p class="mt-4 text-sm text-slate-500">Hiển thị toàn bộ đánh giá của khách hàng.</p>
                </button>
                @forelse($productSummaries as $productSummary)
                    <button type="button" data-review-filter="{{ $productSummary->id }}" class="admin-card text-left transition hover:-translate-y-0.5 hover:border-orange-300 focus:outline-none focus:ring-2 focus:ring-orange-300">
                        <div class="flex items-start justify-between gap-3">
                            <h3 class="font-semibold text-slate-900">{{ $productSummary->name }}</h3>
                            <span class="shrink-0 text-lg font-bold text-amber-500">{{ $productSummary->approved_rating_average !== null ? number_format((float) $productSummary->approved_rating_average, 1, ',', '.') : '—' }} ★</span>
                        </div>
                        <div class="mt-4 grid grid-cols-3 gap-2 text-center text-xs">
                            <div class="rounded-lg bg-slate-50 p-2"><strong class="block text-base text-slate-900">{{ $productSummary->reviews_count }}</strong><span class="text-slate-500">Tổng số</span></div>
                            <div class="rounded-lg bg-emerald-50 p-2"><strong class="block text-base text-emerald-700">{{ $productSummary->approved_reviews_count }}</strong><span class="text-slate-500">Đã duyệt</span></div>
                            <div class="rounded-lg bg-amber-50 p-2"><strong class="block text-base text-amber-700">{{ $productSummary->pending_reviews_count }}</strong><span class="text-slate-500">Chờ duyệt</span></div>
                        </div>
                    </button>
                @empty
                    <div class="admin-card text-sm text-slate-500">Chưa có sản phẩm nào nhận được đánh giá.</div>
                @endforelse
            </div>
            <div>{{ $productSummaries->links() }}</div>
        </section>

        <section id="review-list" class="space-y-4" aria-labelledby="review-list-title">
            <div>
                <h2 id="review-list-title" class="text-lg font-semibold text-slate-900">Đánh giá của khách hàng</h2>
                <p id="review-filter-label" class="mt-1 text-sm text-slate-500">Đang hiển thị tất cả đánh giá.</p>
            </div>
            @forelse($reviews as $review)
                <article data-review-product="{{ $review->product_id }}" class="admin-card space-y-3 text-sm">
                    <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-slate-100 pb-3">
                        <div>
                            <a href="{{ route('products.show', $review->product->slug) }}" target="_blank" rel="noopener" class="font-semibold text-slate-900 hover:text-orange-600">{{ $review->product->name }}</a>
                            <span class="ml-2 text-amber-500">{{ $review->rating }}/5 ★</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-xs text-slate-500">{{ $review->user->email }}</span>
                            <span @class([
                                'rounded-full px-2.5 py-1 text-xs font-semibold',
                                'bg-emerald-100 text-emerald-700' => $review->is_approved,
                                'bg-amber-100 text-amber-700' => ! $review->is_approved,
                            ])>{{ $review->is_approved ? 'Đã duyệt' : 'Chờ duyệt' }}</span>
                        </div>
                    </div>
                    <p class="leading-relaxed text-slate-700">{{ $review->comment ?: 'Khách hàng không để lại nội dung.' }}</p>
                    @if($review->mediaUrl())
                        @if($review->isVideo())
                            <video src="{{ $review->mediaUrl() }}" controls preload="metadata" playsinline class="h-40 w-72 rounded-lg border border-slate-200 bg-slate-900 object-contain">Trình duyệt không hỗ trợ phát video.</video>
                        @else
                            <a href="{{ $review->mediaUrl() }}" target="_blank" rel="noopener" title="Mở ảnh đánh giá kích thước đầy đủ">
                                <img src="{{ $review->mediaUrl() }}" alt="Ảnh đánh giá" class="h-32 w-28 rounded-lg border border-slate-200 object-cover transition hover:opacity-80" />
                            </a>
                        @endif
                    @endif
                    <form method="post" action="{{ route('admin.reviews-moderation.reply', $review) }}" class="space-y-2 rounded-lg bg-slate-50 p-3">
                        @csrf
                        <label class="block text-xs font-semibold uppercase tracking-wide text-slate-500">Phản hồi của cửa hàng</label>
                        <textarea name="admin_reply" rows="2" maxlength="2000" class="admin-input w-full" placeholder="Nhập câu trả lời cho khách hàng...">{{ old('admin_reply', $review->admin_reply) }}</textarea>
                        <button class="admin-btn text-sm" type="submit">{{ $review->admin_reply ? 'Cập nhật phản hồi' : 'Gửi phản hồi' }}</button>
                    </form>
                    <div class="flex flex-wrap gap-2">
                        @if(! $review->is_approved)
                            <form method="post" action="{{ route('admin.reviews-moderation.approve', $review) }}">@csrf
                                <button class="admin-btn text-sm" type="submit">Duyệt đánh giá</button>
                            </form>
                        @endif
                        <form method="post" action="{{ route('admin.reviews-moderation.reject', $review) }}" onsubmit="return confirm('{{ $review->is_approved ? 'Xóa đánh giá đã duyệt này?' : 'Từ chối và xóa đánh giá này?' }}')">@csrf @method('DELETE')
                            <button class="admin-btn-outline border-red-200 text-red-700 hover:bg-red-50" type="submit">{{ $review->is_approved ? 'Xóa đánh giá' : 'Từ chối' }}</button>
                        </form>
                    </div>
                </article>
            @empty
                <div class="admin-card py-12 text-center text-slate-500">Chưa có đánh giá nào.</div>
            @endforelse
            <div>{{ $reviews->links() }}</div>
        </section>
    </div>
    <script>
        (() => {
            const buttons = document.querySelectorAll('[data-review-filter]');
            const cards = document.querySelectorAll('[data-review-product]');
            const label = document.getElementById('review-filter-label');
            buttons.forEach((button) => button.addEventListener('click', () => {
                const filter = button.dataset.reviewFilter;
                cards.forEach((card) => { card.hidden = filter !== 'all' && card.dataset.reviewProduct !== filter; });
                const productName = filter === 'all' ? 'tất cả đánh giá' : button.querySelector('h3')?.textContent?.trim() || 'sản phẩm đã chọn';
                if (label) label.textContent = `Đang hiển thị đánh giá của ${productName}.`;
                document.getElementById('review-list')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }));
        })();
    </script>
@endsection
