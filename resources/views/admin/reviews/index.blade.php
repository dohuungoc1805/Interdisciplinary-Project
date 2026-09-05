@extends('layouts.admin')
@section('title', 'Đánh giá chờ duyệt')
@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Đánh giá chờ duyệt</h1>
                <p class="admin-page-lead">Duyệt hoặc từ chối bình luận của khách.</p>
            </div>
        </div>
        <div class="space-y-4">
            @forelse($reviews as $r)
                <div class="admin-card space-y-3 text-sm">
                    <div class="flex flex-wrap items-baseline justify-between gap-2 border-b border-slate-100 pb-2">
                        <p class="font-semibold text-slate-900">
                            <a href="{{ route('products.show', $r->product->slug) }}" class="hover:text-orange-600" target="_blank" rel="noopener">{{ $r->product->name }}</a>
                            <span class="font-normal text-slate-500">— {{ $r->rating }}/5</span>
                        </p>
                        <span class="text-xs text-slate-500">{{ $r->user->email }}</span>
                    </div>
                    <p class="leading-relaxed text-slate-700">{{ $r->comment }}</p>
                    <div class="flex flex-wrap gap-3 pt-1">
                        <form method="post" action="{{ route('admin.reviews-moderation.approve', $r) }}" class="inline">@csrf
                            <button class="admin-btn text-sm" type="submit">Duyệt</button>
                        </form>
                        <form method="post" action="{{ route('admin.reviews-moderation.reject', $r) }}" class="inline" onsubmit="return confirm('Từ chối và xóa đánh giá này?')">@csrf @method('DELETE')
                            <button class="admin-btn-outline border-red-200 text-red-700 hover:bg-red-50" type="submit">Từ chối</button>
                        </form>
                    </div>
                </div>
            @empty
                <div class="admin-card py-12 text-center text-slate-500">Không có đánh giá chờ duyệt.</div>
            @endforelse
        </div>
        <div class="mt-6">{{ $reviews->links() }}</div>
    </div>
@endsection
