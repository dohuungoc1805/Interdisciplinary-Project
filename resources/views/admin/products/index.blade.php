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
                <p class="mb-2 text-xs text-slate-500">Template hỗ trợ nhiều dòng biến thể, URL ảnh tại cột <code>main_image_url</code> và trạng thái sản phẩm.</p>
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
            <div class="min-w-[11rem]">
                <label class="admin-label" for="status">Trạng thái</label>
                <select id="status" name="status" class="admin-input">
                    <option value="">Tất cả trạng thái</option>
                    <option value="active" @selected(request('status') === 'active')>Đang bán</option>
                    <option value="draft" @selected(request('status') === 'draft')>Bản nháp</option>
                    <option value="hidden" @selected(request('status') === 'hidden')>Tạm ẩn</option>
                    <option value="discontinued" @selected(request('status') === 'discontinued')>Ngừng kinh doanh</option>
                </select>
            </div>
            <label class="flex h-10 items-center gap-2 text-sm font-medium text-slate-700">
                <input type="checkbox" name="sale" value="1" class="rounded border-slate-300 text-orange-600" @checked(request()->boolean('sale'))>
                Đang sale
            </label>
            <button class="admin-btn-outline" type="submit">Lọc</button>
            <a href="{{ route('admin.products.index') }}" class="admin-link text-sm">Đặt lại</a>
        </form>

        <form method="post" action="{{ route('admin.products.bulk') }}" id="bulk-product-form" class="admin-card mb-6 space-y-4 border-blue-100 bg-blue-50/30">
            @csrf
            <div class="flex flex-wrap items-center justify-between gap-2">
                <div><h2 class="text-sm font-bold text-slate-800">Thao tác hàng loạt</h2><p class="mt-1 text-xs text-slate-500">Chọn sản phẩm rồi áp dụng một thay đổi cho tất cả.</p></div>
                <span id="selected-product-count" class="text-xs font-semibold text-blue-700">0 sản phẩm được chọn</span>
            </div>
            <div class="flex flex-wrap items-end gap-3">
                <label class="admin-label flex items-center gap-2"><input type="checkbox" id="select-all-products" class="rounded border-slate-300 text-blue-600"> Chọn tất cả trang này</label>
                <select name="action" id="bulk-action" class="admin-input w-auto min-w-52" required>
                    <option value="">Chọn thao tác</option>
                    <option value="activate">Bật bán</option>
                    <option value="deactivate">Ngừng kinh doanh</option>
                    <option value="category">Đổi danh mục</option>
                    <option value="price">Cập nhật giá</option>
                    <option value="price_increase_percent">Tăng giá theo %</option>
                    <option value="price_decrease_percent">Giảm giá theo %</option>
                    <option value="stock">Cập nhật tồn kho</option>
                    <option value="stock_increase">Tăng tồn kho</option>
                    <option value="stock_decrease">Giảm tồn kho</option>
                </select>
                <select name="category_id" id="bulk-category" class="admin-input hidden w-auto min-w-52">
                    <option value="">Chọn danh mục mới</option>
                    @foreach($categories as $category)<option value="{{ $category->id }}">{{ $category->parent_id ? '— ' : '' }}{{ $category->name }}</option>@endforeach
                </select>
                <input type="number" name="price" id="bulk-price" min="0" step="0.01" placeholder="Giá mới" class="admin-input hidden w-36" />
                <input type="number" name="stock" id="bulk-stock" min="0" max="100000" placeholder="Tồn kho mới" class="admin-input hidden w-36" />
                <input type="number" name="percent" id="bulk-percent" min="0" max="1000" step="0.01" placeholder="Phần trăm" class="admin-input hidden w-36" />
                <input type="number" name="quantity" id="bulk-quantity" min="0" max="100000" placeholder="Số lượng thay đổi" class="admin-input hidden w-40" />
                <button type="submit" class="admin-btn disabled:cursor-not-allowed disabled:opacity-50" disabled id="bulk-submit">Áp dụng</button>
            </div>
        </form>

        <div class="admin-table-wrap overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="w-10"><span class="sr-only">Chọn</span></th>
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
                            <td><input type="checkbox" name="product_ids[]" value="{{ $p->id }}" form="bulk-product-form" class="product-checkbox rounded border-slate-300 text-blue-600"></td>
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
                            @php $status = $p->status ?? ($p->is_active ? 'active' : 'discontinued'); @endphp
                            <td><span class="rounded-full px-2.5 py-1 text-xs font-medium {{ match($status) { 'active' => 'bg-emerald-100 text-emerald-800', 'draft' => 'bg-violet-100 text-violet-800', 'hidden' => 'bg-amber-100 text-amber-800', default => 'bg-slate-100 text-slate-600' } }}">{{ match($status) { 'active' => 'Đang bán', 'draft' => 'Bản nháp', 'hidden' => 'Tạm ẩn', default => 'Ngừng kinh doanh' } }}</span></td>
                            <td class="whitespace-nowrap text-right">
                                <a class="admin-link" href="{{ route('admin.products.edit', $p) }}">Sửa</a>
                                <form class="inline" method="post" action="{{ route('admin.products.destroy', $p) }}" onsubmit="return confirm('Chuyển sản phẩm này sang Ngừng kinh doanh?')">@csrf @method('DELETE')
                                    <button type="submit" class="admin-link-danger">{{ $p->is_active ? 'Ngừng bán' : 'Đã ngừng' }}</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $products->links() }}</div>
    </div>
    <script>
        (() => {
            const form = document.getElementById('bulk-product-form');
            const action = document.getElementById('bulk-action');
            const all = document.getElementById('select-all-products');
            const boxes = [...document.querySelectorAll('.product-checkbox')];
            const count = document.getElementById('selected-product-count');
            const submit = document.getElementById('bulk-submit');
            const fields = { category: document.getElementById('bulk-category'), price: document.getElementById('bulk-price'), stock: document.getElementById('bulk-stock'), percent: document.getElementById('bulk-percent'), quantity: document.getElementById('bulk-quantity') };
            const visibleFields = { category: ['category'], price: ['price'], stock: ['stock'], price_increase_percent: ['percent'], price_decrease_percent: ['percent'], stock_increase: ['quantity'], stock_decrease: ['quantity'] };
            const sync = () => { const selected = boxes.filter(box => box.checked).length; count.textContent = `${selected} sản phẩm được chọn`; submit.disabled = selected === 0 || !action.value; const visible = visibleFields[action.value] || []; Object.entries(fields).forEach(([key, field]) => field.classList.toggle('hidden', !visible.includes(key))); };
            all.addEventListener('change', () => { boxes.forEach(box => { box.checked = all.checked; }); sync(); });
            boxes.forEach(box => box.addEventListener('change', sync)); action.addEventListener('change', sync); sync();
            form.addEventListener('submit', event => { if (!boxes.some(box => box.checked)) { event.preventDefault(); return; } if (['deactivate', 'delete'].includes(action.value) && !confirm('Chuyển các sản phẩm đã chọn sang Ngừng kinh doanh?')) event.preventDefault(); });
        })();
    </script>
@endsection
