<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="antialiased">
        <div class="min-h-screen bg-[color:var(--bg-soft)]">
            @include('layouts.navigation')

            <!-- Page Heading -->
            @isset($header)
                <header class="border-b border-[color:var(--line-soft)] bg-[color:var(--surface)]">
                    <div class="mx-auto max-w-[1200px] px-4 py-7 sm:px-8 lg:px-10">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main class="mx-auto max-w-[1200px] px-4 py-8 sm:px-8 lg:px-10">
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
