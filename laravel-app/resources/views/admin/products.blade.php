@extends('admin.layout')

@section('admin-content')
@php
    $oldEditProductId = old('edit_product_id', session('admin_products_open_edit_id'));
    $oldEditProductId = is_numeric($oldEditProductId) ? (int) $oldEditProductId : null;
    $oldEditAction = $oldEditProductId !== null
        ? route('admin.products.update', ['productId' => $oldEditProductId])
        : '#';
@endphp

<section class="flex flex-wrap items-start justify-between gap-4">
    <div class="space-y-2">
        <h1 class="font-mono text-[42px] leading-[0.95] tracking-[-0.02em] text-black">Products Management</h1>
        <p class="font-roboto text-[15px] text-black/70">Manage live product records from database with add, edit, delete, and instant search.</p>
    </div>
    <button id="admin-products-open-create" type="button" class="admin-action-btn admin-action-info rounded-xl px-4 py-2 text-sm">
        Add Product
    </button>
</section>

@if (session('admin_products_success'))
    <section class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
        {{ session('admin_products_success') }}
    </section>
@endif

@if (session('admin_products_error'))
    <section class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
        {{ session('admin_products_error') }}
    </section>
@endif

<section id="admin-product-create-card" class="dashboard-solid-card rounded-2xl p-6" style="display: none;">
    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">
        <h2 class="font-mono text-2xl leading-none text-black">Add New Product</h2>
        <button id="admin-products-close-create" type="button" class="admin-action-btn admin-action-danger rounded-xl px-3 py-2 text-xs">
            Cancel
        </button>
    </div>

    @if ($errors->productCreate->any())
        <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
            Please fix the highlighted product fields and submit again.
        </div>
    @endif

    <form method="POST" action="{{ route('admin.products.store') }}" class="grid gap-4 md:grid-cols-2">
        @csrf

        <div class="md:col-span-2">
            <label for="admin-create-product-name" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-black/55">
                Product Name
            </label>
            <input
                id="admin-create-product-name"
                type="text"
                name="product_name"
                value="{{ old('product_name') }}"
                class="admin-product-input @if ($errors->productCreate->has('product_name')) admin-product-input-error @endif"
                placeholder="Enter product name"
                required
                maxlength="255"
            />
            @if ($errors->productCreate->has('product_name'))
                <p class="mt-2 text-sm text-rose-700">{{ $errors->productCreate->first('product_name') }}</p>
            @endif
        </div>

        <div class="md:col-span-2">
            <label for="admin-create-product-description" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-black/55">
                Description
            </label>
            <textarea
                id="admin-create-product-description"
                name="description"
                rows="4"
                class="admin-product-input @if ($errors->productCreate->has('description')) admin-product-input-error @endif"
                placeholder="Product description (optional)"
                maxlength="4000"
            >{{ old('description') }}</textarea>
            @if ($errors->productCreate->has('description'))
                <p class="mt-2 text-sm text-rose-700">{{ $errors->productCreate->first('description') }}</p>
            @endif
        </div>

        <div>
            <label for="admin-create-product-stock" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-black/55">
                Stock Quantity
            </label>
            <input
                id="admin-create-product-stock"
                type="number"
                name="stock_qty"
                value="{{ old('stock_qty') }}"
                class="admin-product-input @if ($errors->productCreate->has('stock_qty')) admin-product-input-error @endif"
                placeholder="0"
                min="0"
                required
            />
            @if ($errors->productCreate->has('stock_qty'))
                <p class="mt-2 text-sm text-rose-700">{{ $errors->productCreate->first('stock_qty') }}</p>
            @endif
        </div>

        <div>
            <label for="admin-create-product-price" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-black/55">
                Price
            </label>
            <input
                id="admin-create-product-price"
                type="number"
                name="price"
                value="{{ old('price') }}"
                class="admin-product-input @if ($errors->productCreate->has('price')) admin-product-input-error @endif"
                placeholder="0.00"
                min="0"
                step="0.01"
                required
            />
            @if ($errors->productCreate->has('price'))
                <p class="mt-2 text-sm text-rose-700">{{ $errors->productCreate->first('price') }}</p>
            @endif
        </div>

        <div class="md:col-span-2 flex justify-end">
            <button type="submit" class="admin-action-btn admin-action-success rounded-xl px-4 py-2 text-sm">
                Save Product
            </button>
        </div>
    </form>
