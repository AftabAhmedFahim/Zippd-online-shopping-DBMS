@extends('admin.layout')

@section('admin-content')
@php
    $currentQuery = trim((string) ($initialSearchQuery ?? ''));
    $oldEditOrderId = old('edit_order_id', session('admin_orders_open_edit_id'));
    $oldEditOrderId = is_numeric($oldEditOrderId) ? (int) $oldEditOrderId : null;
    $oldEditAction = $oldEditOrderId !== null
        ? route('admin.orders.update', ['orderId' => $oldEditOrderId])
        : '#';
@endphp

<section class="space-y-2">
    <h1 class="font-mono text-[42px] leading-[0.95] tracking-[-0.02em] text-black">Orders Management</h1>
    <p class="font-roboto text-[15px] text-black/70">Manage all customer orders and update delivery states.</p>
</section>

@if (session('admin_orders_success'))
    <section class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
        {{ session('admin_orders_success') }}
    </section>
@endif

@if (session('admin_orders_error'))
    <section class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
        {{ session('admin_orders_error') }}
    </section>
@endif

@if ($errors->orderUpdate->any() || $errors->orderStatusUpdate->any() || $errors->orderPaymentUpdate->any())
    <section class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
        Please choose valid values and submit again.
    </section>
@endif

<section class="dashboard-solid-card overflow-hidden rounded-2xl">
    <header class="border-b border-black/10 px-5 py-4">
        <form method="GET" action="{{ route('admin.orders') }}" class="max-w-md">
            <label class="admin-search-wrap">
                <svg class="h-4 w-4 text-black/40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z" />
                </svg>
                <input
                    type="text"
                    name="q"
                    value="{{ $currentQuery }}"
                    class="admin-search-input"
                    placeholder="Search by order ID, user, email, status, or payment..."
                />
            </label>
        </form>
    </header>

    <div class="overflow-x-auto">
        <table class="admin-table min-w-[1320px]">
            <thead>
            <tr>
                <th>Order ID</th>
                <th>User</th>
                <th>Order Date</th>
                <th>Items</th>
                <th>Status</th>
                <th>Shipping Address</th>
                <th>Total Amount</th>
                <th>Payment</th>
                <th class="text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($orders as $order)
                @php
                    $orderStatus = strtolower(trim((string) ($order['order_status'] ?? 'pending')));
                    $statusClass = 'admin-status-live';
                    if ($orderStatus === 'delivered') {
                        $statusClass = 'admin-status-success';
                    } elseif ($orderStatus === 'confirmed') {
                        $statusClass = 'admin-status-success';
                    } elseif ($orderStatus === 'pending') {
                        $statusClass = 'admin-status-danger';
                    } elseif ($orderStatus === 'shipped') {
                        $statusClass = 'admin-status-info';
                    }

                    $isPaid = (bool) ($order['is_paid'] ?? false);
                    $paymentStatus = strtolower((string) ($order['payment_status'] ?? ($isPaid ? 'paid' : 'pending')));
                    $paymentMethod = (string) ($order['payment_method'] ?? '');
                    $statusLabel = ucfirst($orderStatus);
                @endphp
                <tr
                    data-order-id="{{ (int) ($order['order_id'] ?? 0) }}"
                    data-update-url="{{ route('admin.orders.update', ['orderId' => $order['order_id']]) }}"
                    data-order-status="{{ $orderStatus }}"
                    data-is-paid="{{ $isPaid ? '1' : '0' }}"
                >
                    <td class="font-semibold text-black">{{ (int) ($order['order_id'] ?? 0) }}</td>
                    <td>
                        <div class="font-semibold text-black">{{ $order['user_name'] ?? 'Unknown User' }}</div>
                        <div class="text-xs text-black/55">ID: {{ (int) ($order['user_id'] ?? 0) }}</div>
                        <div class="text-xs text-black/55">{{ $order['user_email'] ?? '-' }}</div>
                    </td>
                    <td>{{ $order['order_date'] ?? '-' }}</td>
                    <td>{{ (int) ($order['total_items'] ?? 0) }}</td>
                    <td>
                        <span class="admin-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="max-w-xs">{{ $order['shipping_address'] ?? '-' }}</td>
                    <td>${{ number_format((float) ($order['total_amount'] ?? 0), 2) }}</td>
                    <td>
                        <span class="admin-status-badge {{ $isPaid ? 'admin-status-success' : 'admin-status-danger' }}">
                            {{ $isPaid ? 'Paid' : 'Unpaid' }}
                        </span>
                        <div class="mt-1 text-[11px] uppercase tracking-[0.12em] text-black/55">
                            {{ $paymentMethod !== '' ? str_replace('_', ' ', $paymentMethod) : 'method: n/a' }}
                            @if($paymentStatus !== '')
                                | {{ $paymentStatus }}
                            @endif
                        </div>
                    </td>
                    <td class="text-right">
                        <button type="button" class="admin-icon-btn admin-icon-btn-edit admin-order-edit-btn" aria-label="Edit order">
                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2M4 21h4l10.5-10.5a1.5 1.5 0 00-2.12-2.12L5.88 18.88 4 21z" />
                            </svg>
                        </button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9" class="py-8 text-center text-black/55">
                        No orders found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<section id="admin-order-edit-modal" class="admin-modal-shell" style="display: none;">
    <div class="admin-modal-backdrop"></div>
    <div class="admin-modal-card">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="font-mono text-2xl leading-none text-black">Edit Order</h2>
            <button id="admin-order-edit-close" type="button" class="admin-action-btn admin-action-danger rounded-xl px-3 py-2 text-xs">
                Close
            </button>
        </div>

        @if ($errors->orderUpdate->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                Please fix the highlighted fields and try again.
            </div>
        @endif

        <form id="admin-order-edit-form" method="POST" action="{{ $oldEditAction }}" class="grid gap-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="edit_order_id" id="admin-order-edit-id" value="{{ $oldEditOrderId }}">
            <input type="hidden" name="return_q" value="{{ $currentQuery }}">

            <div>
                <label for="admin-order-edit-status" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-black/55">
                    Order Status
                </label>
                <select
                    id="admin-order-edit-status"
                    name="order_status"
                    class="admin-product-input @if ($errors->orderUpdate->has('order_status')) admin-product-input-error @endif"
                >
                    <option value="pending" @selected(old('order_status') === 'pending')>Pending</option>
                    <option value="confirmed" @selected(old('order_status') === 'confirmed')>Confirmed</option>
                    <option value="shipped" @selected(old('order_status') === 'shipped')>Shipped</option>
                    <option value="delivered" @selected(old('order_status') === 'delivered')>Delivered</option>
                </select>
                @if ($errors->orderUpdate->has('order_status'))
                    <p class="mt-2 text-sm text-rose-700">{{ $errors->orderUpdate->first('order_status') }}</p>
                @endif
            </div>

            <input type="hidden" name="is_paid" id="admin-order-edit-payment" value="{{ old('is_paid', '0') }}">

            <div class="flex justify-end">
                <button type="submit" class="admin-action-btn admin-action-success rounded-xl px-4 py-2 text-sm">
                    Update Order
                </button>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('admin-order-edit-modal');
    const editForm = document.getElementById('admin-order-edit-form');
    const editIdInput = document.getElementById('admin-order-edit-id');
    const editStatusInput = document.getElementById('admin-order-edit-status');
    const editPaymentInput = document.getElementById('admin-order-edit-payment');
    const closeEditBtn = document.getElementById('admin-order-edit-close');
    const hasUpdateErrors = @json($errors->orderUpdate->any());
    const oldEditOrderId = @json($oldEditOrderId);

    if (editModal && editModal.parentElement !== document.body) {
        document.body.appendChild(editModal);
    }

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

    document.querySelectorAll('.admin-order-edit-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            if (!row || !editForm || !editIdInput || !editStatusInput || !editPaymentInput) {
                return;
            }

            const orderId = row.getAttribute('data-order-id') || '';
            const updateUrl = row.getAttribute('data-update-url') || '#';
            const orderStatus = row.getAttribute('data-order-status') || 'pending';
            const isPaid = row.getAttribute('data-is-paid') || '0';

            editForm.setAttribute('action', updateUrl);
            editIdInput.value = orderId;
            editStatusInput.value = orderStatus;
            editPaymentInput.value = isPaid;

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

    if (hasUpdateErrors && oldEditOrderId !== null) {
        openEditModal();
    }
});
</script>
@endsection
