<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Order Details - Zippd</title>
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
                       class="sidebar-link flex items-center gap-2 rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-black/80 hover:bg-white">
                        Products
                    </a>
                    <a href="{{ route('dashboard.orders') }}"
                       class="sidebar-link flex items-center gap-2 rounded-xl bg-black px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white">
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
        <div class="mx-auto max-w-6xl space-y-6">
            @if(session('order_success'))
                <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                    {{ session('order_success') }}
                </div>
            @endif

            <section class="catalog-header rounded-3xl px-7 py-7 md:px-9 md:py-8">
                <p class="font-roboto text-xs uppercase tracking-[0.26em] text-black/45">Order Section</p>
                <h1 class="catalog-heading font-mono text-[40px] leading-[0.94] tracking-[-0.02em]">Order Details</h1>
                <p class="catalog-subtitle font-roboto mt-2 text-sm">
                    Cart currently has {{ $cartItemCount }} item{{ $cartItemCount === 1 ? '' : 's' }}.
                </p>
            </section>

            @if($orders === [])
                <section class="catalog-empty rounded-2xl p-8 text-center">
                    <p class="font-mono text-2xl text-black">No orders yet</p>
                    <p class="mx-auto mt-2 max-w-xl text-sm text-black/65">
                        Once you confirm checkout, order id, status, and all items will appear here.
                    </p>
                    <a href="{{ route('products') }}"
                       class="catalog-btn-secondary mt-5 inline-flex rounded-xl px-4 py-2.5 text-sm font-semibold transition hover:bg-[#f9f4e5]">
                        Browse products
                    </a>
                </section>
            @else
                @foreach($orders as $order)
                    @php
                        $status = strtolower((string) $order['order_status']);
                        $statusClass = match ($status) {
                            'delivered' => 'catalog-stock-ok',
                            'shipped', 'in_transit' => 'catalog-stock-low',
                            'cancelled', 'failed' => 'catalog-stock-out',
                            default => 'catalog-stock-low',
                        };
                    @endphp
                    <section class="catalog-filters rounded-2xl p-6 md:p-7">
                        <div class="flex flex-wrap items-center justify-between gap-3 border-b border-black/10 pb-4">
                            <div>
                                <p class="text-xs uppercase tracking-[0.2em] text-black/50">Order ID</p>
                                <p class="font-mono text-2xl text-black">#{{ $order['order_id'] }}</p>
                            </div>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold {{ $statusClass }}">
                                {{ ucfirst((string) $order['order_status']) }}
                            </span>
                        </div>

                        <div class="mt-4 grid gap-4 text-sm md:grid-cols-3">
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-black/45">Order Date</p>
                                <p class="mt-1 font-medium text-black">
                                    {{ \Carbon\Carbon::parse($order['order_date'])->format('M d, Y h:i A') }}
                                </p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-black/45">Shipping Address</p>
                                <p class="mt-1 font-medium text-black">{{ $order['shipping_address'] }}</p>
                            </div>
                            <div>
                                <p class="text-xs uppercase tracking-[0.18em] text-black/45">Total Amount</p>
                                <p class="mt-1 font-mono text-xl text-black">{{ $order['total_amount_formatted'] }}</p>
                            </div>
                        </div>

                        <div class="mt-5 overflow-x-auto">
                            <table class="min-w-full text-left text-sm">
                                <thead>
                                <tr class="border-b border-black/10 text-xs uppercase tracking-[0.2em] text-black/50">
                                    <th class="px-2 py-3">Product</th>
                                    <th class="px-2 py-3">Quantity</th>
                                    <th class="px-2 py-3">Unit Price</th>
                                    <th class="px-2 py-3">Line Total</th>
                                </tr>
                                </thead>
                                <tbody>
                                @foreach($order['items'] as $item)
                                    <tr class="border-b border-black/5">
                                        <td class="px-2 py-3 font-semibold text-black">{{ $item['product_name'] }}</td>
                                        <td class="px-2 py-3">{{ $item['quantity'] }}</td>
                                        <td class="px-2 py-3">{{ $item['unit_price_formatted'] }}</td>
                                        <td class="px-2 py-3">{{ $item['line_total_formatted'] }}</td>
                                    </tr>
                                @endforeach
                                </tbody>
                            </table>
                        </div>
                    </section>
                @endforeach
            @endif
        </div>
    </main>
</div>

@include('partials.mssql-console-debug')
</body>
</html>
