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
            <div class="min-w-[11rem]">
                <label class="admin-label" for="category">Danh mục</label>
                <select id="category" name="category" class="admin-input">
                    <option value="">Tất cả danh mục</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" @selected((int) request('category') === $category->id)>{{ $category->parent_id ? '— ' : '' }}{{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[10rem]">
                <label class="admin-label" for="availability">Tồn kho</label>
                <select id="availability" name="availability" class="admin-input">
                    <option value="">Tất cả tồn kho</option>
                    <option value="in_stock" @selected(request('availability') === 'in_stock')>Còn hàng</option>
                    <option value="low_stock" @selected(request('availability') === 'low_stock')>Tồn kho thấp</option>
                    <option value="out_of_stock" @selected(request('availability') === 'out_of_stock')>Hết hàng</option>
                </select>
            </div>
            <div class="min-w-[10rem]">
                <label class="admin-label" for="visibility">Hiển thị</label>
                <select id="visibility" name="visibility" class="admin-input">
                    <option value="">Tất cả</option>
                    <option value="active" @selected(request('visibility') === 'active')>Đang bán</option>
                    <option value="inactive" @selected(request('visibility') === 'inactive')>Đang ẩn</option>
                </select>
            </div>
            <label class="flex h-10 items-center gap-2 text-sm font-medium text-slate-700">
                <input type="checkbox" name="sale" value="1" class="rounded border-slate-300 text-orange-600" @checked(request()->boolean('sale'))>
                Đang sale
            </label>
            <button class="admin-btn-outline" type="submit">Lọc</button>
            <a href="{{ route('admin.products.index') }}" class="admin-link text-sm">Đặt lại</a>
        </form>

        <div class="admin-table-wrap overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="w-20">Ảnh</th>
                        <th>Sản phẩm</th>
                        <th>Danh mục</th>
                        <th>Giá</th>
                        <th>Tồn kho</th>
                        <th>Trạng thái</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($products as $p)
                        <tr>
                            <td><img src="{{ $p->mainImageUrl() }}" alt="" class="h-12 w-10 border border-slate-200 object-cover" /></td>
                            <td>
                                <a href="{{ route('products.show', $p->slug) }}" class="font-semibold text-slate-900 hover:text-orange-600" target="_blank" rel="noopener">{{ $p->name }}</a>
                                @if($p->sku)<p class="mt-1 font-mono text-xs text-slate-500">{{ $p->sku }}</p>@endif
                                <div class="mt-1 space-x-1 text-xs">
                                    @if($p->is_hot)<span class="rounded-md bg-amber-100 px-2 py-0.5 font-medium text-amber-900">Hot</span>@endif
                                    @if($p->is_on_sale)<span class="rounded-md bg-red-100 px-2 py-0.5 font-medium text-red-800">Sale</span>@endif
                                    @if($p->is_new)<span class="rounded-md bg-pink-100 px-2 py-0.5 font-medium text-pink-900">Mới</span>@endif
                                </div>
                            </td>
                            <td class="text-slate-600">{{ $p->category?->name ?? '—' }}</td>
                            <td class="font-medium">{{ number_format((float) $p->price, 0, ',', '.') }} ₫</td>
                            <td>
                                <span @class([
                                    'font-semibold',
                                    'text-red-700' => (int) $p->stock_total === 0,
                                    'text-amber-700' => (int) $p->stock_total > 0 && (int) $p->stock_total <= config('shop.low_stock_threshold'),
                                    'text-emerald-700' => (int) $p->stock_total > config('shop.low_stock_threshold'),
                                ])>{{ (int) $p->stock_total }}</span>
                            </td>
                            <td><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ $p->is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-600' }}">{{ $p->is_active ? 'Đang bán' : 'Đang ẩn' }}</span></td>
                            <td class="whitespace-nowrap text-right">
                                <a class="admin-link" href="{{ route('admin.products.edit', $p) }}">Sửa</a>
                                <form class="inline" method="post" action="{{ route('admin.products.destroy', $p) }}" onsubmit="return confirm('Xóa sản phẩm này?')">@csrf @method('DELETE')
                                    <button type="submit" class="admin-link-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    </div>
@endsection
