<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Checkout - Zippd</title>
    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/css/products.css', 'resources/js/app.js'])
</head>
<body class="products-shell text-[#171717] antialiased">
@php
    $currentUser = auth()->user();
    $displayName = trim((string) ($currentUser->full_name ?? 'User'));
    $avatarLetter = $displayName !== '' ? strtoupper(substr($displayName, 0, 1)) : 'U';
    $cartItemCount = (int) ($cartSummary['item_count'] ?? 0);
@endphp

<div class="min-h-screen">
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
                       class="sidebar-link flex items-center gap-2 rounded-xl bg-black px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white">
                        Products
                    </a>
                    <a href="{{ route('dashboard.orders') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-black/80 hover:bg-white">
                        Order Details
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
            @if(session('cart_error'))
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ session('cart_error') }}
                </div>
            @endif
            @if(session('cart_success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('cart_success') }}
                </div>
            @endif
            @if($errors->any())
                <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                    {{ $errors->first() }}
                </div>
            @endif

            <section class="catalog-header rounded-3xl px-7 py-7 md:px-9 md:py-8">
                <p class="font-roboto text-xs uppercase tracking-[0.26em] text-black/45">Checkout</p>
                <h1 class="catalog-heading font-mono text-[40px] leading-[0.94] tracking-[-0.02em]">Order Bill</h1>
                <p class="catalog-subtitle font-roboto mt-2 text-sm">
                    Review your items and confirm order. Cart items: {{ $cartItemCount }}.
                </p>
            </section>

            <section class="catalog-filters rounded-3xl p-6 md:p-7">
                <div class="overflow-x-auto">
                    <table class="min-w-full text-left text-sm">
                        <thead>
                        <tr class="border-b border-black/10 text-xs uppercase tracking-[0.2em] text-black/50">
                            <th class="px-2 py-3">Item</th>
                            <th class="px-2 py-3">Quantity</th>
                            <th class="px-2 py-3">Unit Price</th>
                            <th class="px-2 py-3">Line Total</th>
                            <th class="px-2 py-3 text-right">Action</th>
                        </tr>
                        </thead>
                        <tbody>
                        @foreach($cartDetails['items'] as $item)
                            <tr class="border-b border-black/5 align-top">
                                <td class="px-2 py-3">
                                    <p class="font-semibold text-black">{{ $item['product_name'] }}</p>
                                    <p class="mt-1 text-xs {{ $item['can_fulfill'] ? 'text-emerald-700' : 'text-rose-700' }}">
                                        {{ $item['can_fulfill'] ? 'In stock' : 'Insufficient stock' }}
                                        (Available: {{ $item['stock_qty'] }})
                                    </p>
                                </td>
                                <td class="px-2 py-3">{{ $item['quantity'] }}</td>
                                <td class="px-2 py-3">{{ $item['unit_price_formatted'] }}</td>
                                <td class="px-2 py-3">{{ $item['line_total_formatted'] }}</td>
                                <td class="px-2 py-3 text-right">
                                    <form method="POST" action="{{ route('cart.remove', ['productId' => $item['product_id']]) }}">
                                        @csrf
                                        <button type="submit"
                                                class="catalog-btn-secondary inline-flex items-center rounded-lg px-3 py-1.5 text-xs font-semibold transition hover:bg-[#f9f4e5]">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                        </tbody>
                    </table>
                </div>

                @if(!empty($cartDetails['issues']))
                    <div class="mt-5 rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-sm text-amber-800">
                        @foreach($cartDetails['issues'] as $issue)
                            <p>{{ $issue }}</p>
                        @endforeach
                    </div>
                @endif

                <div class="mt-6 flex items-center justify-between rounded-xl border border-black/10 bg-white/70 px-4 py-3">
                    <p class="text-sm text-black/60">Grand Total</p>
                    <p class="catalog-price font-mono text-2xl">{{ $cartDetails['total_amount_formatted'] }}</p>
                </div>

                <form method="POST" action="{{ route('checkout.confirm') }}" class="mt-6 space-y-4">
                    @csrf
                    <div>
                        <label for="shipping_address" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-black/50">
                            Shipping Address
                        </label>
                        <textarea id="shipping_address"
                                  name="shipping_address"
                                  rows="3"
                                  class="catalog-input w-full px-4 py-2.5 text-sm"
                                  required>{{ $defaultShippingAddress }}</textarea>
                    </div>

                    <div class="flex flex-wrap gap-3">
                        <button type="submit"
                                class="catalog-btn-primary rounded-xl px-5 py-2.5 text-sm font-semibold shadow-sm transition hover:brightness-110 disabled:cursor-not-allowed disabled:opacity-60"
                                @disabled(!$cartDetails['can_checkout'])>
                            Confirm Order
                        </button>
                        <a href="{{ route('products') }}"
                           class="catalog-btn-secondary inline-flex items-center rounded-xl px-5 py-2.5 text-sm font-semibold transition hover:bg-[#f9f4e5]">
                            Back to Products
                        </a>
                    </div>
                </form>
            </section>
        </div>
    </main>
</div>

@include('partials.mssql-console-debug')
</body>
</html>

