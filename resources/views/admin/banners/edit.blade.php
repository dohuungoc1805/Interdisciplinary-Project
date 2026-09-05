@extends('layouts.admin')
@section('title', 'Sửa banner')
@section('content')
    @php
        $selectedId = old('product_id', $banner->product_id);
        $selectedName = '';
        if ($selectedId) {
            if ((int) $selectedId === (int) $banner->product_id && $banner->product) {
                $selectedName = $banner->product->name;
            } else {
                $selectedName = \App\Models\Product::query()->whereKey($selectedId)->value('name') ?? '';
            }
        }
    @endphp
    <div class="admin-shell max-w-lg">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Sửa banner</h1>
                <p class="admin-page-lead">{{ $banner->title }}</p>
            </div>
            <a href="{{ route('admin.banners.index') }}" class="admin-btn-outline shrink-0">← Danh sách</a>
        </div>
        <div class="admin-card space-y-4">
            <form method="post" action="{{ route('admin.banners.update', $banner) }}" enctype="multipart/form-data" class="space-y-4 text-sm">@csrf @method('PUT')
                <div>
                    <label class="admin-label" for="title">Tiêu đề</label>
                    <input id="title" class="admin-input" name="title" value="{{ old('title', $banner->title) }}" required />
                </div>
                <div>
                    <label class="admin-label" for="link_url">Liên kết</label>
                    <input id="link_url" class="admin-input" name="link_url" value="{{ old('link_url', $banner->link_url) }}" />
                </div>
                @include('admin.banners.partials.product-picker', [
                    'selectedProductId' => $selectedId,
                    'selectedProductName' => $selectedName,
                ])
                <div>
                    <label class="admin-label" for="image">Thay ảnh</label>
                    <p class="mb-2 text-xs text-slate-500">Xem trước hiện tại:</p>
                    <img src="{{ $banner->imagePublicUrl() }}" class="mb-3 h-28 w-full max-w-xs rounded-lg border border-slate-200 object-cover" alt="" />
                    <input id="image" name="image" type="file" accept="image/*" class="w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-orange-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-orange-800 hover:file:bg-orange-100" />
                </div>
                <div>
                    <label class="admin-label" for="sort_order">Thứ tự hiển thị</label>
                    <input id="sort_order" class="admin-input" name="sort_order" type="number" value="{{ old('sort_order', $banner->sort_order) }}" />
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="admin-label" for="starts_at">Bắt đầu</label>
                        <input id="starts_at" class="admin-input" name="starts_at" type="datetime-local" value="{{ old('starts_at', $banner->starts_at?->format('Y-m-d\TH:i')) }}" />
                    </div>
                    <div>
                        <label class="admin-label" for="ends_at">Kết thúc</label>
                        <input id="ends_at" class="admin-input" name="ends_at" type="datetime-local" value="{{ old('ends_at', $banner->ends_at?->format('Y-m-d\TH:i')) }}" />
                    </div>
                </div>
                <label class="flex items-center gap-2 text-slate-700">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-orange-600" @checked(! $errors->any() ? $banner->is_active : request()->boolean('is_active')) /> Đang kích hoạt
                </label>
                <button class="admin-btn" type="submit">Cập nhật</button>
            </form>
        </div>
    </div>
@endsection