</section>

<section class="dashboard-solid-card overflow-hidden rounded-2xl">
    <header class="border-b border-black/10 px-5 py-4">
        <label class="admin-search-wrap max-w-md">
            <svg class="h-4 w-4 text-black/40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z" />
            </svg>
            <input
                id="admin-products-live-search"
                type="text"
                value="{{ $initialSearchQuery ?? '' }}"
                class="admin-search-input"
                placeholder="Search by ID, name, description, stock, price, or category..."
                autocomplete="off"
            />
        </label>
    </header>

    <div class="overflow-x-auto">
        <table class="admin-table min-w-[1080px]">
            <thead>
            <tr>
                <th>Product ID</th>
                <th>Product Name</th>
                <th>Description</th>
                <th>Categories</th>
                <th>Stock</th>
                <th>Price</th>
                <th>Rating</th>
                <th class="text-right">Actions</th>
            </tr>
            </thead>
            <tbody id="admin-products-table-body">
            @forelse ($products as $product)
                @php
                    $categoryNames = trim((string) ($product['category_names'] ?? ''));
                    $categoryLabel = $categoryNames !== '' ? $categoryNames : 'Uncategorized';
                    $priceValue = number_format((float) ($product['price'] ?? 0), 2, '.', '');
                    $ratingValue = (float) ($product['average_rating'] ?? 0);
                    $ratingLabel = $ratingValue > 0 ? number_format($ratingValue, 1) : 'N/A';
                    $searchableText = strtolower(trim(implode(' ', [
                        (string) ($product['product_id'] ?? ''),
                        (string) ($product['product_name'] ?? ''),
                        (string) ($product['description'] ?? ''),
                        $categoryLabel,
                        (string) ($product['stock_qty'] ?? ''),
                        $priceValue,
                        $ratingLabel,
                    ])));
                @endphp
                <tr
                    data-search-text="{{ e($searchableText) }}"
                    data-product-id="{{ $product['product_id'] ?? '' }}"
                    data-product-name="{{ e((string) ($product['product_name'] ?? '')) }}"
                    data-product-description="{{ e((string) ($product['description'] ?? '')) }}"
                    data-product-stock="{{ $product['stock_qty'] ?? 0 }}"
                    data-product-price="{{ $priceValue }}"
                    data-update-url="{{ route('admin.products.update', ['productId' => $product['product_id']]) }}"
                >
                    <td class="font-semibold text-black">{{ $product['product_id'] ?? '-' }}</td>
                    <td>{{ $product['product_name'] ?? '-' }}</td>
                    <td class="max-w-sm">{{ $product['description'] ?? '-' }}</td>
                    <td>{{ $categoryLabel }}</td>
                    <td>
                        <span class="admin-status-badge {{ (int) ($product['stock_qty'] ?? 0) < 1 ? 'admin-status-danger' : 'admin-status-success' }}">
                            {{ $product['stock_qty'] ?? 0 }}
                        </span>
                    </td>
                    <td>${{ number_format((float) ($product['price'] ?? 0), 2) }}</td>
                    <td>
                        <span class="inline-flex items-center gap-1 text-black/80">
                            <span class="text-amber-500">&#9733;</span>
                            {{ $ratingLabel }}
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="inline-flex items-center gap-2">
                            <button type="button" class="admin-icon-btn admin-icon-btn-edit admin-product-edit-btn" aria-label="Edit product">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2M4 21h4l10.5-10.5a1.5 1.5 0 00-2.12-2.12L5.88 18.88 4 21z" />
                                </svg>
                            </button>
                            <form method="POST" action="{{ route('admin.products.destroy', ['productId' => $product['product_id']]) }}" onsubmit="return confirm('Delete this product? This action cannot be undone.');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="admin-icon-btn admin-icon-btn-danger" aria-label="Delete product">
                                    <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 7h12M9 7V4h6v3m-7 4v6m4-6v6m4-10v12a1 1 0 01-1 1H8a1 1 0 01-1-1V7h10z" />
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="py-8 text-center text-black/55">
                        No products found.
                    </td>
                </tr>
            @endforelse
            <tr id="admin-products-no-match-row" style="display: none;">
                <td colspan="8" class="py-8 text-center text-black/55">
                    No products found for this search.
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</section>

