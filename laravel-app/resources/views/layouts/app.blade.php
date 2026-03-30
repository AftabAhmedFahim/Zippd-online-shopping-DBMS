<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @if (request()->routeIs('profile.*'))
            @vite(['resources/css/dashboard.css'])
        @endif
        @stack('styles')
    </head>
    <body @class([
        'antialiased',
        'dashboard-theme text-[#171717]' => request()->routeIs('profile.*'),
        'font-sans' => !request()->routeIs('profile.*'),
    ])>
        <div @class([
            'min-h-screen',
            'bg-gray-100' => !request()->routeIs('profile.*'),
        ])>
            @unless (request()->routeIs('profile.*'))
                @include('layouts.navigation')
            @endunless

            <!-- Page Heading -->
            @isset($header)
                <header @class([
                    'bg-white',
                    'shadow' => !request()->routeIs('profile.*'),
                    'topbar-animate relative z-50 border-b border-black/10' => request()->routeIs('profile.*'),
                ])>
                    <div class="{{ request()->routeIs('profile.*')
                        ? 'w-full px-5 py-5 md:px-10 md:py-6 lg:px-14'
                        : 'mx-auto max-w-7xl py-6 px-4 sm:px-6 lg:px-8' }}">
                        {{ $header }}
                    </div>
                </header>
            @endisset

            <!-- Page Content -->
            <main>
                {{ $slot }}
            </main>
        </div>
    </body>
</html>
