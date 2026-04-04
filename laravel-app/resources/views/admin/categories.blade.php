@extends('admin.layout')

@section('admin-content')
@php
    $editingCategoryId = (int) ($editingCategoryId ?? 0);
    $storeErrors = $errors->getBag('storeCategory');
    $updateErrors = $errors->getBag('updateCategory');
@endphp

<section class="flex flex-wrap items-start justify-between gap-4">
    <div class="space-y-2">
        <h1 class="font-mono text-[42px] leading-[0.95] tracking-[-0.02em] text-black">Categories Management</h1>
        <p class="font-roboto text-[15px] text-black/70">Live category table with add/edit and category-wise product browsing.</p>
    </div>
    <button
        id="toggle-add-category-form-btn"
        type="button"
        class="admin-action-btn admin-action-success rounded-xl px-4 py-2 text-sm"
    >
        Add Category
    </button>
</section>

@if (session('categorySuccess'))
    <section class="rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
        {{ session('categorySuccess') }}
    </section>
@endif

@if (session('categoryError'))
    <section class="rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
        {{ session('categoryError') }}
    </section>
@endif

<section
    id="add-category-form-wrap"
    class="dashboard-solid-card rounded-2xl p-5"
    style="{{ $storeErrors->any() ? '' : 'display: none;' }}"
>
    <h2 class="font-mono text-2xl leading-none">Add New Category</h2>
    <form method="POST" action="{{ route('admin.categories.store') }}" class="mt-4 grid gap-4 md:grid-cols-2">
        @csrf
        <div class="space-y-2">
            <label for="store-category-name" class="text-xs font-semibold uppercase tracking-wide text-black/55">Category Name</label>
            <input
                id="store-category-name"
                name="category_name"
                type="text"
                value="{{ old('category_name') }}"
                class="admin-auth-input {{ $storeErrors->has('category_name') ? 'admin-auth-input-error' : '' }}"
                maxlength="255"
                required
            />
            @if ($storeErrors->has('category_name'))
                <p class="text-xs text-rose-600">{{ $storeErrors->first('category_name') }}</p>
            @endif
        </div>

        <div class="space-y-2">
            <label for="store-category-description" class="text-xs font-semibold uppercase tracking-wide text-black/55">Description</label>
            <textarea
                id="store-category-description"
                name="description"
                rows="3"
                maxlength="5000"
                class="admin-auth-input {{ $storeErrors->has('description') ? 'admin-auth-input-error' : '' }}"
            >{{ old('description') }}</textarea>
            @if ($storeErrors->has('description'))
                <p class="text-xs text-rose-600">{{ $storeErrors->first('description') }}</p>
            @endif
        </div>

        <div class="md:col-span-2 flex flex-wrap items-center gap-2">
            <button type="submit" class="admin-action-btn admin-action-success rounded-xl px-4 py-2 text-sm">Save Category</button>
            <button type="button" id="cancel-add-category-form-btn" class="admin-action-btn admin-action-info rounded-xl px-4 py-2 text-sm">Cancel</button>
        </div>
    </form>
</section>

