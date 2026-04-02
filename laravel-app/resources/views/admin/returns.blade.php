@extends('admin.layout')

@section('admin-content')
@php
    $returns = [
        ['return_id' => 'RET001', 'order_id' => 'ORD001', 'user_id' => 'USR001', 'return_reason' => 'Product damaged during shipping', 'return_date' => '2026-03-03', 'status' => 'Returned Successfully'],
        ['return_id' => 'RET002', 'order_id' => 'ORD002', 'user_id' => 'USR002', 'return_reason' => 'Wrong item received', 'return_date' => '2026-03-06', 'status' => 'In Progress'],
        ['return_id' => 'RET003', 'order_id' => 'ORD003', 'user_id' => 'USR003', 'return_reason' => "Product doesn't match description", 'return_date' => '2026-03-08', 'status' => 'In Progress'],
        ['return_id' => 'RET004', 'order_id' => 'ORD005', 'user_id' => 'USR005', 'return_reason' => 'Changed mind about purchase', 'return_date' => '2026-03-09', 'status' => 'Rejected'],
        ['return_id' => 'RET005', 'order_id' => 'ORD001', 'user_id' => 'USR001', 'return_reason' => 'Quality not as expected', 'return_date' => '2026-03-10', 'status' => 'In Progress'],
    ];
@endphp

<section class="space-y-2">
    <h1 class="font-mono text-[42px] leading-[0.95] tracking-[-0.02em] text-black">Returns Management</h1>
    <p class="font-roboto text-[15px] text-black/70">UI-only return review table with status badges and actions.</p>
</section>

<section class="dashboard-solid-card overflow-hidden rounded-2xl">
    <header class="border-b border-black/10 px-5 py-4">
        <label class="admin-search-wrap max-w-md">
            <svg class="h-4 w-4 text-black/40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z" />
            </svg>
            <input type="text" class="admin-search-input" placeholder="Search by return ID, order ID, user ID, or status..." />
        </label>
    </header>

    <div class="overflow-x-auto">
        <table class="admin-table min-w-[1080px]">
            <thead>
            <tr>
                <th>Return ID</th>
                <th>Order ID</th>
                <th>User ID</th>
                <th>Return Reason</th>
                <th>Return Date</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($returns as $return)
                @php
                    $statusClass = 'admin-status-live';
                    if ($return['status'] === 'Returned Successfully') {
                        $statusClass = 'admin-status-success';
                    } elseif ($return['status'] === 'Rejected') {
                        $statusClass = 'admin-status-danger';
                    }
                @endphp
                <tr>
                    <td class="font-semibold text-black">{{ $return['return_id'] }}</td>
                    <td>{{ $return['order_id'] }}</td>
                    <td>{{ $return['user_id'] }}</td>
                    <td class="max-w-md">{{ $return['return_reason'] }}</td>
                    <td>{{ $return['return_date'] }}</td>
                    <td><span class="admin-status-badge {{ $statusClass }}">{{ $return['status'] }}</span></td>
                    <td class="text-right">
                        <button type="button" class="admin-action-btn admin-action-info">Review</button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
