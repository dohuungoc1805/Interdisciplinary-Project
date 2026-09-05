@extends('layouts.shop')

@section('title', 'Tài khoản — '.config('app.name'))

@section('content')
    <div class="nd-shop-page-head">
        <h1 class="nd-shop-page-title">Tài khoản</h1>
        <p class="nd-shop-page-lead">Cập nhật thông tin cá nhân, mật khẩu và quản lý tài khoản của bạn.</p>
    </div>

    <div class="mx-auto max-w-2xl space-y-8">
        @include('profile.partials.update-profile-information-form')
        @include('profile.partials.update-password-form')
        @include('profile.partials.delete-user-form')
    </div>
@endsection
