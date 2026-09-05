@extends('layouts.admin')
@section('title', 'Sản phẩm')
@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Sản phẩm</h1>
                <p class="admin-page-lead">Quản lý giá, ảnh, biến thể và nhãn marketing.</p>
            </div>
            <div class="flex flex-wrap items-center gap-2">
                <a href="{{ route('admin.products.template') }}" class="admin-btn-outline">Tải template Excel</a>
                <a href="{{ route('admin.products.export') }}" class="admin-btn-outline">Export Excel</a>
                <a href="{{ route('admin.products.create') }}" class="admin-btn shrink-0">Thêm sản phẩm</a>
            </div>
        </div>

        <form method="post" action="{{ route('admin.products.import') }}" enctype="multipart/form-data" class="admin-card mb-6 flex flex-wrap items-end gap-3">
            @csrf
            <div class="min-w-[16rem] flex-1">
                <label class="admin-label" for="file">Import Excel theo SKU</label>
                <input id="file" type="file" name="file" class="admin-input" accept=".xlsx,.xls,.csv" required />
            </div>
            <button class="admin-btn" type="submit">Import</button>
        </form>

        <form method="get" class="admin-card mb-6 flex flex-wrap items-end gap-3">
            <div class="min-w-[12rem] flex-1">
                <label class="admin-label" for="q">Tìm kiếm</label>
                <input id="q" name="q" class="admin-input" value="{{ request('q') }}" placeholder="Tên hoặc SKU…" />
            </div>
            <button class="admin-btn-outline" type="submit">Lọc</button>
        </form>

        <div class="admin-card divide-y divide-slate-100 p-0">
            @foreach($products as $p)
                <div class="flex flex-col gap-3 px-5 py-4 transition hover:bg-slate-50/80 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <a href="{{ route('products.show', $p->slug) }}" class="font-semibold text-slate-900 hover:text-orange-600" target="_blank" rel="noopener">{{ $p->name }}</a>
                        <span class="text-slate-500">({{ $p->category?->name }})</span>
                        <span class="ml-2 space-x-1 text-xs">
                            @if($p->is_hot)<span class="rounded-md bg-amber-100 px-2 py-0.5 font-medium text-amber-900">Hot</span>@endif
                            @if($p->is_on_sale)<span class="rounded-md bg-red-100 px-2 py-0.5 font-medium text-red-800">Sale</span>@endif
                            @if($p->is_new)<span class="rounded-md bg-pink-100 px-2 py-0.5 font-medium text-pink-900">Mới</span>@endif
                        </span>
                    </div>
                    <div class="flex shrink-0 items-center gap-4 text-sm">
                        <a class="admin-link" href="{{ route('admin.products.edit', $p) }}">Sửa</a>
                        <form class="inline" method="post" action="{{ route('admin.products.destroy', $p) }}" onsubmit="return confirm('Xóa sản phẩm này?')">@csrf @method('DELETE')
                            <button type="submit" class="admin-link-danger">Xóa</button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    </div>
@endsection
