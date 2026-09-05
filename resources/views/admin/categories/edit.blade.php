@extends('layouts.admin')
@section('title', 'Sửa danh mục')
@section('content')
    <div class="admin-shell max-w-lg">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Sửa danh mục</h1>
                <p class="admin-page-lead">{{ $category->name }}</p>
            </div>
            <a href="{{ route('admin.categories.index') }}" class="admin-btn-outline shrink-0">← Danh sách</a>
        </div>
        <div class="admin-card space-y-4">
            <form method="post" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data" class="space-y-4">@csrf @method('PUT')
                <div>
                    <label class="admin-label" for="name">Tên</label>
                    <input id="name" name="name" class="admin-input" required value="{{ old('name', $category->name) }}" />
                </div>
                <div>
                    <label class="admin-label" for="parent_id">Danh mục cha</label>
                    <select id="parent_id" name="parent_id" class="admin-input">
                        <option value="">— Không —</option>
                        @foreach($parents as $p)<option value="{{ $p->id }}" @selected($category->parent_id==$p->id)>{{ $p->name }}</option>@endforeach
                    </select>
                </div>
                <div>
                    <label class="admin-label" for="image">Ảnh danh mục</label>
                    @if($category->image_path)
                        <p class="mb-2 text-xs text-slate-500">Ảnh hiện tại:</p>
                        <img src="{{ $category->imagePublicUrl() }}" alt="" class="mb-3 h-24 w-40 max-w-full rounded-lg border border-slate-200 object-cover" />
                    @endif
                    <input id="image" name="image" type="file" accept="image/*" class="w-full text-xs file:mr-3 file:rounded-md file:border-0 file:bg-orange-50 file:px-3 file:py-1.5 file:text-xs file:font-medium file:text-orange-800 hover:file:bg-orange-100" />
                    <p class="mt-1 text-xs text-slate-500">Để trống nếu giữ nguyên ảnh.</p>
                </div>
                <div>
                    <label class="admin-label" for="position">Thứ tự</label>
                    <input id="position" name="position" type="number" class="admin-input" value="{{ old('position', $category->position) }}" />
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-700">
                    <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-orange-600 focus:ring-orange-500/30" @checked($category->is_active) /> Đang kích hoạt
                </label>
                <button class="admin-btn w-full sm:w-auto" type="submit">Cập nhật</button>
            </form>
        </div>
    </div>
@endsection