<section class="dashboard-solid-card overflow-hidden rounded-2xl">
    <header class="border-b border-black/10 px-5 py-4">
        <label class="admin-search-wrap max-w-sm">
            <svg class="h-4 w-4 text-black/40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z" />
            </svg>
            <input
                id="admin-categories-live-search"
                type="text"
                value="{{ $initialSearchQuery ?? '' }}"
                class="admin-search-input"
                placeholder="Search by ID, name, or description..."
                autocomplete="off"
            />
        </label>
    </header>

    <div class="overflow-x-auto">
        <table class="admin-table min-w-[900px]">
            <thead>
            <tr>
                <th>Category ID</th>
                <th>Category Name</th>
                <th>Description</th>
                <th>Products</th>
                <th class="text-right">Actions</th>
            </tr>
            </thead>
            <tbody id="admin-categories-table-body">
            @forelse ($categories as $category)
                @php
                    $categoryId = (int) ($category['category_id'] ?? 0);
                    $isEditingThisRow = $editingCategoryId === $categoryId;
                    $searchableText = strtolower(trim(implode(' ', [
                        (string) ($category['category_id'] ?? ''),
                        (string) ($category['category_name'] ?? ''),
                        (string) ($category['description'] ?? ''),
                        (string) ($category['product_count'] ?? ''),
                    ])));
                @endphp
                <tr
                    class="admin-category-data-row"
                    data-search-text="{{ e($searchableText) }}"
                    data-category-id="{{ $categoryId }}"
                >
                    <td class="font-semibold text-black">{{ $categoryId }}</td>
                    <td>
                        <a
                            href="{{ route('admin.categories.products', ['categoryId' => $categoryId]) }}"
                            class="font-semibold text-blue-700 hover:underline"
                        >
                            {{ $category['category_name'] ?? '-' }}
                        </a>
                    </td>
                    <td class="max-w-md">{{ $category['description'] ?? '-' }}</td>
                    <td>
                        <a
                            href="{{ route('admin.categories.products', ['categoryId' => $categoryId]) }}"
                            class="inline-flex items-center rounded-full bg-blue-50 px-2.5 py-1 text-xs font-semibold text-blue-700 hover:bg-blue-100"
                        >
                            {{ (int) ($category['product_count'] ?? 0) }} products
                        </a>
                    </td>
                    <td class="text-right">
                        <div class="inline-flex items-center gap-2">
                            <button
                                type="button"
                                class="admin-icon-btn admin-icon-btn-edit admin-category-edit-toggle"
                                data-target="category-edit-row-{{ $categoryId }}"
                                aria-label="Edit category"
                            >
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2M4 21h4l10.5-10.5a1.5 1.5 0 00-2.12-2.12L5.88 18.88 4 21z" />
                                </svg>
                            </button>
                        </div>
                    </td>
                </tr>
                <tr
                    id="category-edit-row-{{ $categoryId }}"
                    class="admin-category-edit-row"
                    style="{{ $isEditingThisRow ? '' : 'display: none;' }}"
                >
                    <td colspan="5" class="bg-[#fbfbf7]">
                        <form method="POST" action="{{ route('admin.categories.update', ['categoryId' => $categoryId]) }}" class="grid gap-3 md:grid-cols-2">
                            @csrf
                            @method('PUT')
                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-black/55">Category Name</label>
                                <input
                                    name="category_name"
                                    type="text"
                                    value="{{ $isEditingThisRow ? old('category_name', $category['category_name'] ?? '') : ($category['category_name'] ?? '') }}"
                                    class="admin-auth-input {{ $isEditingThisRow && $updateErrors->has('category_name') ? 'admin-auth-input-error' : '' }}"
                                    maxlength="255"
                                    required
                                />
                                @if ($isEditingThisRow && $updateErrors->has('category_name'))
                                    <p class="text-xs text-rose-600">{{ $updateErrors->first('category_name') }}</p>
                                @endif
                            </div>

                            <div class="space-y-2">
                                <label class="text-xs font-semibold uppercase tracking-wide text-black/55">Description</label>
                                <textarea
                                    name="description"
                                    rows="3"
                                    maxlength="5000"
                                    class="admin-auth-input {{ $isEditingThisRow && $updateErrors->has('description') ? 'admin-auth-input-error' : '' }}"
                                >{{ $isEditingThisRow ? old('description', $category['description'] ?? '') : ($category['description'] ?? '') }}</textarea>
                                @if ($isEditingThisRow && $updateErrors->has('description'))
                                    <p class="text-xs text-rose-600">{{ $updateErrors->first('description') }}</p>
                                @endif
                            </div>

                            <div class="md:col-span-2 flex flex-wrap items-center gap-2">
                                <button type="submit" class="admin-action-btn admin-action-success rounded-xl px-4 py-2 text-sm">Update Category</button>
                                <button
                                    type="button"
                                    class="admin-action-btn admin-action-info rounded-xl px-4 py-2 text-sm admin-category-edit-cancel"
                                    data-target="category-edit-row-{{ $categoryId }}"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-black/55">
                        No categories available.
                    </td>
                </tr>
            @endforelse
            <tr id="admin-categories-no-match-row" style="display: none;">
                <td colspan="5" class="py-8 text-center text-black/55">
                    No categories found for this search.
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const addFormWrap = document.getElementById('add-category-form-wrap');
    const toggleAddFormBtn = document.getElementById('toggle-add-category-form-btn');
    const cancelAddFormBtn = document.getElementById('cancel-add-category-form-btn');
    const input = document.getElementById('admin-categories-live-search');
    const tableBody = document.getElementById('admin-categories-table-body');
    const noMatchRow = document.getElementById('admin-categories-no-match-row');

    if (toggleAddFormBtn && addFormWrap) {
        toggleAddFormBtn.addEventListener('click', function () {
            addFormWrap.style.display = addFormWrap.style.display === 'none' ? '' : 'none';
        });
    }

    if (cancelAddFormBtn && addFormWrap) {
        cancelAddFormBtn.addEventListener('click', function () {
            addFormWrap.style.display = 'none';
        });
    }

    const editRows = Array.from(document.querySelectorAll('.admin-category-edit-row'));
    const editToggles = Array.from(document.querySelectorAll('.admin-category-edit-toggle'));
    const editCancels = Array.from(document.querySelectorAll('.admin-category-edit-cancel'));

    const closeAllEditRows = () => {
        editRows.forEach((row) => {
            row.style.display = 'none';
        });
    };

    editToggles.forEach((button) => {
        button.addEventListener('click', function () {
            const targetId = button.getAttribute('data-target');
            if (!targetId) {
                return;
            }

            const targetRow = document.getElementById(targetId);
            if (!targetRow) {
                return;
            }

            const isCurrentlyVisible = targetRow.style.display !== 'none';
            closeAllEditRows();
            targetRow.style.display = isCurrentlyVisible ? 'none' : '';
        });
    });

    editCancels.forEach((button) => {
        button.addEventListener('click', function () {
            const targetId = button.getAttribute('data-target');
            if (!targetId) {
                return;
            }

            const targetRow = document.getElementById(targetId);
            if (targetRow) {
                targetRow.style.display = 'none';
            }
        });
    });

    if (!input || !tableBody || !noMatchRow) {
        return;
    }

    const dataRows = Array.from(tableBody.querySelectorAll('.admin-category-data-row'));

    const applyFilter = () => {
        const query = input.value.trim().toLowerCase();
        let visibleCount = 0;

        dataRows.forEach((row) => {
            const haystack = row.getAttribute('data-search-text') || '';
            const isMatch = query === '' || haystack.includes(query);
            const categoryId = row.getAttribute('data-category-id');
            const editRow = categoryId ? document.getElementById('category-edit-row-' + categoryId) : null;

            row.style.display = isMatch ? '' : 'none';
            if (!isMatch && editRow) {
                editRow.style.display = 'none';
            }

            if (isMatch) {
                visibleCount += 1;
            }
        });

        noMatchRow.style.display = visibleCount === 0 ? '' : 'none';
    };

    input.addEventListener('input', applyFilter);
    applyFilter();
});
</script>
@endsection
