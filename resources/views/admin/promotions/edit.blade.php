@extends('layouts.admin')
@section('title', 'Sửa khuyến mãi')
@section('content')
    <div class="admin-shell">
        <div class="admin-page-head">
            <div>
                <h1 class="admin-page-title">Sửa khuyến mãi</h1>
            </div>
        </div>

        <form method="post" action="{{ route('admin.promotions.update', $promotion) }}" class="admin-card space-y-4">
            @csrf
            @method('PUT')
            @include('admin.promotions.partials.form', ['promotion' => $promotion])
            <button type="submit" class="admin-btn">Cập nhật</button>
        </form>
    </div>
@endsection
