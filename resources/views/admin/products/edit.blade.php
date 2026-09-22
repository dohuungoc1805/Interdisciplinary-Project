@extends('layouts.admin')
@section('title', 'Sửa sản phẩm')
@section('content')
    @php
        $variantRows = old('variants', $product->variants->map(fn ($variant) => [
            'size' => $variant->size,
            'color' => $variant->color,
            'sku' => $variant->sku,
            'stock' => $variant->stock,
            'price' => $variant->price,
        ])->values()->all());
    @endphp
    <div class="admin-shell max-w-2xl">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Sửa sản phẩm</h1>
                <p class="admin-page-lead">{{ $product->name }}</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="admin-btn-outline shrink-0">&larr; Danh sách</a>
        </div>
        <div class="admin-card space-y-5">
            <form method="post" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data" class="space-y-5 text-sm">
                @csrf
                @method('PUT')
                <div>
                    <label class="admin-label" for="category_id">Danh mục</label>
                    <select id="category_id" name="category_id" class="admin-input" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected(old('category_id', $product->category_id) == $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="name">Tên sản phẩm</label>
                    <input id="name" name="name" class="admin-input" required value="{{ old('name', $product->name) }}" />
                </div>
                <div>
                    <label class="admin-label" for="description">Mô tả</label>
                    <textarea id="description" name="description" class="admin-input min-h-[5rem]" rows="3">{{ old('description', $product->description) }}</textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="admin-label" for="price">Giá (đ)</label>
                        <input id="price" name="price" type="number" min="0" step="0.01" class="admin-input" required value="{{ old('price', $product->price) }}" />
                    </div>
                    <div>
                        <label class="admin-label" for="compare_price">Giá gốc / so sánh</label>
                        <input id="compare_price" name="compare_price" type="number" min="0" step="0.01" class="admin-input" value="{{ old('compare_price', $product->compare_price) }}" />
                    </div>
                </div>
                <div>
                    <label class="admin-label" for="sku">SKU sản phẩm</label>
                    <input id="sku" name="sku" class="admin-input font-mono text-xs" value="{{ old('sku', $product->sku) }}" />
                </div>
                <div>
                    <label class="admin-label" for="image">Thay ảnh chính</label>
                    <p class="mb-2 text-xs text-slate-500">
                        Hiện tại:
                        @if($product->main_image)
                            <img src="{{ $product->mainImageUrl() }}" class="mt-1 h-20 rounded-md border border-slate-200 object-cover" alt="Ảnh hiện tại" />
                        @else
                            <span class="text-slate-400">Chưa có</span>
                        @endif
                    </p>
                    <input id="image" name="image" type="file" accept="image/*" class="w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-orange-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-orange-800 hover:file:bg-orange-100" />
                </div>
                <div>
                    <label class="admin-label" for="gallery">Thêm ảnh thư viện</label>
                    <input id="gallery" name="gallery[]" type="file" accept="image/*" multiple class="w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200" />
                </div>
                <div class="flex flex-wrap gap-x-5 gap-y-2">
                    <label class="flex items-center gap-2 text-slate-700"><input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300 text-orange-600" @checked(old('is_featured', $product->is_featured)) /> Nổi bật (trang chủ)</label>
                    <label class="flex items-center gap-2 text-slate-700"><input type="checkbox" name="is_hot" value="1" class="rounded border-slate-300 text-orange-600" @checked(old('is_hot', $product->is_hot)) /> Hot</label>
                    <label class="flex items-center gap-2 text-slate-700"><input type="checkbox" name="is_on_sale" value="1" class="rounded border-slate-300 text-orange-600" @checked(old('is_on_sale', $product->is_on_sale)) /> Đang sale</label>
                    <label class="flex items-center gap-2 text-slate-700"><input type="checkbox" name="is_new" value="1" class="rounded border-slate-300 text-orange-600" @checked(old('is_new', $product->is_new)) /> Hàng mới</label>
                    <label class="flex items-center gap-2 text-slate-700">
                        <input type="hidden" name="is_active" value="0" />
                        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-orange-600" @checked(! $errors->any() ? $product->is_active : request()->boolean('is_active')) /> Đang bán
                    </label>
                </div>
                <x-admin.product-variant-editor :variants="$variantRows" />
                <button class="admin-btn" type="submit">Cập nhật</button>
            </form>
        </div>
    </div>
@endsection
