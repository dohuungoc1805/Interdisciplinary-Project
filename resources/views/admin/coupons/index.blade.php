@extends('layouts.admin')
@section('title', 'Mã giảm giá')
@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Mã giảm giá</h1>
                <p class="admin-page-lead">Coupon áp dụng ở giỏ hàng / thanh toán.</p>
            </div>
            <a href="{{ route('admin.coupons.create') }}" class="admin-btn shrink-0">Thêm mã</a>
        </div>

        <div class="admin-table-wrap overflow-x-auto">
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nhóm voucher</th>
                        <th>Mã</th>
                        <th>Loại</th>
                        <th>Giá trị</th>
                        <th>Đã dùng</th>
                        <th>Kích hoạt</th>
                        <th class="text-right">Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($coupons as $c)
                        <tr>
                            <td>{{ $c->kindLabel() }}</td>
                            <td class="font-mono text-xs font-semibold">{{ $c->code }}</td>
                            <td>{{ $c->type }}</td>
                            <td>{{ $c->value }}</td>
                            <td>{{ $c->used_count }} / {{ $c->max_uses ?? '∞' }}</td>
                            <td>{{ $c->is_active ? 'Có' : 'Không' }}</td>
                            <td class="text-right">
                                <a href="{{ route('admin.coupons.edit', $c) }}" class="admin-link">Sửa</a>
                                <form class="ml-3 inline" method="post" action="{{ route('admin.coupons.destroy', $c) }}" onsubmit="return confirm('Xóa mã này?')">@csrf @method('DELETE')
                                    <button type="submit" class="admin-link-danger">Xóa</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="mt-6">{{ $coupons->links() }}</div>
    </div>
@endsection
