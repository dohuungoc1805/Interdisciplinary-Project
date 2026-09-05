<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="shop-body min-h-screen antialiased text-[color:var(--text-main)]">
    <div class="shop-blob shop-blob-a" aria-hidden="true"></div>
    <div class="shop-blob shop-blob-b" aria-hidden="true"></div>
    <div class="shop-blob shop-blob-c" aria-hidden="true"></div>
    @include('partials.shop-header')
    <main class="shop-main w-full flex-1">
        <div class="mx-auto w-full max-w-[1200px] px-5 pb-16 pt-6 sm:pt-8">
            @if(session('status'))
                <div class="mb-4 rounded-[var(--radius-ui)] border border-[color:var(--line-soft)] bg-[#f7f7f9] px-4 py-3 text-sm text-[color:var(--text-main)]">{{ session('status') }}</div>
            @endif
            @if(session('error'))
                <div class="mb-4 rounded-[var(--radius-ui)] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">{{ session('error') }}</div>
            @endif
            @if ($errors->any())
                <div class="mb-4 rounded-[var(--radius-ui)] border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800">
                    <ul class="list-inside list-disc">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                </div>
            @endif
            @yield('content')
        </div>
    </main>
    @include('partials.shop-footer')
    @include('partials.chat-widget')
</body>
</html>
