@extends('admin.layout')

@section('admin-content')
@php
    $currentQuery = trim((string) ($initialSearchQuery ?? ''));
    $oldEditReturnId = old('edit_return_id', session('admin_returns_open_edit_id'));
    $oldEditReturnId = is_numeric($oldEditReturnId) ? (int) $oldEditReturnId : null;
    $oldEditAction = $oldEditReturnId !== null
        ? route('admin.returns.update', ['returnId' => $oldEditReturnId])
        : '#';

    $statusOptions = [
        'pending' => 'Pending',
        'in progress' => 'In Progress',
        'approved' => 'Approved',
        'returned successfully' => 'Returned Successfully',
        'rejected' => 'Rejected',
        'cancelled' => 'Cancelled',
    ];

    $refundLabels = [
        'bkash' => 'bKash',
        'nagad' => 'Nagad',
        'voucher' => 'Voucher',
    ];

    $formatDateTime = static function ($value): string {
        if ($value === null || $value === '') {
            return '-';
        }

        try {
            return \Carbon\Carbon::parse((string) $value)->format('M d, Y h:i A');
        } catch (\Throwable $exception) {
            return (string) $value;
        }
    };
@endphp

<section class="space-y-2">
    <h1 class="font-mono text-[42px] leading-[0.95] tracking-[-0.02em] text-black">Returns Management</h1>
    <p class="font-roboto text-[15px] text-black/70">Review all return details, including customer messages, request dates, and latest modifications.</p>
</section>

@if (session('admin_returns_success'))
    <section class="rounded-2xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-800">
        {{ session('admin_returns_success') }}
    </section>
@endif

@if (session('admin_returns_error'))
    <section class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
        {{ session('admin_returns_error') }}
    </section>
@endif

@if ($errors->returnUpdate->any())
    <section class="rounded-2xl border border-rose-200 bg-rose-50 px-5 py-4 text-sm text-rose-700">
        Please choose a valid status and submit again.
    </section>
@endif

<section class="dashboard-solid-card overflow-hidden rounded-2xl">
    <header class="border-b border-black/10 px-5 py-4">
        <form method="GET" action="{{ route('admin.returns') }}" class="max-w-md">
            <label class="admin-search-wrap">
                <svg class="h-4 w-4 text-black/40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z" />
                </svg>
                <input
                    type="text"
                    name="q"
                    value="{{ $currentQuery }}"
                    class="admin-search-input"
                    placeholder="Search by return ID, order ID, user, product, message, or status..."
                />
            </label>
        </form>
    </header>

    <div class="overflow-x-auto">
        <table class="admin-table">
            <thead>
            <tr>
                <th>Return ID</th>
                <th>Order / Product</th>
                <th>Customer</th>
                <th>Reason</th>
                <th>Message</th>
                <th>Refund To</th>
                <th>Request Date</th>
                <th>Last Modified</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @forelse ($returns as $return)
                @php
                    $normalizedStatus = strtolower(trim((string) ($return['status'] ?? 'pending')));
                    $statusClass = match ($normalizedStatus) {
                        'approved', 'returned successfully' => 'admin-status-success',
                        'rejected', 'cancelled' => 'admin-status-danger',
                        'in progress' => 'admin-status-info',
                        'pending' => 'admin-status-live',
                        default => 'admin-status-outline',
                    };
                    $statusLabel = $statusOptions[$normalizedStatus] ?? ucwords($normalizedStatus);
                    $refundValue = strtolower(trim((string) ($return['refund_to'] ?? '')));
                    $refundLabel = $refundLabels[$refundValue] ?? ($refundValue !== '' ? ucfirst($refundValue) : 'Not set');
                    $returnCode = trim((string) ($return['return_code'] ?? ''));
                    $displayReturnId = $returnCode !== '' ? $returnCode : '#' . (int) ($return['return_id'] ?? 0);
                @endphp
                <tr
                    data-return-id="{{ (int) ($return['return_id'] ?? 0) }}"
                    data-update-url="{{ route('admin.returns.update', ['returnId' => $return['return_id']]) }}"
                    data-return-status="{{ $normalizedStatus }}"
                >
                    <td class="font-semibold text-black">
                        <div>{{ $displayReturnId }}</div>
                        <div class="text-xs font-normal text-black/55">DB ID: {{ (int) ($return['return_id'] ?? 0) }}</div>
                    </td>
                    <td>
                        <div class="font-semibold text-black">Order #{{ (int) ($return['order_id'] ?? 0) }}</div>
                        <div class="text-xs text-black/55">Product #{{ (int) ($return['product_id'] ?? 0) }}</div>
                        <div class="text-xs text-black/55">{{ $return['product_name'] ?? 'Unknown Product' }}</div>
                        <div class="text-xs text-black/55">Qty: {{ (int) ($return['quantity'] ?? 0) }}</div>
                    </td>
                    <td>
                        <div class="font-semibold text-black">{{ $return['user_name'] ?? 'Unknown User' }}</div>
                        <div class="text-xs text-black/55">ID: {{ (int) ($return['user_id'] ?? 0) }}</div>
                        <div class="text-xs text-black/55">{{ $return['user_email'] ?? '-' }}</div>
                    </td>
                    <td class="max-w-[240px]">{{ $return['return_reason'] ?? '-' }}</td>
                    <td class="max-w-[280px] whitespace-pre-line break-words">{{ $return['comments'] ?? 'No message provided.' }}</td>
                    <td>{{ $refundLabel }}</td>
                    <td>{{ $formatDateTime($return['return_date'] ?? null) }}</td>
                    <td>{{ $formatDateTime($return['updated_at'] ?? null) }}</td>
                    <td>
                        <span class="admin-status-badge {{ $statusClass }}">{{ $statusLabel }}</span>
                    </td>
                    <td class="text-right">
                        <button type="button" class="admin-action-btn admin-action-info admin-return-edit-btn">Review</button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="10" class="py-8 text-center text-black/55">
                        No return requests found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>

