@extends('layouts.admin')
@section('title', 'Danh mục')
@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Danh mục</h1>
                <p class="admin-page-lead">Nhóm sản phẩm hiển thị trên cửa hàng.</p>
            </div>
            <a href="{{ route('admin.categories.create') }}" class="admin-btn shrink-0">Thêm danh mục</a>
        </div>

        <div class="admin-table-wrap overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th class="w-20">Ảnh</th>
                        <th>Tên</th>
                        <th>Slug</th>
                        <th>Sáº£n pháº©m</th>
                        <th>Hoạt động</th>
                        <th class="w-40 text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($categories as $c)
                        <tr>
                            <td>
                                @if($c->image_path)
                                    <img src="{{ $c->imagePublicUrl() }}" alt="" class="h-12 w-16 rounded-md border border-slate-200 object-cover" />
                                @else
                                    <span class="text-xs text-slate-400">—</span>
                                @endif
                            </td>
                            <td class="font-medium text-slate-900">
                                {{ $c->parent ? 'â€” ' : '' }}{{ $c->name }}
                                @if($c->parent)
                                    <span class="mt-1 block text-xs font-normal text-slate-500">Thuá»™c {{ $c->parent->name }}</span>
                                @endif
                            </td>
                            <td class="font-mono text-xs text-slate-500">{{ $c->slug }}</td>
                            <td>
                                <button type="button" class="admin-link" data-category-toggle="{{ $c->id }}" aria-expanded="false">
                                    {{ $c->products_count }} sáº£n pháº©m
                                </button>
                            </td>
                            <td>{{ $c->is_active ? 'Có' : 'Không' }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.categories.edit', $c) }}" class="admin-link">Sửa</a>
                                <form class="ml-3 inline" method="post" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Xóa danh mục này?')">@csrf @method('DELETE')
                                    <button type="submit" class="admin-link-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                        <tr id="category-products-{{ $c->id }}" class="hidden bg-slate-50/70">
                            <td colspan="6" class="px-6 py-4">
                                <div class="space-y-2">
                                    <p class="text-sm font-semibold text-slate-900">Sản phẩm thuộc danh mục {{ $c->name }}</p>
                                    @forelse($c->products as $product)
                                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-lg border border-slate-200 bg-white px-3 py-2 text-sm">
                                            <div>
                                                <span class="font-medium text-slate-900">{{ $product->name }}</span>
                                                <span class="ml-2 font-mono text-xs text-slate-500">{{ $product->sku }}</span>
                                            </div>
                                            <div class="flex items-center gap-3">
                                                <span class="text-slate-600">{{ number_format($product->price, 0, ',', '.') }} đ</span>
                                                <span class="text-xs {{ $product->is_active ? 'text-emerald-600' : 'text-slate-400' }}">{{ $product->is_active ? 'Đang bán' : 'Đang ẩn' }}</span>
                                                <a href="{{ route('admin.products.edit', $product) }}" class="admin-link">Sửa</a>
                                            </div>
                                        </div>
                                    @empty
                                        <p class="text-sm text-slate-500">Danh mục này chưa có sản phẩm.</p>
                                    @endforelse
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $categories->links() }}</div>
    </div>
    <script>
        document.querySelectorAll('[data-category-toggle]').forEach((button) => {
            const count = button.textContent.match(/\d+/)?.[0] || '0';
            button.textContent = `${count} sản phẩm`;
            button.addEventListener('click', () => {
                const row = document.getElementById(`category-products-${button.dataset.categoryToggle}`);
                if (!row) return;
                const isHidden = row.classList.toggle('hidden');
                button.setAttribute('aria-expanded', isHidden ? 'false' : 'true');
            });
        });
        const productsHeader = document.querySelector('thead th:nth-child(4)');
        if (productsHeader) productsHeader.textContent = 'Sản phẩm';
    </script>
@endsection
