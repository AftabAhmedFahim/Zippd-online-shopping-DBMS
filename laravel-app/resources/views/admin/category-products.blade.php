@extends('admin.layout')

@section('admin-content')
<section class="space-y-2">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-mono text-[38px] leading-[0.98] tracking-[-0.02em] text-black">
                Category Products: {{ $category['category_name'] ?? 'Unknown' }}
            </h1>
            <p class="font-roboto mt-1 text-[15px] text-black/70">
                Showing products mapped to this category. 10 products per page.
            </p>
        </div>
        <a href="{{ route('admin.categories') }}" class="admin-action-btn admin-action-info rounded-xl px-4 py-2 text-sm">
            Back to Categories
        </a>
    </div>
</section>

<section class="dashboard-solid-card overflow-hidden rounded-2xl">
    <header class="border-b border-black/10 px-5 py-4">
        <div class="flex flex-wrap items-center justify-between gap-2">
            <p class="text-sm text-black/70">
                Total products in this category: <span class="font-semibold text-black">{{ (int) ($category['product_count'] ?? 0) }}</span>
            </p>
            <p class="text-xs uppercase tracking-wide text-black/45">
                Category ID: {{ (int) ($category['category_id'] ?? 0) }}
            </p>
        </div>
    </header>

    <div class="overflow-x-auto">
        <table class="admin-table min-w-[900px]">
            <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Description</th>
                <th>Stock</th>
                <th>Price</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($products as $product)
                @php
                    $stockQty = (int) ($product['stock_qty'] ?? 0);
                @endphp
                <tr>
                    <td class="font-semibold text-black">{{ (int) ($product['product_id'] ?? 0) }}</td>
                    <td>{{ $product['product_name'] ?? '-' }}</td>
                    <td class="max-w-md">{{ $product['description'] ?? '-' }}</td>
                    <td>
                        <span @class([
                            'admin-status-badge',
                            'admin-status-danger' => $stockQty <= 0,
                            'admin-status-info' => $stockQty > 0 && $stockQty <= 10,
                            'admin-status-success' => $stockQty > 10,
                        ])>
                            {{ $stockQty }}
                        </span>
                    </td>
                    <td>BDT {{ number_format((float) ($product['price'] ?? 0), 2) }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-black/55">
                        No products found in this category.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

@if ($products->hasPages())
    <section class="dashboard-solid-card rounded-2xl p-5">
        {{ $products->onEachSide(1)->links() }}
    </section>
@endif
@endsection
