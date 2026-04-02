@extends('admin.layout')

@section('admin-content')
@php
    $orders = [
        ['order_id' => 'ORD001', 'user_id' => 'USR001', 'order_date' => '2026-03-01', 'order_status' => 'Delivered', 'shipping_address' => '123 Main St, New York, NY 10001', 'total_amount' => 249.97, 'is_paid' => true],
        ['order_id' => 'ORD002', 'user_id' => 'USR002', 'order_date' => '2026-03-05', 'order_status' => 'Processing', 'shipping_address' => '456 Oak Ave, Los Angeles, CA 90001', 'total_amount' => 579.98, 'is_paid' => true],
        ['order_id' => 'ORD003', 'user_id' => 'USR003', 'order_date' => '2026-03-07', 'order_status' => 'Shipped', 'shipping_address' => '789 Pine Rd, Chicago, IL 60601', 'total_amount' => 129.99, 'is_paid' => true],
        ['order_id' => 'ORD004', 'user_id' => 'USR004', 'order_date' => '2026-03-08', 'order_status' => 'Pending', 'shipping_address' => '321 Elm St, Houston, TX 77001', 'total_amount' => 84.98, 'is_paid' => false],
        ['order_id' => 'ORD005', 'user_id' => 'USR005', 'order_date' => '2026-03-09', 'order_status' => 'Processing', 'shipping_address' => '654 Maple Dr, Phoenix, AZ 85001', 'total_amount' => 329.99, 'is_paid' => true],
    ];
@endphp

<section class="space-y-2">
    <h1 class="font-mono text-[42px] leading-[0.95] tracking-[-0.02em] text-black">Orders Management</h1>
    <p class="font-roboto text-[15px] text-black/70">UI-only order tracking table and edit/delete action layout.</p>
</section>

<section class="dashboard-solid-card overflow-hidden rounded-2xl">
    <header class="border-b border-black/10 px-5 py-4">
        <label class="admin-search-wrap max-w-sm">
            <svg class="h-4 w-4 text-black/40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z" />
            </svg>
            <input type="text" class="admin-search-input" placeholder="Search by order ID, user ID, or status..." />
        </label>
    </header>

    <div class="overflow-x-auto">
        <table class="admin-table min-w-[1120px]">
            <thead>
            <tr>
                <th>Order ID</th>
                <th>User ID</th>
                <th>Order Date</th>
                <th>Status</th>
                <th>Shipping Address</th>
                <th>Total Amount</th>
                <th>Payment</th>
                <th class="text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($orders as $order)
                @php
                    $statusClass = 'admin-status-live';
                    if ($order['order_status'] === 'Delivered') {
                        $statusClass = 'admin-status-success';
                    } elseif ($order['order_status'] === 'Pending') {
                        $statusClass = 'admin-status-danger';
                    } elseif ($order['order_status'] === 'Shipped') {
                        $statusClass = 'admin-status-info';
                    }
                @endphp
                <tr>
                    <td class="font-semibold text-black">{{ $order['order_id'] }}</td>
                    <td>{{ $order['user_id'] }}</td>
                    <td>{{ $order['order_date'] }}</td>
                    <td>
                        <span class="admin-status-badge {{ $statusClass }}">{{ $order['order_status'] }}</span>
                    </td>
                    <td class="max-w-xs truncate">{{ $order['shipping_address'] }}</td>
                    <td>${{ number_format($order['total_amount'], 2) }}</td>
                    <td>
                        <span class="admin-status-badge {{ $order['is_paid'] ? 'admin-status-success' : 'admin-status-danger' }}">
                            {{ $order['is_paid'] ? 'Paid' : 'Unpaid' }}
                        </span>
                    </td>
                    <td class="text-right">
                        <div class="inline-flex items-center gap-2">
                            <button type="button" class="admin-icon-btn admin-icon-btn-edit" aria-label="Edit order">
                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5h2M4 21h4l10.5-10.5a1.5 1.5 0 00-2.12-2.12L5.88 18.88 4 21z" />
                                </svg>
                            </button>
                            <button type="button" class="admin-icon-btn admin-icon-btn-danger" aria-label="Delete order">
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
