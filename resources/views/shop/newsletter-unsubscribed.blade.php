@extends('layouts.shop')

@section('title', 'Bản tin — '.config('app.name'))

@section('content')
    <div class="nd-shop-page-head">
        <h1 class="nd-shop-page-title">Đã hủy đăng ký</h1>
        <p class="nd-shop-page-lead">Bạn sẽ không còn nhận email khuyến mãi từ chúng tôi.</p>
    </div>
    <a href="{{ route('home') }}" class="inline-block text-sm font-semibold text-[color:var(--accent)] hover:underline">Về trang chủ</a>
@endsection
