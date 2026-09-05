<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Be+Vietnam+Pro:wght@400;500;600;700;800&family=Playfair+Display:wght@500;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" crossorigin="anonymous" referrerpolicy="no-referrer">
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen antialiased text-white" style="font-family: 'Be Vietnam Pro', sans-serif;">
        @include('partials.shop-header')

        <main class="relative min-h-screen overflow-hidden">
            <img
                src="https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=2200&q=80"
                alt="Fashion background"
                class="absolute inset-0 h-full w-full object-cover"
            >
            <div class="absolute inset-0 bg-[linear-gradient(108deg,rgba(5,8,12,0.84)_8%,rgba(12,20,30,0.56)_46%,rgba(9,14,22,0.72)_100%)]"></div>

            <div class="relative z-10 mx-auto flex min-h-screen w-full max-w-[1400px] items-center px-7 py-10 lg:px-14">
                <div class="hidden w-full max-w-[620px] pr-12 lg:block">
                    <x-shop-brand-logo class="mb-16 !text-white [&_.nd-logo-mark]:!h-16 [&_.nd-logo-mark]:!w-16 [&_.nd-logo-mark]:!border-white/45 [&_.nd-logo-mark]:!bg-white/10 [&_.nd-logo-mark]:!text-white [&_.nd-logo-line1]:!text-white [&_.nd-logo-line2]:!text-white/80" />

                    <p class="mb-5 text-[1.95rem] font-extrabold uppercase tracking-wide text-[#57d8ce]">Spring Studio 2026</p>
                    <h1 class="max-w-[560px] text-[clamp(3.25rem,7vw,6.1rem)] leading-[0.92] text-white" style="font-family: 'Playfair Display', serif;">Phong cách mới, vừa vặn với bạn.</h1>
                    <p class="mt-7 max-w-[560px] text-[1.02rem] leading-[1.65] text-white/88">Khám phá các thiết kế ready-to-wear, phụ kiện và ưu đãi dành riêng cho thành viên.</p>
                </div>

                <section class="ml-auto w-full max-w-[560px]">
                    <x-shop-brand-logo class="mb-6 lg:hidden !text-white [&_.nd-logo-mark]:!border-white/45 [&_.nd-logo-mark]:!bg-white/10 [&_.nd-logo-mark]:!text-white [&_.nd-logo-line1]:!text-white [&_.nd-logo-line2]:!text-white/80" />
                    {{ $slot }}
                </section>
            </div>
        </main>

        @include('partials.shop-footer')
        @include('partials.chat-widget')
    </body>
</html>
