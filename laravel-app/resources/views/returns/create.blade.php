<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Request Return - Zippd</title>
    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/css/products.css', 'resources/js/app.js'])
</head>
<body class="products-shell text-[#171717] antialiased">
@php
    $currentUser = auth()->user();
    $displayName = trim((string) ($currentUser->full_name ?? 'User'));
    $avatarLetter = $displayName !== '' ? strtoupper(substr($displayName, 0, 1)) : 'U';
    $cartItemCount = (int) ($cartSummary['item_count'] ?? 0);
    $refundOptions = array_map(static fn (array $destination): array => [
        'value' => $destination['value'],
        'label' => $destination['label'],
        'icon' => asset($destination['icon_path']),
    ], $refundDestinations);
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
                        Returns
                    </a>
                </nav>

                <div class="relative" x-data="{ open: false }">
                    <button type="button"
                            class="avatar-trigger"
                            x-on:click.stop="open = !open"
                            aria-label="Open profile menu">
                        <span class="avatar-inner">{{ $avatarLetter }}</span>
                    </button>

                    <div x-show="open"
                         x-transition
                         x-on:click.outside="open = false"
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
                <p class="font-roboto text-xs uppercase tracking-[0.26em] text-black/45">Return Center</p>
                <h1 class="catalog-heading font-mono text-[40px] leading-[0.94] tracking-[-0.02em]">Request a Return</h1>
                <p class="catalog-subtitle font-roboto mt-2 text-sm">
                    Cart currently has {{ $cartItemCount }} item{{ $cartItemCount === 1 ? '' : 's' }}.
                </p>
            </section>

            @if ($errors->any())
                <section class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
                    Please review the form and fix the highlighted fields before submitting your return request.
                </section>
            @endif

            <form method="POST"
                  action="{{ route('returns.store', ['orderId' => $item['order_id'], 'productId' => $item['product_id']]) }}"
                  class="catalog-filters rounded-[28px] p-6 md:p-8"
                  x-data="returnForm()">
                @csrf

                <div class="catalog-return-hero grid gap-5 border-b border-black/10 pb-6 md:grid-cols-[1.2fr_220px] md:items-center">
                    <div>
                        <p class="text-xs uppercase tracking-[0.18em] text-black/45">Product</p>
                        <h2 class="mt-2 font-mono text-3xl leading-tight text-black">{{ $item['product_name'] }}</h2>
                        <div class="mt-4 flex flex-wrap gap-3 text-sm text-black/70">
                            <span class="catalog-return-pill">Order #{{ $item['order_id'] }}</span>
                            <span class="catalog-return-pill">Qty {{ $item['quantity'] }}</span>
                        </div>
                    </div>
                    <div class="justify-self-end">
                        <img src="{{ asset($item['image_path']) }}"
                             alt="{{ $item['product_name'] }}"
                             class="catalog-image catalog-return-thumb w-full rounded-[26px] border border-black/10"
                             onerror="this.onerror=null;this.src='{{ asset('images/products/placeholder.svg') }}';">
                    </div>
                </div>

                <div class="mt-6 grid gap-6">
                    <div>
                        <label for="return_reason" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-black/55">
                            Return Reasons
                        </label>
                        <select id="return_reason"
                                name="return_reason"
                                class="catalog-select w-full px-4 py-3 text-sm"
                                x-model="returnReason">
                            <option value="">Select a reason</option>
                            @foreach($returnReasons as $reason)
                                <option value="{{ $reason }}" @selected(old('return_reason') === $reason)>{{ $reason }}</option>
                            @endforeach
                        </select>
                        @error('return_reason')
                            <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="comments" class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-black/55">
                            Comments
                        </label>
                        <textarea id="comments"
                                  name="comments"
                                  rows="5"
                                  class="catalog-input w-full px-4 py-3 text-sm"
                                  placeholder="Tell us a little more about the issue so the team can review it quickly."
                                  x-model="comments">{{ old('comments') }}</textarea>
                        @error('comments')
                            <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="relative">
                        <label class="mb-2 block text-xs font-semibold uppercase tracking-[0.18em] text-black/55">
                            Return To
                        </label>
                        <input type="hidden" name="refund_to" x-model="refundTo">
                        <button type="button"
                                class="catalog-select flex w-full items-center justify-between gap-3 px-4 py-3 text-left text-sm"
                                x-on:click="refundMenuOpen = !refundMenuOpen"
                                x-on:click.outside="refundMenuOpen = false">
                            <span class="flex min-w-0 items-center gap-3">
                                <template x-if="selectedRefundOption()">
                                    <img :src="selectedRefundOption().icon" :alt="selectedRefundOption().label" class="h-8 w-8 rounded-lg border border-black/10 bg-white p-1">
                                </template>
                                <span x-text="selectedRefundOption() ? selectedRefundOption().label : 'Select refund destination'"></span>
                            </span>
                            <span class="text-black/45">▼</span>
                        </button>

                        <div x-show="refundMenuOpen"
                             x-transition
                             class="absolute z-20 mt-2 w-full rounded-[24px] border border-black/10 bg-[#fffef9] p-2 shadow-[0_20px_32px_-30px_rgba(17,24,39,0.42)]"
                             style="display: none;">
                            @foreach($refundDestinations as $destination)
                                <button type="button"
                                        class="catalog-return-option flex w-full items-center gap-3 rounded-2xl px-3 py-3 text-left transition hover:bg-black/5"
                                        x-on:click="refundTo = @js($destination['value']); refundMenuOpen = false">
                                    <img src="{{ asset($destination['icon_path']) }}"
                                         alt="{{ $destination['label'] }}"
                                         class="h-10 w-10 rounded-xl border border-black/10 bg-white p-1.5">
                                    <span class="font-medium text-black">{{ $destination['label'] }}</span>
                                </button>
                            @endforeach
                        </div>
                        @error('refund_to')
                            <p class="mt-2 text-sm text-rose-700">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="mt-8 flex flex-col-reverse gap-3 border-t border-black/10 pt-6 sm:flex-row sm:justify-end">
                    <a href="{{ route('dashboard.orders') }}"
                       class="catalog-btn-secondary inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition hover:bg-[#f9f4e5]">
                        Cancel
                    </a>
                    <button type="submit"
                            class="catalog-btn-primary catalog-return-confirm inline-flex items-center justify-center rounded-xl px-5 py-3 text-sm font-semibold transition"
                            x-bind:disabled="!canSubmit">
                        Confirm
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    const refundOptionsData = @js($refundOptions);
    const oldReturnReason = @js(old('return_reason', ''));
    const oldComments = @js(old('comments', ''));
    const oldRefundTo = @js(old('refund_to', ''));
    
    function returnForm() {
        return {
            returnReason: oldReturnReason,
            comments: oldComments,
            refundTo: oldRefundTo,
            refundMenuOpen: false,
            refundOptions: refundOptionsData,
            get canSubmit() {
                return this.returnReason !== '' && this.comments.trim() !== '' && this.refundTo !== '';
            },
            selectedRefundOption() {
                return this.refundOptions.find((option) => option.value === this.refundTo) || null;
            },
        };
    }
</script>

@include('partials.mssql-console-debug')
</body>
</html>
