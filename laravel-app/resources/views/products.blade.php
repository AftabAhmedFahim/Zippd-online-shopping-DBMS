<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Products - Zippd</title>
    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/css/products.css', 'resources/js/app.js'])
</head>
<body class="products-shell text-[#171717] antialiased">
@php
    $currentUser = auth()->user();
    $displayName = trim((string) ($currentUser->full_name ?? 'User'));
    $avatarLetter = $displayName !== '' ? strtoupper(substr($displayName, 0, 1)) : 'U';
    $cartItemCount = (int) ($cartSummary['item_count'] ?? 0);
    $cartUniqueCount = (int) ($cartSummary['unique_count'] ?? 0);
@endphp

<div x-data='productCatalogPage(@json([
    "initialCartItemCount" => $cartItemCount,
    "initialCartUniqueCount" => $cartUniqueCount,
]))' class="min-h-screen">
    <div class="catalog-content" :class="{ 'catalog-content--blurred': selectedProduct !== null }">
        <header class="topbar-animate relative z-50 border-b border-black/10">
            <div class="w-full px-5 py-5 md:px-10 md:py-6 lg:px-14">
                <div class="flex items-center justify-between gap-4">
                    <a href="{{ route('dashboard') }}" class="font-display text-2xl leading-none tracking-tight md:text-[30px]">Zippd</a>

                    <nav class="hidden flex-1 items-center justify-center gap-2 md:flex">
                        <a href="{{ route('dashboard') }}"
                           class="sidebar-link flex items-center gap-2 rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-black/80 hover:bg-white">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M9 21V9h6v12" />
                                </svg>
                            </span>
                            Dashboard
                        </a>
                        <a href="{{ route('products') }}"
                           class="sidebar-link flex items-center gap-2 rounded-xl bg-black px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white">
                            <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-white/15 text-white">
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

                <nav class="mt-4 flex gap-2 overflow-x-auto pb-1 md:hidden">
                    <a href="{{ route('dashboard') }}"
                       class="sidebar-link flex shrink-0 items-center gap-2 rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-black/80 hover:bg-white">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-zinc-100 text-zinc-700">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l9-9 9 9M9 21V9h6v12" />
                            </svg>
                        </span>
                        Dashboard
                    </a>
                    <a href="{{ route('products') }}"
                       class="sidebar-link flex shrink-0 items-center gap-2 rounded-xl bg-black px-4 py-2 text-xs font-semibold uppercase tracking-wide text-white">
                        <span class="inline-flex h-6 w-6 items-center justify-center rounded-lg bg-white/15 text-white">
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

        <main class="dashboard-fade-up min-w-0 px-5 py-8 md:px-10 md:py-10">
            <div class="mx-auto max-w-7xl space-y-8 md:space-y-10">
                @if(session('cart_success'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('cart_success') }}
                    </div>
                @endif
                @if(session('cart_error'))
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ session('cart_error') }}
                    </div>
                @endif
                @if(session('review_success'))
                    <div class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                        {{ session('review_success') }}
                    </div>
                @endif
                @if(session('review_error'))
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ session('review_error') }}
                    </div>
                @endif
                @if($errors->has('rating') || $errors->has('review_text'))
                    <div class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                        {{ $errors->first('rating') ?? $errors->first('review_text') }}
                    </div>
                @endif
                <nav aria-label="Breadcrumb" class="catalog-breadcrumb catalog-enter catalog-enter-1">
                    <ol class="flex flex-wrap items-center gap-2 text-sm">
                        <li>
                            <a href="{{ route('dashboard') }}" class="catalog-breadcrumb-link">Dashboard</a>
                        </li>
                        <li class="catalog-breadcrumb-sep">></li>
                        <li class="catalog-breadcrumb-link">Categories</li>
                        <li class="catalog-breadcrumb-sep">></li>
                        <li aria-current="page" class="catalog-breadcrumb-current">Products</li>
                    </ol>
                </nav>

                <section class="catalog-header catalog-enter catalog-enter-2 rounded-3xl px-7 py-7 md:px-9 md:py-8">
                    <div>
                        <p class="font-roboto text-xs uppercase tracking-[0.26em] text-black/45">User Catalog</p>
                        <h1 class="catalog-heading font-mono text-[44px] leading-[0.94] tracking-[-0.02em]">Browse Components</h1>
                        <p class="catalog-subtitle font-roboto mt-2 text-sm">
                            Build faster systems with trusted hardware.
                        </p>
                    </div>
                </section>

                <section class="catalog-filters catalog-enter catalog-enter-3 rounded-3xl p-6 md:p-7">
                    <form action="{{ route('products') }}" method="GET" class="grid gap-5 md:grid-cols-2 lg:grid-cols-4">
                        <div class="lg:col-span-2">
                            <label for="search" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-black/50">Search</label>
                            <input type="text"
                                   id="search"
                                   name="search"
                                   value="{{ $searchTerm }}"
                                   placeholder="Search product name..."
                                   class="catalog-input w-full px-4 py-2.5 text-sm" />
                        </div>

                        <div>
                            <label for="category" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-black/50">Category</label>
                            <select id="category" name="category" class="catalog-select w-full px-4 py-2.5 text-sm">
                                <option value="">All Categories</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category['category_id'] }}" @selected((string) $selectedCategory === (string) $category['category_id'])>
                                        {{ $category['category_name'] }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div>
                            <label for="sort" class="mb-2 block text-xs font-semibold uppercase tracking-[0.2em] text-black/50">Sort</label>
                            <select id="sort" name="sort" class="catalog-select w-full px-4 py-2.5 text-sm">
                                <option value="price_asc" @selected($selectedSort === 'price_asc')>Price: Low to High</option>
                                <option value="price_desc" @selected($selectedSort === 'price_desc')>Price: High to Low</option>
                            </select>
                            <a href="{{ route('checkout.show') }}"
                               class="catalog-checkout-btn mt-3 inline-flex w-full items-center justify-center rounded-xl px-4 py-3 text-sm font-semibold tracking-[0.08em] uppercase transition hover:brightness-110">
                                <span>Checkout</span>
                                <span class="catalog-checkout-count"
                                     x-text="cartItemCount">
                                    {{ $cartItemCount }}
                                </span>
                            </a>
                            <p class="mt-2 text-xs text-black/50"
                               x-text="`${cartUniqueCount} unique product${cartUniqueCount === 1 ? '' : 's'} in cart`">
                                {{ $cartUniqueCount }} unique products in cart
                            </p>
                        </div>

                        <div class="flex gap-2 md:col-span-2 lg:col-span-4">
                            <button type="submit"
                                    class="catalog-btn-primary rounded-xl px-4 py-2.5 text-sm font-semibold shadow-sm transition hover:brightness-110">
                                Apply Filters
                            </button>
                            <a href="{{ route('products') }}"
                               class="catalog-btn-secondary inline-flex items-center rounded-xl px-4 py-2.5 text-sm font-semibold transition hover:bg-[#f9f4e5]">
                                Reset
                            </a>
                        </div>
                    </form>
                </section>

                <section class="space-y-5 catalog-enter catalog-enter-4">
                    @if($products->count() > 0)
                        <div class="grid gap-7 sm:grid-cols-2 xl:grid-cols-3">
                            @foreach($products as $product)
                                @php
                                    $stockQty = (int) ($product['stock_qty'] ?? 0);
                                    $averageRating = (float) ($product['average_rating'] ?? 0);
                                    $reviewCount = (int) ($product['review_count'] ?? 0);
                                    $stockLabel = $stockQty === 0
                                        ? 'Out of stock'
                                        : ($stockQty <= 10 ? 'Low stock: ' . $stockQty : 'In stock: ' . $stockQty);
                                    $productPayload = [
                                        'id' => (int) $product['product_id'],
                                        'name' => (string) $product['product_name'],
                                        'description' => (string) ($product['description'] ?: 'No description available for this product yet.'),
                                        'priceFormatted' => 'BDT ' . number_format((float) $product['price'], 2),
                                        'image' => asset($product['image_path']),
                                        'categories' => $product['categories'],
                                        'stockLabel' => $stockLabel,
                                        'stockClass' => $stockQty === 0
                                            ? 'catalog-stock-out'
                                            : ($stockQty <= 10 ? 'catalog-stock-low' : 'catalog-stock-ok'),
                                        'averageRating' => round($averageRating, 2),
                                        'reviewCount' => $reviewCount,
                                        'userRating' => isset($product['user_rating']) ? (int) $product['user_rating'] : null,
                                        'userReviewText' => (string) ($product['user_review_text'] ?? ''),
                                        'reviewAction' => route('products.reviews.store', ['productId' => (int) $product['product_id']]),
                                        'reviewsUrl' => route('products.reviews.index', ['productId' => (int) $product['product_id']]),
                                    ];
                                @endphp
                                <article
                                    class="catalog-card catalog-card-clickable overflow-hidden rounded-2xl"
                                    role="button"
                                    tabindex="0"
                                    x-on:click='openProduct(@json($productPayload))'
                                    x-on:keydown.enter.prevent='openProduct(@json($productPayload))'
                                    x-on:keydown.space.prevent='openProduct(@json($productPayload))'
                                    :class="{ 'catalog-card-active': selectedProduct && selectedProduct.id === {{ (int) $product['product_id'] }} }"
                                >
                                    <img src="{{ asset($product['image_path']) }}"
                                         alt="{{ $product['product_name'] }}"
                                         loading="lazy"
                                         class="catalog-image w-full"
                                         onerror="this.onerror=null;this.src='{{ asset('images/products/placeholder.svg') }}';" />

                                    <div class="space-y-4 p-6">
                                        <div class="space-y-2">
                                            <h2 class="text-lg font-semibold leading-tight text-black">{{ $product['product_name'] }}</h2>
                                            <p class="text-sm leading-relaxed text-black/65">
                                                {{ $product['description'] ?: 'No description available for this product yet.' }}
                                            </p>
                                        </div>

                                        <div class="flex flex-wrap gap-2">
                                            @forelse($product['categories'] as $categoryName)
                                                <span class="catalog-chip inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-wide">
                                                    {{ $categoryName }}
                                                </span>
                                            @empty
                                                <span class="catalog-chip inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-wide">
                                                    Uncategorized
                                                </span>
                                            @endforelse
                                        </div>

                                        <div class="flex items-start justify-between gap-4">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.2em] text-black/45">Price</p>
                                                <p class="catalog-price font-mono text-2xl leading-none">BDT {{ number_format((float) $product['price'], 2) }}</p>
                                            </div>
                                            <div class="space-y-1 text-right">
                                                @if($reviewCount > 0)
                                                    <p class="catalog-rating-summary text-xs font-semibold text-black/70">
                                                        <span class="text-amber-500">&#9733;</span>
                                                        {{ number_format($averageRating, 1) }}
                                                        <span class="text-black/45">({{ $reviewCount }} {{ $reviewCount === 1 ? 'review' : 'reviews' }})</span>
                                                    </p>
                                                @else
                                                    <p class="catalog-rating-summary text-xs font-semibold text-black/45">
                                                        No reviews yet
                                                    </p>
                                                @endif
                                                <span @class([
                                                    'inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold',
                                                    'catalog-stock-out' => $stockQty === 0,
                                                    'catalog-stock-low' => $stockQty > 0 && $stockQty <= 10,
                                                    'catalog-stock-ok' => $stockQty > 10,
                                                ])>
                                                    {{ $stockLabel }}
                                                </span>
                                            </div>
                                        </div>

                                        <form method="POST"
                                              action="{{ route('cart.add', ['productId' => (int) $product['product_id']]) }}"
                                              class="pt-1"
                                              x-on:submit.prevent="addToCart($event, {{ (int) $product['product_id'] }})"
                                              x-on:click.stop
                                              x-on:keydown.stop>
                                            @csrf
                                            <input type="hidden" name="quantity" value="1" />
                                            <button type="submit"
                                                    class="catalog-add-btn inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition"
                                                    x-bind:disabled="{{ $stockQty === 0 ? 'true' : ('isAdding(' . (int) $product['product_id'] . ')') }}"
                                                    @disabled($stockQty === 0)
                                                    x-on:click.stop>
                                                <span x-show="{{ $stockQty === 0 ? 'true' : 'false' }}">Out of stock</span>
                                                <span x-show="{{ $stockQty > 0 ? 'true' : 'false' }}"
                                                      x-text="isAdding({{ (int) $product['product_id'] }}) ? 'Adding...' : 'Add to Cart'">
                                                    Add to Cart
                                                </span>
                                            </button>
                                        </form>
                                    </div>
                                </article>
                            @endforeach
                        </div>
                    @else
                        <div class="catalog-empty rounded-2xl p-8 text-center">
                            <p class="font-mono text-2xl text-black">No matching products</p>
                            <p class="mx-auto mt-2 max-w-xl text-sm text-black/65">
                                Try removing the category filter, clearing the search box, or switching to a different sorting option.
                            </p>
                            <a href="{{ route('products') }}"
                               class="catalog-btn-secondary mt-5 inline-flex rounded-xl px-4 py-2.5 text-sm font-semibold transition hover:bg-[#f9f4e5]">
                                View full catalog
                            </a>
                        </div>
                    @endif
                </section>

                @if($products->hasPages())
                    <section class="catalog-filters catalog-enter catalog-enter-5 rounded-2xl p-5">
                        {{ $products->onEachSide(1)->links() }}
                    </section>
                @endif
            </div>
        </main>
    </div>

    <div
        x-cloak
        x-show="selectedProduct"
        x-transition.opacity.duration.250ms
        class="catalog-overlay"
        @keydown.escape.window="closeProduct()"
    >
        <div class="catalog-overlay-backdrop" @click="closeProduct()"></div>

        <template x-if="selectedProduct">
            <article
                class="catalog-modal-card"
                x-show="selectedProduct"
                x-transition:enter="transition duration-350 ease-out"
                x-transition:enter-start="opacity-0 translate-y-8 scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                x-transition:leave="transition duration-250 ease-in"
                x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                x-transition:leave-end="opacity-0 translate-y-6 scale-95"
            >
                <button type="button" class="catalog-modal-close" @click="closeProduct()">Close</button>

                <div class="catalog-modal-grid">
                    <div>
                        <img
                            :src="selectedProduct.image"
                            :alt="selectedProduct.name"
                            class="catalog-image w-full rounded-xl"
                            x-on:error="$event.target.src='{{ asset('images/products/placeholder.svg') }}'"
                        />
                    </div>

                    <div class="space-y-5">
                        <div>
                            <p class="text-xs uppercase tracking-[0.22em] text-black/45">Selected Product</p>
                            <h2 class="catalog-heading font-mono mt-2 text-4xl leading-[0.95]" x-text="selectedProduct.name"></h2>
                            <p class="catalog-subtitle mt-3 text-sm leading-relaxed" x-text="selectedProduct.description"></p>
                        </div>

                        <div class="flex flex-wrap gap-2">
                            <template x-for="category in selectedProduct.categories" :key="category">
                                <span class="catalog-chip inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-wide" x-text="category"></span>
                            </template>
                            <template x-if="!selectedProduct.categories || selectedProduct.categories.length === 0">
                                <span class="catalog-chip inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold tracking-wide">Uncategorized</span>
                            </template>
                        </div>

                        <div class="flex flex-wrap items-center justify-between gap-3">
                            <p class="catalog-price font-mono text-3xl leading-none" x-text="selectedProduct.priceFormatted"></p>
                            <span class="inline-flex items-center rounded-full px-3 py-1 text-xs font-semibold"
                                  :class="selectedProduct.stockClass"
                                  x-text="selectedProduct.stockLabel"></span>
                        </div>

                        <div class="catalog-review-panel rounded-xl p-4">
                            <p class="text-xs font-semibold uppercase tracking-[0.2em] text-black/45">Customer Rating</p>
                            <p class="mt-2 text-sm text-black/70"
                               x-text="selectedProduct.reviewCount > 0
                                    ? `Average rating: ${Number(selectedProduct.averageRating).toFixed(1)} / 5 (${selectedProduct.reviewCount} review${selectedProduct.reviewCount === 1 ? '' : 's'})`
                                    : 'Average rating: No reviews yet'">
                            </p>

                            <form method="POST"
                                  class="mt-4 space-y-3"
                                  :action="selectedProduct.reviewAction">
                                @csrf
                                <input type="hidden" name="rating" :value="reviewForm.rating" />

                                <div class="catalog-star-list" role="radiogroup" aria-label="Star rating">
                                    <template x-for="star in starScale" :key="star">
                                        <button
                                            type="button"
                                            class="catalog-star-btn"
                                            :class="{ 'catalog-star-btn-active': reviewForm.rating >= star }"
                                            :style="{ color: reviewForm.rating >= star ? '#f59e0b' : '#d4d4d4' }"
                                            :aria-label="`Rate ${star} star${star === 1 ? '' : 's'}`"
                                            @click="setReviewRating(star)">
                                            &#9733;
                                        </button>
                                    </template>
                                </div>

                                <p class="text-xs text-black/55"
                                   x-text="reviewForm.rating > 0
                                        ? `${reviewForm.rating} star${reviewForm.rating === 1 ? '' : 's'} selected`
                                        : 'Select a star rating (required)'">
                                </p>

                                <div>
                                    <label for="review-text" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-black/55">
                                        Review (optional)
                                    </label>
                                    <textarea
                                        id="review-text"
                                        name="review_text"
                                        maxlength="4000"
                                        rows="4"
                                        class="catalog-input w-full px-3 py-2 text-sm"
                                        placeholder="Write what you liked or disliked (optional)..."
                                        x-model="reviewForm.reviewText"></textarea>
                                </div>

                                <button type="submit"
                                        class="catalog-btn-primary inline-flex w-full items-center justify-center rounded-xl px-4 py-2.5 text-sm font-semibold transition hover:brightness-110"
                                        :disabled="reviewForm.rating < 1">
                                    Save Review
                                </button>
                            </form>

                            <div class="catalog-review-list mt-5 space-y-3">
                                <div class="flex items-center justify-between gap-3">
                                    <p class="text-xs font-semibold uppercase tracking-[0.16em] text-black/55">Customer Reviews</p>
                                    <p class="text-xs text-black/50">Max 5 per page</p>
                                </div>

                                <p class="text-sm text-black/60" x-show="reviews.loading">Loading reviews...</p>
                                <p class="text-sm text-rose-700" x-show="!reviews.loading && reviews.error" x-text="reviews.error"></p>

                                <template x-if="!reviews.loading && !reviews.error && reviews.items.length === 0">
                                    <p class="text-sm text-black/60">No reviews yet for this product.</p>
                                </template>

                                <template x-for="review in reviews.items" :key="review.review_id">
                                    <article class="catalog-review-item rounded-xl p-3">
                                        <div class="catalog-review-meta flex items-center justify-between gap-3">
                                            <p class="text-sm font-semibold text-black/80" x-text="review.reviewer_name"></p>
                                            <p class="text-xs text-black/45" x-text="review.reviewed_at"></p>
                                        </div>
                                        <p class="catalog-review-stars text-sm text-amber-500" x-text="'★'.repeat(review.rating) + '☆'.repeat(5 - review.rating)"></p>
                                        <p class="mt-1 text-sm leading-relaxed text-black/70"
                                           x-text="review.review_text && review.review_text.length > 0 ? review.review_text : 'Rated without review text.'"></p>
                                    </article>
                                </template>

                                <div class="catalog-review-pagination flex items-center justify-between gap-3 pt-1">
                                    <button type="button"
                                            class="catalog-btn-secondary rounded-lg px-3 py-1.5 text-xs font-semibold"
                                            :disabled="reviews.loading || !reviews.hasPrev"
                                            @click="loadPreviousReviews()">
                                        Previous
                                    </button>
                                    <p class="text-xs text-black/55" x-text="`Page ${reviews.page}`"></p>
                                    <button type="button"
                                            class="catalog-btn-secondary rounded-lg px-3 py-1.5 text-xs font-semibold"
                                            :disabled="reviews.loading || !reviews.hasNext"
                                            @click="loadNextReviews()">
                                        Next
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </article>
        </template>
    </div>