<section id="admin-product-edit-modal" class="admin-modal-shell" style="display: none;">
    <div class="admin-modal-backdrop"></div>
    <div class="admin-modal-card">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="font-mono text-2xl leading-none text-black">Edit Product</h2>
            <button id="admin-product-edit-close" type="button" class="admin-action-btn admin-action-danger rounded-xl px-3 py-2 text-xs">
                Close
            </button>
        </div>

        @if ($errors->productUpdate->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                Please fix the highlighted fields and try updating again.
            </div>
        @endif

        <form id="admin-product-edit-form" method="POST" action="{{ $oldEditAction }}" class="grid gap-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="edit_product_id" id="admin-product-edit-id" value="{{ $oldEditProductId }}">

            <div>
                <label for="admin-product-edit-name" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-black/55">
                    Product Name
                </label>
                <input
                    id="admin-product-edit-name"
                    type="text"
                    name="product_name"
                    value="{{ old('product_name') }}"
                    class="admin-product-input @if ($errors->productUpdate->has('product_name')) admin-product-input-error @endif"
                    placeholder="Enter product name"
                    required
                    maxlength="255"
                />
                @if ($errors->productUpdate->has('product_name'))
                    <p class="mt-2 text-sm text-rose-700">{{ $errors->productUpdate->first('product_name') }}</p>
                @endif
            </div>

            <div>
                <label for="admin-product-edit-description" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-black/55">
                    Description
                </label>
                <textarea
                    id="admin-product-edit-description"
                    name="description"
                    rows="4"
                    class="admin-product-input @if ($errors->productUpdate->has('description')) admin-product-input-error @endif"
                    placeholder="Product description (optional)"
                    maxlength="4000"
                >{{ old('description') }}</textarea>
                @if ($errors->productUpdate->has('description'))
                    <p class="mt-2 text-sm text-rose-700">{{ $errors->productUpdate->first('description') }}</p>
                @endif
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label for="admin-product-edit-stock" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-black/55">
                        Stock Quantity
                    </label>
                    <input
                        id="admin-product-edit-stock"
                        type="number"
                        name="stock_qty"
                        value="{{ old('stock_qty') }}"
                        class="admin-product-input @if ($errors->productUpdate->has('stock_qty')) admin-product-input-error @endif"
                        placeholder="0"
                        min="0"
                        required
                    />
                    @if ($errors->productUpdate->has('stock_qty'))
                        <p class="mt-2 text-sm text-rose-700">{{ $errors->productUpdate->first('stock_qty') }}</p>
                    @endif
                </div>

                <div>
                    <label for="admin-product-edit-price" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-black/55">
                        Price
                    </label>
                    <input
                        id="admin-product-edit-price"
                        type="number"
                        name="price"
                        value="{{ old('price') }}"
                        class="admin-product-input @if ($errors->productUpdate->has('price')) admin-product-input-error @endif"
                        placeholder="0.00"
                        min="0"
                        step="0.01"
                        required
                    />
                    @if ($errors->productUpdate->has('price'))
                        <p class="mt-2 text-sm text-rose-700">{{ $errors->productUpdate->first('price') }}</p>
                    @endif
                </div>
            </div>

            <div class="flex justify-end">
                <button type="submit" class="admin-action-btn admin-action-success rounded-xl px-4 py-2 text-sm">
                    Update Product
                </button>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const createCard = document.getElementById('admin-product-create-card');
    const openCreateBtn = document.getElementById('admin-products-open-create');
    const closeCreateBtn = document.getElementById('admin-products-close-create');
    const hasCreateErrors = @json($errors->productCreate->any());

    const showCreateForm = () => {
        if (createCard) {
            createCard.style.display = '';
        }
    };

    const hideCreateForm = () => {
        if (createCard) {
            createCard.style.display = 'none';
        }
    };

    if (openCreateBtn) {
        openCreateBtn.addEventListener('click', showCreateForm);
    }

    if (closeCreateBtn) {
        closeCreateBtn.addEventListener('click', hideCreateForm);
    }

    if (hasCreateErrors) {
        showCreateForm();
    }

    const input = document.getElementById('admin-products-live-search');
    const tableBody = document.getElementById('admin-products-table-body');
    const noMatchRow = document.getElementById('admin-products-no-match-row');

    if (input && tableBody && noMatchRow) {
        const dataRows = Array.from(tableBody.querySelectorAll('tr')).filter((row) => {
            return row.id !== 'admin-products-no-match-row' && row.hasAttribute('data-search-text');
        });

        const applyFilter = () => {
            if (dataRows.length === 0) {
                noMatchRow.style.display = 'none';
                return;
            }

            const query = input.value.trim().toLowerCase();
            let visibleCount = 0;

            dataRows.forEach((row) => {
                const haystack = row.getAttribute('data-search-text') || '';
                const isMatch = query === '' || haystack.includes(query);

                row.style.display = isMatch ? '' : 'none';
                if (isMatch) {
                    visibleCount += 1;
                }
            });

            noMatchRow.style.display = visibleCount === 0 ? '' : 'none';
        };

        input.addEventListener('input', applyFilter);
        applyFilter();
    }

    const editModal = document.getElementById('admin-product-edit-modal');
    const editForm = document.getElementById('admin-product-edit-form');
    const editIdInput = document.getElementById('admin-product-edit-id');
    const editNameInput = document.getElementById('admin-product-edit-name');
    const editDescriptionInput = document.getElementById('admin-product-edit-description');
    const editStockInput = document.getElementById('admin-product-edit-stock');
    const editPriceInput = document.getElementById('admin-product-edit-price');
    const closeEditBtn = document.getElementById('admin-product-edit-close');
    const hasUpdateErrors = @json($errors->productUpdate->any());
    const oldEditProductId = @json($oldEditProductId);

    const openEditModal = () => {
        if (editModal) {
            editModal.style.display = 'flex';
            document.body.classList.add('admin-modal-open');
        }
    };

    const closeEditModal = () => {
        if (editModal) {
            editModal.style.display = 'none';
            document.body.classList.remove('admin-modal-open');
        }
    };

    document.querySelectorAll('.admin-product-edit-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            if (!row || !editForm || !editIdInput || !editNameInput || !editDescriptionInput || !editStockInput || !editPriceInput) {
                return;
            }

            const productId = row.getAttribute('data-product-id') || '';
            const updateUrl = row.getAttribute('data-update-url') || '#';

            editForm.setAttribute('action', updateUrl);
            editIdInput.value = productId;
            editNameInput.value = row.getAttribute('data-product-name') || '';
            editDescriptionInput.value = row.getAttribute('data-product-description') || '';
            editStockInput.value = row.getAttribute('data-product-stock') || '0';
            editPriceInput.value = row.getAttribute('data-product-price') || '0.00';

            openEditModal();
        });
    });

    if (closeEditBtn) {
        closeEditBtn.addEventListener('click', closeEditModal);
    }

    if (editModal) {
        editModal.addEventListener('click', (event) => {
            if (event.target.classList.contains('admin-modal-shell') || event.target.classList.contains('admin-modal-backdrop')) {
                closeEditModal();
            }
        });
    }

    if (hasUpdateErrors && oldEditProductId !== null) {
        openEditModal();
    }
});
</script>
@endsection
