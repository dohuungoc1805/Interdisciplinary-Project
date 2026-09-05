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
                            <td class="font-medium text-slate-900">{{ $c->name }}</td>
                            <td class="font-mono text-xs text-slate-500">{{ $c->slug }}</td>
                            <td>{{ $c->is_active ? 'Có' : 'Không' }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.categories.edit', $c) }}" class="admin-link">Sửa</a>
                                <form class="ml-3 inline" method="post" action="{{ route('admin.categories.destroy', $c) }}" onsubmit="return confirm('Xóa danh mục này?')">@csrf @method('DELETE')
                                    <button type="submit" class="admin-link-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $categories->links() }}</div>
    </div>
@endsection