<div x-cloak
     x-show="flashMessage"
     x-transition:enter="transition ease-out duration-300"
     x-transition:enter-start="opacity-0 translate-y-3 sm:translate-y-0 sm:translate-x-3"
     x-transition:enter-end="opacity-100 translate-y-0 sm:translate-x-0"
     x-transition:leave="transition ease-in duration-200"
     x-transition:leave-start="opacity-100 translate-y-0 sm:translate-x-0"
     x-transition:leave-end="opacity-0 translate-y-2 sm:translate-y-0 sm:translate-x-2"
     class="pointer-events-none fixed inset-x-4 top-4 z-[110] flex justify-center">
    <div class="pointer-events-auto w-full max-w-sm overflow-hidden rounded-2xl border shadow-2xl ring-1 ring-black/5"
         :class="flashType === 'success'
            ? 'border-emerald-200 bg-white'
            : 'border-[#efc7c7] bg-white'">
        <div class="flex items-start gap-3 px-4 py-4"
             :class="flashType === 'success'
                ? 'bg-white text-emerald-700'
                : 'bg-[#fff1ee] text-rose-800'">
            <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-full text-xs font-bold"
                 :class="flashType === 'success'
                    ? 'bg-emerald-100 text-emerald-700'
                    : 'bg-rose-100 text-rose-700'">
                <span x-text="flashType === 'success' ? 'OK' : '!'"></span>
            </div>
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold uppercase tracking-[0.18em]"
                   x-text="flashType === 'success' ? 'Added to cart' : 'Cart update failed'"></p>
                <p class="mt-1 text-sm leading-relaxed" x-text="flashMessage"></p>
            </div>
            <button type="button"
                    class="rounded-lg px-2 py-1 text-xs font-semibold text-black/45 transition hover:bg-black/5 hover:text-black/70"
                    @click="clearFlash()"
                    aria-label="Dismiss notification">
                Close
            </button>
        </div>
    </div>
    </div>
