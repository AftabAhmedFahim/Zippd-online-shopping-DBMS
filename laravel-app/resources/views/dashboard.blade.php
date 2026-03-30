<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Dashboard - Zippd</title>
    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/js/app.js'])
</head>
<body class="dashboard-theme text-[#171717] antialiased">
@php
    $displayName = $userInfo['full_name'] ?? 'User';
    $trimmedName = trim($displayName);
    $avatarLetter = $trimmedName !== '' ? strtoupper(substr($trimmedName, 0, 1)) : 'U';
@endphp

<div class="min-h-screen">
    <header class="topbar-animate relative z-50 border-b border-black/10">
        <div class="w-full px-5 py-5 md:px-10 md:py-6 lg:px-14">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('dashboard') }}" class="font-display text-2xl leading-none tracking-tight md:text-[30px]">Zippd</a>

                <nav class="hidden flex-1 items-center justify-center gap-2 md:flex">
                    <a href="{{ route('dashboard') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl bg-black px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-white/15 text-white">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M9 21V9h6v12" />
                            </svg>
                        </span>
                        Dashboard
                    </a>
                    <a href="{{ route('products') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-black/80 hover:bg-white">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7L12 3 4 7m16 0v10l-8 4-8-4V7m16 0-8 4m-8-4 8 4m0 0v10" />
                            </svg>
                        </span>
                        Products
                    </a>
                    <a href="{{ route('dashboard.orders') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-black/80 hover:bg-white">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l2.5 12.5a2 2 0 002 1.5h8.5a2 2 0 001.95-1.55L22 7H8m2 14a1 1 0 100 2 1 1 0 000-2zm9 0a1 1 0 100 2 1 1 0 000-2z" />
                            </svg>
                        </span>
                        Order Details
                    </a>
                </nav>

                <div class="relative" x-data="{ open: false }">
                    <button type="button"
                            class="avatar-trigger"
                            @click.stop="open = !open"
                            aria-label="Open profile menu">
                        <span class="avatar-inner">
                            {{ $avatarLetter }}
                        </span>
                    </button>

                    <div x-show="open"
                         x-transition
                         @click.outside="open = false"
                         class="profile-menu absolute right-0 z-[70] mt-2 w-44 rounded-lg border border-black/10 bg-white py-2 shadow-xl"
                         style="display: none;">
                        <a href="{{ route('profile.edit') }}"
                           class="block px-4 py-2 text-sm text-black/80 transition hover:bg-black/5">
                            Edit profile
                        </a>
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

            <nav class="mt-4 flex gap-2 overflow-x-auto pb-1 md:hidden">
                <a href="{{ route('dashboard') }}"
                   class="sidebar-link flex shrink-0 items-center gap-2 rounded-xl bg-black px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-white/15 text-white">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M9 21V9h6v12" />
                        </svg>
                    </span>
                    Dashboard
                </a>
                <a href="{{ route('products') }}"
                   class="sidebar-link flex shrink-0 items-center gap-2 rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-black/80 hover:bg-white">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-emerald-100 text-emerald-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7L12 3 4 7m16 0v10l-8 4-8-4V7m16 0-8 4m-8-4 8 4m0 0v10" />
                        </svg>
                    </span>
                    Products
                </a>
                <a href="{{ route('dashboard.orders') }}"
                   class="sidebar-link flex shrink-0 items-center gap-2 rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-black/80 hover:bg-white">
                    <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-amber-100 text-amber-700">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l2.5 12.5a2 2 0 002 1.5h8.5a2 2 0 001.95-1.55L22 7H8m2 14a1 1 0 100 2 1 1 0 000-2zm9 0a1 1 0 100 2 1 1 0 000-2z" />
                        </svg>
                    </span>
                    Order Details
                </a>
            </nav>
        </div>
    </header>

    <main class="dashboard-fade-up min-w-0 px-5 py-6 md:px-10 md:py-8">
        <div class="mx-auto max-w-6xl space-y-6">
                <div>
                    <h1 class="font-mono text-[46px] leading-[0.95] tracking-[-0.02em] text-black">Dashboard</h1>
                    <p class="font-roboto mt-2 text-[15px] text-black/70">Welcome back. {{ $userInfo['full_name'] ?? 'User' }}!</p>
                </div>

                <div class="grid gap-6 lg:grid-cols-2">
                    <section class="dashboard-solid-card overflow-hidden rounded-2xl">
                        <div class="border-b border-black/10 px-6 py-5">
                            <h2 class="font-mono text-3xl leading-none tracking-tight">Profile Information</h2>
                            <p class="font-roboto mt-2 text-sm text-black/60">Core details</p>
                        </div>
                        <div class="space-y-3 px-6 py-6">
                            <div class="info-row flex items-start gap-4 p-4">
                                <div class="rounded-xl border border-sky-200 bg-sky-100 p-3 text-sky-700 shadow-sm">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A11.956 11.956 0 0112 16c2.4 0 4.635.707 6.515 1.922M15 10a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-roboto text-sm text-black/60">Full Name</p>
                                    <p class="font-roboto text-base font-medium">{{ $userInfo['full_name'] ?? 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="info-row flex items-start gap-4 p-4">
                                <div class="rounded-xl border border-violet-200 bg-violet-100 p-3 text-violet-700 shadow-sm">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16v12H4zM22 6l-10 7L2 6" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-roboto text-sm text-black/60">Email Address</p>
                                    <p class="font-roboto text-base font-medium">{{ $userInfo['email'] ?? 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="info-row flex items-start gap-4 p-4">
                                <div class="rounded-xl border border-emerald-200 bg-emerald-100 p-3 text-emerald-700 shadow-sm">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h4l2 5-2 2a16 16 0 007 7l2-2 5 2v4a2 2 0 01-2 2h-1C9.716 25 0 15.284 0 3V2a2 2 0 012-2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-roboto text-sm text-black/60">Phone Number</p>
                                    <p class="font-roboto text-base font-medium">{{ $userInfo['phone'] ?? 'Not provided' }}</p>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="dashboard-solid-card overflow-hidden rounded-2xl">
                        <div class="border-b border-black/10 px-6 py-5">
                            <h2 class="font-mono text-3xl leading-none tracking-tight">More Details</h2>
                            <p class="font-roboto mt-2 text-sm text-black/60">Additional informations</p>
                        </div>
                        <div class="space-y-3 px-6 py-6">
                            <div class="info-row flex items-start gap-4 p-4">
                                <div class="rounded-xl border border-rose-200 bg-rose-100 p-3 text-rose-700 shadow-sm">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a5 5 0 00-5 5v1H6a2 2 0 00-2 2v8a2 2 0 002 2h12a2 2 0 002-2v-8a2 2 0 00-2-2h-1V7a5 5 0 00-5-5z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-roboto text-sm text-black/60">Gender</p>
                                    <p class="font-roboto text-base font-medium">{{ $userInfo['gender'] ?? 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="info-row flex items-start gap-4 p-4">
                                <div class="rounded-xl border border-amber-200 bg-amber-100 p-3 text-amber-700 shadow-sm">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 21s-6-5.373-6-10a6 6 0 1112 0c0 4.627-6 10-6 10z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-roboto text-sm text-black/60">Address</p>
                                    <p class="font-roboto text-base font-medium">{{ $userInfo['address'] ?? 'Not provided' }}</p>
                                </div>
                            </div>
                            <div class="info-row flex items-start gap-4 p-4">
                                <div class="rounded-xl border border-indigo-200 bg-indigo-100 p-3 text-indigo-700 shadow-sm">
                                    <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10m-11 9h12a2 2 0 002-2V7a2 2 0 00-2-2H6a2 2 0 00-2 2v11a2 2 0 002 2z" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-roboto text-sm text-black/60">Member Since</p>
                                    <p class="font-roboto text-base font-medium">
                                        {{ !empty($userInfo['created_at']) ? \Carbon\Carbon::parse($userInfo['created_at'])->format('M d, Y') : 'Not available' }}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
    </main>
</div>

@include('partials.mssql-console-debug')
</body>
</html>