<section id="admin-return-edit-modal" class="admin-modal-shell" style="display: none;">
    <div class="admin-modal-backdrop"></div>
    <div class="admin-modal-card">
        <div class="mb-4 flex items-center justify-between gap-3">
            <h2 class="font-mono text-2xl leading-none text-black">Review Return</h2>
            <button id="admin-return-edit-close" type="button" class="admin-action-btn admin-action-danger rounded-xl px-3 py-2 text-xs">
                Close
            </button>
        </div>

        @if ($errors->returnUpdate->any())
            <div class="mb-4 rounded-xl border border-rose-200 bg-rose-50 px-4 py-3 text-sm text-rose-700">
                Please fix the highlighted field and try again.
            </div>
        @endif

        <form id="admin-return-edit-form" method="POST" action="{{ $oldEditAction }}" class="grid gap-4">
            @csrf
            @method('PATCH')
            <input type="hidden" name="edit_return_id" id="admin-return-edit-id" value="{{ $oldEditReturnId }}">
            <input type="hidden" name="return_q" value="{{ $currentQuery }}">

            <div>
                <label for="admin-return-edit-status" class="mb-2 block text-xs font-semibold uppercase tracking-[0.16em] text-black/55">
                    Return Status
                </label>
                <select
                    id="admin-return-edit-status"
                    name="status"
                    class="admin-product-input @if ($errors->returnUpdate->has('status')) admin-product-input-error @endif"
                >
                    @foreach($statusOptions as $statusValue => $statusLabel)
                        <option value="{{ $statusValue }}" @selected(old('status') === $statusValue)>{{ $statusLabel }}</option>
                    @endforeach
                </select>
                @if ($errors->returnUpdate->has('status'))
                    <p class="mt-2 text-sm text-rose-700">{{ $errors->returnUpdate->first('status') }}</p>
                @endif
            </div>

            <div class="flex justify-end">
                <button type="submit" class="admin-action-btn admin-action-success rounded-xl px-4 py-2 text-sm">
                    Update Return
                </button>
            </div>
        </form>
    </div>
</section>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const editModal = document.getElementById('admin-return-edit-modal');
    const editForm = document.getElementById('admin-return-edit-form');
    const editIdInput = document.getElementById('admin-return-edit-id');
    const editStatusInput = document.getElementById('admin-return-edit-status');
    const closeEditBtn = document.getElementById('admin-return-edit-close');
    const hasUpdateErrors = @json($errors->returnUpdate->any());
    const oldEditReturnId = @json($oldEditReturnId);

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

    document.querySelectorAll('.admin-return-edit-btn').forEach((button) => {
        button.addEventListener('click', () => {
            const row = button.closest('tr');
            if (!row || !editForm || !editIdInput || !editStatusInput) {
                return;
            }

            const returnId = row.getAttribute('data-return-id') || '';
            const updateUrl = row.getAttribute('data-update-url') || '#';
            const returnStatus = row.getAttribute('data-return-status') || 'pending';

            editForm.setAttribute('action', updateUrl);
            editIdInput.value = returnId;
            editStatusInput.value = returnStatus;

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

    if (hasUpdateErrors && oldEditReturnId !== null) {
        openEditModal();
    }
});
</script>
@endsection
