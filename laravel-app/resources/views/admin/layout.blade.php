<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $pageTitle ?? 'Admin Panel - Zippd' }}</title>
    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="dashboard-theme text-[#171717] antialiased">
@php
    $displayName = $adminInfo['full_name'] ?? 'Admin';
    $trimmedName = trim($displayName);
    $avatarLetter = $trimmedName !== '' ? strtoupper(substr($trimmedName, 0, 1)) : 'A';
    $activeTab = $activeTab ?? 'dashboard';
    $contentContainerClass = $activeTab === 'orders'
        ? 'mx-auto max-w-[1700px] space-y-6'
        : 'mx-auto max-w-6xl space-y-6';
@endphp

<div class="min-h-screen">
    <header class="topbar-animate relative z-50 border-b border-black/10">
        <div class="w-full px-5 py-5 md:px-10 md:py-6 lg:px-14">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('admin.dashboard') }}" class="font-display text-2xl leading-none tracking-tight md:text-[30px]">
                    Zippd
                </a>

                <nav class="hidden flex-1 items-center justify-center gap-2 md:flex">
                    <a href="{{ route('admin.dashboard') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $activeTab === 'dashboard' ? 'bg-black text-white' : 'bg-white/70 text-black/80 hover:bg-white' }}">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg {{ $activeTab === 'dashboard' ? 'bg-white/15 text-white' : 'bg-indigo-100 text-indigo-700' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M9 21V9h6v12" />
                            </svg>
                        </span>
                        Dashboard
                    </a>
                    <a href="{{ route('admin.users') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $activeTab === 'users' ? 'bg-black text-white' : 'bg-white/70 text-black/80 hover:bg-white' }}">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg {{ $activeTab === 'users' ? 'bg-white/15 text-white' : 'bg-cyan-100 text-cyan-700' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0v-2a4 4 0 00-4-4H11a4 4 0 00-4 4v2m10 0H7m8-11a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                        </span>
                        Users
                    </a>
                    <a href="{{ route('admin.categories') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $activeTab === 'categories' ? 'bg-black text-white' : 'bg-white/70 text-black/80 hover:bg-white' }}">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg {{ $activeTab === 'categories' ? 'bg-white/15 text-white' : 'bg-emerald-100 text-emerald-700' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                            </svg>
                        </span>
                        Categories
                    </a>
                    <a href="{{ route('admin.products') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $activeTab === 'products' ? 'bg-black text-white' : 'bg-white/70 text-black/80 hover:bg-white' }}">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg {{ $activeTab === 'products' ? 'bg-white/15 text-white' : 'bg-sky-100 text-sky-700' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7L12 3 4 7m16 0v10l-8 4-8-4V7m16 0-8 4m-8-4 8 4m0 0v10" />
                            </svg>
                        </span>
                        Products
                    </a>
                    <a href="{{ route('admin.orders') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $activeTab === 'orders' ? 'bg-black text-white' : 'bg-white/70 text-black/80 hover:bg-white' }}">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg {{ $activeTab === 'orders' ? 'bg-white/15 text-white' : 'bg-rose-100 text-rose-700' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l2.5 12.5a2 2 0 002 1.5h8.5a2 2 0 001.95-1.55L22 7H8m2 14a1 1 0 100 2 1 1 0 000-2zm9 0a1 1 0 100 2 1 1 0 000-2z" />
                            </svg>
                        </span>
                        Orders
                    </a>
                    <a href="{{ route('admin.returns') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $activeTab === 'returns' ? 'bg-black text-white' : 'bg-white/70 text-black/80 hover:bg-white' }}">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg {{ $activeTab === 'returns' ? 'bg-white/15 text-white' : 'bg-amber-100 text-amber-700' }}">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h5M20 20v-5h-5M5 9a7 7 0 0112-2M19 15a7 7 0 01-12 2" />
                            </svg>
                        </span>
                        Returns
                    </a>
                </nav>

                <div class="flex items-center gap-3">
                    <span class="admin-tag hidden rounded-full border border-black/10 bg-white/70 px-3 py-1 text-[11px] font-semibold uppercase tracking-wide text-black/60 lg:inline-flex">
                        Admin Panel
                    </span>

                    <div class="relative" x-data="{ open: false }">
                        <button type="button"
                                class="avatar-trigger"
                                @click.stop="open = !open"
                                aria-label="Open profile menu">
                            <span class="avatar-inner">{{ $avatarLetter }}</span>
                        </button>

                        <div x-show="open"
                             x-transition
                             @click.outside="open = false"
                             class="profile-menu absolute right-0 z-[70] mt-2 w-52 rounded-lg border border-black/10 bg-white py-2 shadow-xl"
                             style="display: none;">
                            <p class="border-b border-black/10 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-black/50">
                                {{ $adminInfo['email'] ?? 'admin@zippd.local' }}
                            </p>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <button type="submit"
                                        class="block w-full px-4 py-2 text-left text-sm text-black/80 transition hover:bg-black/5">
                                    Logout
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <nav class="mt-4 flex gap-2 overflow-x-auto pb-1 md:hidden">
                <a href="{{ route('admin.dashboard') }}"
                   class="sidebar-link flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $activeTab === 'dashboard' ? 'bg-black text-white' : 'bg-white/70 text-black/80 hover:bg-white' }}">
                    Dashboard
                </a>
                <a href="{{ route('admin.users') }}"
                   class="sidebar-link flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $activeTab === 'users' ? 'bg-black text-white' : 'bg-white/70 text-black/80 hover:bg-white' }}">
                    Users
                </a>
                <a href="{{ route('admin.categories') }}"
                   class="sidebar-link flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $activeTab === 'categories' ? 'bg-black text-white' : 'bg-white/70 text-black/80 hover:bg-white' }}">
                    Categories
                </a>
                <a href="{{ route('admin.products') }}"
                   class="sidebar-link flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $activeTab === 'products' ? 'bg-black text-white' : 'bg-white/70 text-black/80 hover:bg-white' }}">
                    Products
                </a>
                <a href="{{ route('admin.orders') }}"
                   class="sidebar-link flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $activeTab === 'orders' ? 'bg-black text-white' : 'bg-white/70 text-black/80 hover:bg-white' }}">
                    Orders
                </a>
                <a href="{{ route('admin.returns') }}"
                   class="sidebar-link flex shrink-0 items-center gap-2 rounded-xl px-4 py-2 text-xs font-semibold uppercase tracking-wide {{ $activeTab === 'returns' ? 'bg-black text-white' : 'bg-white/70 text-black/80 hover:bg-white' }}">
                    Returns
                </a>
            </nav>
        </div>
    </header>

    <main class="dashboard-fade-up min-w-0 px-5 py-6 md:px-10 md:py-8">
        <div class="{{ $contentContainerClass }}">
            @yield('admin-content')
        </div>
    </main>
</div>

@include('partials.mssql-console-debug')
</body>
</html>
