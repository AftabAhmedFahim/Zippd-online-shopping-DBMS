@extends('admin.layout')

@section('admin-content')
@php
    $users = [
        [
            'user_id' => 'USR001',
            'full_name' => 'John Smith',
            'email' => 'john.smith@email.com',
            'phone' => '+1-555-0101',
            'address' => '123 Main St, New York, NY 10001',
            'created_at' => '2025-01-15',
            'updated_at' => '2026-02-20',
            'is_restricted' => false,
        ],
        [
            'user_id' => 'USR002',
            'full_name' => 'Sarah Johnson',
            'email' => 'sarah.j@email.com',
            'phone' => '+1-555-0102',
            'address' => '456 Oak Ave, Los Angeles, CA 90001',
            'created_at' => '2025-02-20',
            'updated_at' => '2026-03-01',
            'is_restricted' => false,
        ],
        [
            'user_id' => 'USR003',
            'full_name' => 'Michael Brown',
            'email' => 'm.brown@email.com',
            'phone' => '+1-555-0103',
            'address' => '789 Pine Rd, Chicago, IL 60601',
            'created_at' => '2025-03-10',
            'updated_at' => '2026-03-05',
            'is_restricted' => true,
        ],
        [
            'user_id' => 'USR004',
            'full_name' => 'Emily Davis',
            'email' => 'emily.davis@email.com',
            'phone' => '+1-555-0104',
            'address' => '321 Elm St, Houston, TX 77001',
            'created_at' => '2025-04-05',
            'updated_at' => '2026-03-08',
            'is_restricted' => false,
        ],
        [
            'user_id' => 'USR005',
            'full_name' => 'David Wilson',
            'email' => 'd.wilson@email.com',
            'phone' => '+1-555-0105',
            'address' => '654 Maple Dr, Phoenix, AZ 85001',
            'created_at' => '2025-05-12',
            'updated_at' => '2026-03-09',
            'is_restricted' => false,
        ],
    ];
@endphp

<section class="space-y-2">
    <h1 class="font-mono text-[42px] leading-[0.95] tracking-[-0.02em] text-black">Users Management</h1>
    <p class="font-roboto text-[15px] text-black/70">UI-only table and controls for admin user moderation.</p>
</section>

<section class="dashboard-solid-card overflow-hidden rounded-2xl">
    <header class="border-b border-black/10 px-5 py-4">
        <label class="admin-search-wrap max-w-md">
            <svg class="h-4 w-4 text-black/40" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35M10.5 18a7.5 7.5 0 110-15 7.5 7.5 0 010 15z" />
            </svg>
            <input type="text" class="admin-search-input" placeholder="Search by name, address, phone, or date..." />
        </label>
    </header>

    <div class="overflow-x-auto">
        <table class="admin-table min-w-[1080px]">
            <thead>
            <tr>
                <th>User ID</th>
                <th>Full Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Address</th>
                <th>Created At</th>
                <th>Updated At</th>
                <th>Status</th>
                <th class="text-right">Actions</th>
            </tr>
            </thead>
            <tbody>
            @foreach ($users as $user)
                <tr>
                    <td class="font-semibold text-black">{{ $user['user_id'] }}</td>
                    <td>{{ $user['full_name'] }}</td>
                    <td>{{ $user['email'] }}</td>
                    <td>{{ $user['phone'] }}</td>
                    <td class="max-w-xs truncate">{{ $user['address'] }}</td>
                    <td>{{ $user['created_at'] }}</td>
                    <td>{{ $user['updated_at'] }}</td>
                    <td>
                        <span class="admin-status-badge {{ $user['is_restricted'] ? 'admin-status-danger' : 'admin-status-success' }}">
                            {{ $user['is_restricted'] ? 'Restricted' : 'Active' }}
                        </span>
                    </td>
                    <td class="text-right">
                        <button type="button"
                                class="admin-action-btn {{ $user['is_restricted'] ? 'admin-action-success' : 'admin-action-danger' }}">
                            {{ $user['is_restricted'] ? 'Activate' : 'Restrict' }}
                        </button>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</section>
@endsection
