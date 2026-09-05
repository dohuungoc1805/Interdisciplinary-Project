@extends('layouts.admin')
@section('title', 'Banner')
@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Banner</h1>
                <p class="admin-page-lead">Ảnh trượt trang chủ, liên kết và thời gian hiển thị.</p>
            </div>
            <a href="{{ route('admin.banners.create') }}" class="admin-btn shrink-0">Thêm banner</a>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            @foreach($banners as $b)
                <div class="admin-card overflow-hidden p-0">
                    <img src="{{ $b->imagePublicUrl() }}" class="h-40 w-full object-cover" alt="" />
                    <div class="flex items-start justify-between gap-3 border-t border-slate-100 px-4 py-3 text-sm">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $b->title }}</p>
                            @if($b->product_id)
                                <span class="mt-1 inline-block rounded-md bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800">Sản phẩm</span>
                            @endif
                        </div>
                        <div class="flex shrink-0 flex-col items-end gap-1 sm:flex-row sm:items-center sm:gap-3">
                            <a href="{{ route('admin.banners.edit', $b) }}" class="admin-link">Sửa</a>
                            <form class="inline" method="post" action="{{ route('admin.banners.destroy', $b) }}" onsubmit="return confirm('Xóa banner này?')">@csrf @method('DELETE')
                                <button type="submit" class="admin-link-danger">Xóa</button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $banners->links() }}</div>
    </div>
@endsection
