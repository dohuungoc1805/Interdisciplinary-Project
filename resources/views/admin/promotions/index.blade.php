@extends('layouts.admin')
@section('title', 'Khuyến mãi sản phẩm')
@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Khuyến mãi sản phẩm</h1>
                <p class="admin-page-lead">Quản lý chiến dịch giảm giá hàng loạt theo toàn bộ hoặc danh mục.</p>
            </div>
            <a href="{{ route('admin.promotions.create') }}" class="admin-btn shrink-0">Tạo khuyến mãi</a>
        </div>

        <div class="admin-table-wrap overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tên</th>
                        <th>Phạm vi</th>
                        <th>Giảm</th>
                        <th>Hiệu lực</th>
                        <th>Kích hoạt</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($promotions as $promotion)
                        <tr>
                            <td class="font-medium text-slate-900">{{ $promotion->name }}</td>
                            <td>{{ $promotion->category?->name ?: 'Toàn bộ sản phẩm' }}</td>
                            <td>
                                @if($promotion->type === 'percent')
                                    {{ rtrim(rtrim(number_format($promotion->value, 2, '.', ''), '0'), '.') }}%
                                @else
                                    {{ number_format($promotion->value, 0, ',', '.') }} ₫
                                @endif
                            </td>
                            <td>
                                {{ $promotion->starts_at?->format('d/m/Y H:i') ?: 'Ngay lập tức' }}
                                -
                                {{ $promotion->ends_at?->format('d/m/Y H:i') ?: 'Không giới hạn' }}
                            </td>
                            <td>{{ $promotion->is_active ? 'Có' : 'Không' }}</td>
                            <td class="text-right"><a href="{{ route('admin.promotions.edit', $promotion) }}" class="admin-link">Sửa</a></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-6">{{ $promotions->links() }}</div>
    </div>
@endsection
