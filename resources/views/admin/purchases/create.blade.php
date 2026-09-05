@extends('layouts.admin')
@section('title', 'Tạo phiếu nhập')
@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Tạo phiếu nhập</h1>
                <p class="admin-page-lead">Nhập hàng để cập nhật tồn kho và giá vốn.</p>
            </div>
        </div>

        <form method="post" action="{{ route('admin.purchases.store') }}" class="admin-card space-y-4">
            @csrf
            <div class="grid gap-4 md:grid-cols-2">
                <div>
                    <label class="admin-label" for="received_at">Ngày nhập</label>
                    <input id="received_at" type="date" name="received_at" class="admin-input" value="{{ old('received_at', now()->toDateString()) }}" required />
                </div>
                <div>
                    <label class="admin-label" for="supplier_name">Nhà cung cấp</label>
                    <input id="supplier_name" name="supplier_name" class="admin-input" value="{{ old('supplier_name') }}" />
                </div>
            </div>

            <div>
                <label class="admin-label" for="note">Ghi chú</label>
                <textarea id="note" name="note" rows="3" class="admin-input">{{ old('note') }}</textarea>
            </div>

            <div>
                <h2 class="mb-3 text-sm font-bold text-slate-800">Danh sách nhập</h2>
                <div id="items-wrap" class="space-y-3">
                    @for($i = 0; $i < 3; $i++)
                        <div class="grid gap-3 rounded-xl border border-slate-200 p-3 md:grid-cols-3">
                            <div>
                                <label class="admin-label">Biến thể sản phẩm</label>
                                <select name="items[{{ $i }}][product_variant_id]" class="admin-input" required>
                                    <option value="">Chọn biến thể</option>
                                    @foreach($variants as $variant)
                                        <option value="{{ $variant->id }}" @selected(old("items.$i.product_variant_id") == $variant->id)>
                                            #{{ $variant->id }} - {{ $variant->product?->name }} ({{ $variant->size }}/{{ $variant->color }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="admin-label">Số lượng</label>
                                <input type="number" min="1" name="items[{{ $i }}][quantity]" class="admin-input" value="{{ old("items.$i.quantity", 1) }}" required />
                            </div>
                            <div>
                                <label class="admin-label">Giá nhập / đơn vị</label>
                                <input type="number" min="0" step="0.01" name="items[{{ $i }}][unit_cost]" class="admin-input" value="{{ old("items.$i.unit_cost", 0) }}" required />
                            </div>
                        </div>
                    @endfor
                </div>
            </div>

            <div class="flex items-center gap-3">
                <button type="submit" class="admin-btn">Lưu phiếu nhập</button>
                <a href="{{ route('admin.purchases.index') }}" class="admin-btn-outline">Hủy</a>
            </div>
        </form>
    </div>
@endsection
