@extends('layouts.admin')
@section('title', 'Tạo khuyến mãi')
@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Tạo khuyến mãi sản phẩm</h1>
            </div>
        </div>

        <form method="post" action="{{ route('admin.promotions.store') }}" class="admin-card space-y-4">
            @csrf
            @include('admin.promotions.partials.form', ['promotion' => null])
            <button type="submit" class="admin-btn">Lưu khuyến mãi</button>
        </form>
    </div>
@endsection
