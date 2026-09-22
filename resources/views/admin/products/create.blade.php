@extends('layouts.admin')
@section('title', 'Thêm sản phẩm')
@section('content')
    @php
        $variantRows = old('variants', [['size' => '', 'color' => '', 'stock' => 0, 'price' => '', 'sku' => '']]);
    @endphp
    <div class="admin-shell max-w-2xl">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Thêm sản phẩm</h1>
                <p class="admin-page-lead">Thông tin sản phẩm, hình ảnh, size, màu sắc và tồn kho.</p>
            </div>
            <a href="{{ route('admin.products.index') }}" class="admin-btn-outline shrink-0">&larr; Danh sách</a>
        </div>
        <div class="admin-card space-y-5">
            <form method="post" action="{{ route('admin.products.store') }}" enctype="multipart/form-data" class="space-y-5 text-sm">
                @csrf
                <div>
                    <label class="admin-label" for="category_id">Danh mục</label>
                    <select id="category_id" name="category_id" class="admin-input" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" @selected((string) old('category_id', $categories->first()?->id) === (string) $category->id)>{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="name">Tên sản phẩm</label>
                    <input id="name" name="name" class="admin-input" required value="{{ old('name') }}" />
                </div>
                <div>
                    <label class="admin-label" for="description">Mô tả</label>
                    <textarea id="description" name="description" class="admin-input min-h-[5rem]" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="admin-label" for="price">Giá (đ)</label>
                        <input id="price" name="price" type="number" min="0" step="0.01" class="admin-input" required value="{{ old('price') }}" />
                    </div>
                    <div>
                        <label class="admin-label" for="compare_price">Giá gốc / so sánh</label>
                        <input id="compare_price" name="compare_price" type="number" min="0" step="0.01" class="admin-input" value="{{ old('compare_price') }}" />
                    </div>
                </div>
                <div>
                    <label class="admin-label" for="sku">SKU sản phẩm</label>
                    <input id="sku" name="sku" class="admin-input font-mono text-xs" value="{{ old('sku') }}" />
                </div>
                <div>
                    <label class="admin-label" for="image">Ảnh chính</label>
                    <input id="image" name="image" type="file" accept="image/*" required class="w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-orange-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-orange-800 hover:file:bg-orange-100" />
                </div>
                <div>
                    <label class="admin-label" for="gallery">Thư viện ảnh</label>
                    <input id="gallery" name="gallery[]" type="file" accept="image/*" multiple class="w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-slate-100 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-slate-700 hover:file:bg-slate-200" />
                </div>
                <div class="flex flex-wrap gap-x-5 gap-y-2">
                    <label class="flex items-center gap-2 text-slate-700"><input type="checkbox" name="is_featured" value="1" class="rounded border-slate-300 text-orange-600" @checked(old('is_featured')) /> Nổi bật (trang chủ)</label>
                    <label class="flex items-center gap-2 text-slate-700"><input type="checkbox" name="is_hot" value="1" class="rounded border-slate-300 text-orange-600" @checked(old('is_hot')) /> Hot</label>
                    <label class="flex items-center gap-2 text-slate-700"><input type="checkbox" name="is_on_sale" value="1" class="rounded border-slate-300 text-orange-600" @checked(old('is_on_sale')) /> Đang sale</label>
                    <label class="flex items-center gap-2 text-slate-700"><input type="checkbox" name="is_new" value="1" class="rounded border-slate-300 text-orange-600" @checked(old('is_new')) /> Hàng mới</label>
                    <label class="flex items-center gap-2 text-slate-700">
                        <input type="hidden" name="is_active" value="0" />
                        <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-orange-600" @checked(! $errors->any() ? true : request()->boolean('is_active')) /> Đang bán
                    </label>
                </div>
                <x-admin.product-variant-editor :variants="$variantRows" />
                <button class="admin-btn" type="submit">Tạo sản phẩm</button>
            </form>
        </div>
    </div>
@endsection
