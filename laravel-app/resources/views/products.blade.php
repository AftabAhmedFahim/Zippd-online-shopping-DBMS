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
@endphp

<div x-data="productCatalogPage()" class="min-h-screen">
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

                                        <div class="flex items-center justify-between gap-4">
                                            <div>
                                                <p class="text-xs uppercase tracking-[0.2em] text-black/45">Price</p>
                                                <p class="catalog-price font-mono text-2xl leading-none">BDT {{ number_format((float) $product['price'], 2) }}</p>
                                            </div>
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
                    </div>
                </div>
            </article>
        </template>
    </div>
</div>

<script>
    function productCatalogPage() {
        return {
            selectedProduct: null,
            openProduct(product) {
                this.selectedProduct = product;
                document.body.classList.add('overflow-hidden');
            },
            closeProduct() {
                this.selectedProduct = null;
                document.body.classList.remove('overflow-hidden');
            },
        };
    }
</script>

@include('partials.mssql-console-debug')
</body>
</html>
