<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Return Status - Zippd</title>
    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/css/products.css', 'resources/js/app.js'])
</head>
<body class="products-shell text-[#171717] antialiased">
@php
    $currentUser = auth()->user();
    $displayName = trim((string) ($currentUser->full_name ?? 'User'));
    $avatarLetter = $displayName !== '' ? strtoupper(substr($displayName, 0, 1)) : 'U';
    $cartItemCount = (int) ($cartSummary['item_count'] ?? 0);
    $returnStatus = strtolower((string) ($return['status'] ?? 'pending'));
    $returnStatusClass = match ($returnStatus) {
        'approved', 'confirmed', 'returned successfully' => 'catalog-stock-ok',
        'rejected', 'cancelled' => 'catalog-stock-out',
        default => 'catalog-stock-low',
    };
@endphp

<div class="min-h-screen" x-data="{ confirmCancelOpen: false }">
    <header class="topbar-animate relative z-50 border-b border-black/10">
        <div class="w-full px-5 py-5 md:px-10 md:py-6 lg:px-14">
            <div class="flex items-center justify-between gap-4">
                <a href="{{ route('dashboard') }}" class="font-display text-2xl leading-none tracking-tight md:text-[30px]">Zippd</a>

                <nav class="hidden flex-1 items-center justify-center gap-2 md:flex">
                    <a href="{{ route('dashboard') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-black/80 hover:bg-white">
                        Dashboard
                    </a>
                    <a href="{{ route('products') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-black/80 hover:bg-white">
                        Products
                    </a>
                    <a href="{{ route('dashboard.orders') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl bg-black px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white">
                        Return Status
                    </a>
                </nav>

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
        </div>
    </header>

    <main class="dashboard-fade-up min-w-0 px-5 py-8 md:px-10 md:py-10">
        <div class="mx-auto max-w-5xl space-y-6">
            <section class="catalog-header rounded-3xl px-7 py-7 md:px-9 md:py-8">
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-roboto text-xs uppercase tracking-[0.26em] text-black/45">Return Center</p>
                        <h1 class="catalog-heading font-mono text-[40px] leading-[0.94] tracking-[-0.02em]">Return Status</h1>
                        <p class="catalog-subtitle font-roboto mt-2 text-sm">
                            Cart currently has {{ $cartItemCount }} item{{ $cartItemCount === 1 ? '' : 's' }}.
                        </p>
                    </div>
                    <span class="inline-flex items-center rounded-full px-4 py-2 text-[1.4rem] font-bold uppercase tracking-[0.08em] md:text-[22px] {{ $returnStatusClass }}">
                        {{ ucfirst((string) $return['status']) }}
                    </span>
                </div>
            </section>

            <section class="catalog-filters rounded-[28px] p-6 md:p-8">
                <div class="catalog-return-hero grid gap-5 border-b border-black/10 pb-6 md:grid-cols-[1.2fr_220px] md:items-center">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-black/45">Product</p>
                        <h2 class="mt-2 font-mono text-3xl leading-tight text-black">{{ $return['product_name'] }}</h2>
                        <div class="mt-4 flex flex-wrap gap-3 text-sm text-black/70">
                            <span class="catalog-return-pill">{{ $return['return_code'] }}</span>
                            <span class="catalog-return-pill">Order #{{ $return['order_id'] }}</span>
                            <span class="catalog-return-pill">Qty {{ $return['quantity'] }}</span>
                        </div>
                    </div>
                    <div class="justify-self-end">
                        <img src="{{ asset($return['image_path']) }}"
                             alt="{{ $return['product_name'] }}"
                             class="catalog-image catalog-return-thumb w-full rounded-[26px] border border-black/10"
                             onerror="this.onerror=null;this.src='{{ asset('images/products/placeholder.svg') }}';">
                    </div>
                </div>

                <div class="mt-6 grid gap-4 md:grid-cols-2">
                    <div class="catalog-return-block">
                        <p class="catalog-return-label">Return Reason</p>
                        <p class="catalog-return-value">{{ $return['return_reason'] }}</p>
                    </div>
                    <div class="catalog-return-block">
                        <p class="catalog-return-label">Return To</p>
                        <div class="mt-2 flex items-center gap-3">
                            @if($return['refund_destination'])
                                <img src="{{ asset($return['refund_destination']['icon_path']) }}"
                                     alt="{{ $return['refund_destination']['label'] }}"
                                     class="h-10 w-10 rounded-xl border border-black/10 bg-white p-1.5">
                                <p class="catalog-return-value">{{ $return['refund_destination']['label'] }}</p>
                            @else
                                <p class="catalog-return-value">Not set</p>
                            @endif
                        </div>
                    </div>
                    <div class="catalog-return-block md:col-span-2">
                        <p class="catalog-return-label">Comments</p>
                        <p class="catalog-return-value whitespace-pre-line">{{ $return['comments'] }}</p>
                    </div>
                    <div class="catalog-return-block">
                        <p class="catalog-return-label">Return Date</p>
                        <p class="catalog-return-value">{{ \Carbon\Carbon::parse($return['return_date'])->format('M d, Y h:i A') }}</p>
                    </div>
                    <div class="catalog-return-block">
                        <p class="catalog-return-label">Return ID</p>
                        <p class="catalog-return-value">{{ $return['return_code'] }}</p>
                    </div>
                </div>

                <div class="mt-8 flex flex-col-reverse gap-3 border-t border-black/10 pt-6 sm:flex-row sm:justify-between">
                    <a href="{{ route('dashboard.orders') }}"
                       class="catalog-btn-secondary inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition hover:bg-[#f9f4e5]">
                        Back to Orders
                    </a>
                    <button type="button"
                            class="catalog-btn-primary inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition"
                            @click="confirmCancelOpen = true">
                        Cancel Return
                    </button>
                </div>
            </section>
        </div>
    </main>

    <div x-show="confirmCancelOpen"
         x-transition.opacity
         class="catalog-overlay"
         style="display: none;">
        <div class="catalog-overlay-backdrop" @click="confirmCancelOpen = false"></div>
        <div class="catalog-modal-card max-w-md">
            <p class="text-xs font-semibold uppercase tracking-[0.18em] text-black/45">Cancel Return</p>
            <h2 class="mt-2 font-mono text-3xl leading-tight text-black">Are you sure you want to cancel the return?</h2>
            <p class="mt-3 text-sm text-black/65">
                Pressing confirm will permanently remove this return request from the database.
            </p>

            <div class="mt-6 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button"
                        class="catalog-btn-secondary inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition hover:bg-[#f9f4e5]"
                        @click="confirmCancelOpen = false">
                    Cancel
                </button>
                <form method="POST" action="{{ route('returns.destroy', ['returnId' => $return['return_id']]) }}">
                    @csrf
                    @method('DELETE')
                    <button type="submit"
                            class="catalog-btn-primary inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition">
                        Confirm
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

@include('partials.mssql-console-debug')
</body>
</html>
