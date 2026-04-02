@extends('admin.layout')

@section('admin-content')
@php
    $categories = [
        ['category_id' => 'CAT001', 'category_name' => 'Electronics', 'description' => 'Electronic devices and accessories', 'created_at' => '2025-01-10', 'updated_at' => '2026-02-15', 'product_count' => 156],
        ['category_id' => 'CAT002', 'category_name' => 'Clothing', 'description' => "Men's and women's apparel", 'created_at' => '2025-01-12', 'updated_at' => '2026-02-18', 'product_count' => 243],
        ['category_id' => 'CAT003', 'category_name' => 'Home & Garden', 'description' => 'Home improvement and garden supplies', 'created_at' => '2025-01-15', 'updated_at' => '2026-02-20', 'product_count' => 98],
        ['category_id' => 'CAT004', 'category_name' => 'Sports & Outdoors', 'description' => 'Sports equipment and outdoor gear', 'created_at' => '2025-02-01', 'updated_at' => '2026-02-25', 'product_count' => 127],
        ['category_id' => 'CAT005', 'category_name' => 'Books', 'description' => 'Physical and digital books', 'created_at' => '2025-02-10', 'updated_at' => '2026-03-01', 'product_count' => 268],
    ];
@endphp

<section class="flex flex-wrap items-start justify-between gap-4">
    <div class="space-y-2">
        <h1 class="font-mono text-[42px] leading-[0.95] tracking-[-0.02em] text-black">Categories Management</h1>
        <p class="font-roboto text-[15px] text-black/70">UI-only category table with add/edit/delete action states.</p>
    </div>
    <button type="button" class="admin-action-btn admin-action-success rounded-xl px-4 py-2 text-sm">Add Category</button>
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
        <table class="admin-table min-w-[980px]">
            <thead>
            <tr>
                <th>Category ID</th>
                <th>Category Name</th>
                <th>Description</th>
                <th>Products</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th class="text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($categories as $category)
                <tr>
                    <td class="font-semibold text-black">{{ $category['category_id'] }}</td>
                    <td>{{ $category['category_name'] }}</td>
                    <td class="max-w-md">{{ $category['description'] }}</td>
                    <td>{{ $category['product_count'] }}</td>
                    <td>{{ $category['created_at'] }}</td>
                    <td>{{ $category['updated_at'] }}</td>
                    <td class="text-right">
                        <div class="inline-flex items-center gap-2">
                            <button type="button" class="admin-icon-btn admin-icon-btn-edit" aria-label="Edit category">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2M4 21h4l10.5-10.5a1.5 1.5 0 00-2.12-2.12L5.88 18.88 4 21z" />
                                </svg>
                            </button>
                            <button type="button" class="admin-icon-btn admin-icon-btn-danger" aria-label="Delete category">
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