</div>

<script>
    function productCatalogPage(config = {}) {
        const initialCartItemCount = Number(config.initialCartItemCount ?? 0);
        const initialCartUniqueCount = Number(config.initialCartUniqueCount ?? 0);

        return {
            selectedProduct: null,
            starScale: [1, 2, 3, 4, 5],
            reviewForm: {
                rating: 0,
                reviewText: '',
            },
            reviews: {
                items: [],
                page: 1,
                hasPrev: false,
                hasNext: false,
                loading: false,
                error: '',
            },
            cartItemCount: Number.isFinite(initialCartItemCount) ? initialCartItemCount : 0,
            cartUniqueCount: Number.isFinite(initialCartUniqueCount) ? initialCartUniqueCount : 0,
            flashMessage: '',
            flashType: 'success',
            pendingProductIds: [],
            flashTimeoutId: null,
            openProduct(product) {
                this.selectedProduct = product;
                this.reviewForm.rating = Number(product.userRating ?? 0);
                this.reviewForm.reviewText = String(product.userReviewText ?? '');
                this.fetchProductReviews(1);
                document.body.classList.add('overflow-hidden');
            },
            closeProduct() {
                this.selectedProduct = null;
                this.reviews.items = [];
                this.reviews.page = 1;
                this.reviews.hasPrev = false;
                this.reviews.hasNext = false;
                this.reviews.loading = false;
                this.reviews.error = '';
                document.body.classList.remove('overflow-hidden');
            },
            setReviewRating(star) {
                this.reviewForm.rating = Number(star);
            },
            loadNextReviews() {
                if (this.reviews.loading || !this.reviews.hasNext) {
                    return;
                }

                this.fetchProductReviews(this.reviews.page + 1);
            },
            loadPreviousReviews() {
                if (this.reviews.loading || !this.reviews.hasPrev) {
                    return;
                }

                this.fetchProductReviews(this.reviews.page - 1);
            },
            async fetchProductReviews(page = 1) {
                if (!this.selectedProduct || !this.selectedProduct.reviewsUrl) {
                    return;
                }

                const currentProductId = Number(this.selectedProduct.id);
                const targetPage = Math.max(1, Number(page) || 1);
                const requestUrl = `${this.selectedProduct.reviewsUrl}?page=${targetPage}`;

                this.reviews.loading = true;
                this.reviews.error = '';

                try {
                    const response = await fetch(requestUrl, {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        credentials: 'same-origin',
                    });

                    const contentType = response.headers.get('content-type') || '';
                    const payload = contentType.includes('application/json')
                        ? await response.json()
                        : null;

                    if (!response.ok || !payload || payload.success !== true || !payload.data) {
                        const message = payload?.message ?? 'Unable to load reviews right now.';
                        throw new Error(message);
                    }

                    if (!this.selectedProduct || Number(this.selectedProduct.id) !== currentProductId) {
                        return;
                    }

                    this.reviews.items = Array.isArray(payload.data.items) ? payload.data.items : [];
                    this.reviews.page = Number(payload.data.page ?? targetPage);
                    this.reviews.hasPrev = Boolean(payload.data.has_prev);
                    this.reviews.hasNext = Boolean(payload.data.has_next);

                    this.selectedProduct.averageRating = Number(payload.data.average_rating ?? this.selectedProduct.averageRating);
                    this.selectedProduct.reviewCount = Number(payload.data.review_count ?? this.selectedProduct.reviewCount);
                } catch (error) {
                    this.reviews.items = [];
                    this.reviews.hasPrev = false;
                    this.reviews.hasNext = false;
                    this.reviews.error = error?.message || 'Unable to load reviews right now.';
                } finally {
                    this.reviews.loading = false;
                }
            },
            isAdding(productId) {
                return this.pendingProductIds.includes(productId);
            },
            setFlash(message, type = 'success') {
                this.flashMessage = message;
                this.flashType = type;

                if (this.flashTimeoutId) {
                    clearTimeout(this.flashTimeoutId);
                }

                this.flashTimeoutId = window.setTimeout(() => {
                    this.clearFlash();
                }, 3200);
            },
            clearFlash() {
                this.flashMessage = '';

                if (this.flashTimeoutId) {
                    clearTimeout(this.flashTimeoutId);
                    this.flashTimeoutId = null;
                }
            },
            async addToCart(event, productId) {
                if (this.isAdding(productId)) {
                    return;
                }

                const form = event.target;
                this.pendingProductIds.push(productId);

                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                        },
                        body: new FormData(form),
                        credentials: 'same-origin',
                    });

                    const contentType = response.headers.get('content-type') || '';
                    const payload = contentType.includes('application/json')
                        ? await response.json()
                        : null;

                    if (!response.ok || !payload || payload.success !== true) {
                        const errorMessage =
                            payload?.errors?.quantity?.[0]
                            ?? payload?.message
                            ?? 'Unable to add this item right now. Please try again.';

                        this.setFlash(errorMessage, 'error');
                        return;
                    }

                    this.cartItemCount = Number(payload.cart_summary?.item_count ?? this.cartItemCount);
                    this.cartUniqueCount = Number(payload.cart_summary?.unique_count ?? this.cartUniqueCount);
                    this.setFlash(payload.message ?? 'Item added to cart.', 'success');
                } catch (error) {
                    this.setFlash('Unable to add this item right now. Please try again.', 'error');
                } finally {
                    this.pendingProductIds = this.pendingProductIds.filter((id) => id !== productId);
                }
            },
        };
    }
</script>

@include('partials.mssql-console-debug')
</body>
</html>
