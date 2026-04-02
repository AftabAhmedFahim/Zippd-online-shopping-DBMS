@extends('admin.layout')

@section('admin-content')
@php
    $products = [
        ['product_id' => 'PRD001', 'product_name' => 'Wireless Headphones', 'description' => 'High-quality Bluetooth headphones with noise cancellation', 'stock_qty' => 45, 'price' => 79.99, 'created_at' => '2025-01-20', 'updated_at' => '2026-03-05', 'rating' => 4.5],
        ['product_id' => 'PRD002', 'product_name' => 'Smart Watch', 'description' => 'Fitness tracking smartwatch with heart rate monitor', 'stock_qty' => 23, 'price' => 199.99, 'created_at' => '2025-02-01', 'updated_at' => '2026-03-08', 'rating' => 4.7],
        ['product_id' => 'PRD003', 'product_name' => 'Laptop Stand', 'description' => 'Adjustable aluminum laptop stand', 'stock_qty' => 78, 'price' => 34.99, 'created_at' => '2025-02-10', 'updated_at' => '2026-03-09', 'rating' => 4.2],
        ['product_id' => 'PRD004', 'product_name' => 'USB-C Hub', 'description' => '7-in-1 USB-C hub with multiple ports', 'stock_qty' => 156, 'price' => 49.99, 'created_at' => '2025-02-15', 'updated_at' => '2026-03-09', 'rating' => 4.6],
        ['product_id' => 'PRD005', 'product_name' => 'Mechanical Keyboard', 'description' => 'RGB backlit mechanical gaming keyboard', 'stock_qty' => 12, 'price' => 129.99, 'created_at' => '2025-02-20', 'updated_at' => '2026-03-10', 'rating' => 4.8],
    ];
@endphp

<section class="flex flex-wrap items-start justify-between gap-4">
    <div class="space-y-2">
        <h1 class="font-mono text-[42px] leading-[0.95] tracking-[-0.02em] text-black">Products Management</h1>
        <p class="font-roboto text-[15px] text-black/70">UI-only product inventory controls and listing layout.</p>
    </div>
    <button type="button" class="admin-action-btn admin-action-info rounded-xl px-4 py-2 text-sm">Add Product</button>
</section>

<section class="dashboard-solid-card overflow-hidden rounded-2xl">
    <header class="border-b border-black/10 px-5 py-4">
        <label class="admin-search-wrap max-w-sm">
            <svg class="h-4 w-4 text-black/40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z" />
            </svg>
            <input type="text" class="admin-search-input" placeholder="Search by ID or name..." />
        </label>
    </header>

    <div class="overflow-x-auto">
        <table class="admin-table min-w-[1120px]">
            <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Description</th>
                <th>Stock</th>
                <th>Price</th>
                <th>Rating</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th class="text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($products as $product)
                <tr>
                    <td class="font-semibold text-black">{{ $product['product_id'] }}</td>
                    <td>{{ $product['product_name'] }}</td>
                    <td class="max-w-xs truncate">{{ $product['description'] }}</td>
                    <td>
                        <span class="admin-status-badge {{ $product['stock_qty'] < 20 ? 'admin-status-danger' : 'admin-status-success' }}">
                            {{ $product['stock_qty'] }}
                        </span>
                    </td>
                    <td>${{ number_format($product['price'], 2) }}</td>
                    <td>
                        <span class="inline-flex items-center gap-1 text-black/80">
                            <span class="text-amber-500">★</span>
                            {{ number_format($product['rating'], 1) }}
                        </span>
                    </td>
                    <td>{{ $product['created_at'] }}</td>
                    <td>{{ $product['updated_at'] }}</td>
                    <td class="text-right">
                        <div class="inline-flex items-center gap-2">
                            <button type="button" class="admin-icon-btn admin-icon-btn-edit" aria-label="Edit product">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2M4 21h4l10.5-10.5a1.5 1.5 0 00-2.12-2.12L5.88 18.88 4 21z" />
                                </svg>
                            </button>
                            <button type="button" class="admin-icon-btn admin-icon-btn-danger" aria-label="Delete product">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V4h6v3m-7 4v6m4-6v6m4-10v12a1 1 0 01-1 1H8a1 1 0 01-1-1V7h10z" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
