@extends('admin.layout')

@section('admin-content')
@php
    $stats = [
        ['title' => 'Total Users', 'value' => '1,234', 'tone' => 'cyan', 'icon' => 'user'],
        ['title' => 'Total Categories', 'value' => '45', 'tone' => 'green', 'icon' => 'list'],
        ['title' => 'Total Products', 'value' => '892', 'tone' => 'blue', 'icon' => 'box'],
        ['title' => 'Total Orders', 'value' => '3,567', 'tone' => 'rose', 'icon' => 'cart'],
    ];

    $recentOrders = [
        ['id' => 'ORD3568', 'user' => 'John Smith', 'amount' => '$249.97', 'status' => 'Processing', 'time' => '2 minutes ago'],
        ['id' => 'ORD3567', 'user' => 'Sarah Johnson', 'amount' => '$579.98', 'status' => 'Confirmed', 'time' => '15 minutes ago'],
        ['id' => 'ORD3566', 'user' => 'Michael Brown', 'amount' => '$129.99', 'status' => 'Processing', 'time' => '32 minutes ago'],
        ['id' => 'ORD3565', 'user' => 'Emily Davis', 'amount' => '$84.98', 'status' => 'Confirmed', 'time' => '1 hour ago'],
    ];

    $newUsers = [
        ['id' => 'USR1235', 'name' => 'Alex Thompson', 'email' => 'alex.t@email.com', 'time' => '5 minutes ago'],
        ['id' => 'USR1234', 'name' => 'Jessica Martinez', 'email' => 'jessica.m@email.com', 'time' => '28 minutes ago'],
        ['id' => 'USR1233', 'name' => 'David Lee', 'email' => 'david.lee@email.com', 'time' => '1 hour ago'],
        ['id' => 'USR1232', 'name' => 'Sophie Chen', 'email' => 'sophie.c@email.com', 'time' => '2 hours ago'],
    ];
@endphp

<section class="space-y-2">
    <h1 class="font-mono text-[42px] leading-[0.95] tracking-[-0.02em] text-black">Admin Dashboard</h1>
    <p class="font-roboto text-[15px] text-black/70">
        Welcome back, {{ $adminInfo['full_name'] ?? 'Admin' }}. This is the UI preview state.
    </p>
</section>

<section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
    @foreach ($stats as $stat)
        <article class="dashboard-solid-card admin-kpi-card rounded-2xl p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <p class="font-roboto text-xs font-semibold uppercase tracking-wide text-black/60">{{ $stat['title'] }}</p>
                    <p class="font-mono mt-3 text-4xl leading-none">{{ $stat['value'] }}</p>
                </div>
                <div class="admin-kpi-icon admin-kpi-icon-{{ $stat['tone'] }}">
                    @if ($stat['icon'] === 'user')
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5V4H2v16h5m10 0v-2a4 4 0 00-4-4H11a4 4 0 00-4 4v2m10 0H7m8-11a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    @elseif ($stat['icon'] === 'list')
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
                        </svg>
                    @elseif ($stat['icon'] === 'box')
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7L12 3 4 7m16 0v10l-8 4-8-4V7m16 0-8 4m-8-4 8 4m0 0v10" />
                        </svg>
                    @else
                        <svg class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l2.5 12.5a2 2 0 002 1.5h8.5a2 2 0 001.95-1.55L22 7H8m2 14a1 1 0 100 2 1 1 0 000-2zm9 0a1 1 0 100 2 1 1 0 000-2z" />
                        </svg>
                    @endif
                </div>
            </div>
        </article>
    @endforeach
</section>

<section class="grid gap-6 lg:grid-cols-2">
    <article class="dashboard-solid-card rounded-2xl">
        <header class="flex items-center justify-between border-b border-black/10 px-6 py-5">
            <h2 class="font-mono text-2xl leading-none tracking-tight">Recent Orders</h2>
            <span class="admin-status-badge admin-status-live">Live</span>
        </header>
        <div class="space-y-3 px-6 py-6">
            @foreach ($recentOrders as $order)
                <div class="admin-list-card admin-list-card-rose">
                    <div>
                        <div class="flex items-center gap-2">
                            <p class="font-roboto text-sm font-semibold text-black">{{ $order['id'] }}</p>
                            <span class="admin-status-badge {{ $order['status'] === 'Confirmed' ? 'admin-status-success' : 'admin-status-live' }}">
                                {{ $order['status'] }}
                            </span>
                        </div>
                        <p class="font-roboto mt-1 text-sm text-black/70">{{ $order['user'] }}</p>
                        <p class="font-roboto text-xs text-black/50">{{ $order['time'] }}</p>
                    </div>
                    <p class="font-mono text-sm font-semibold text-rose-700">{{ $order['amount'] }}</p>
                </div>
            @endforeach
        </div>
    </article>

    <article class="dashboard-solid-card rounded-2xl">
        <header class="flex items-center justify-between border-b border-black/10 px-6 py-5">
            <h2 class="font-mono text-2xl leading-none tracking-tight">New Users</h2>
            <span class="admin-status-badge admin-status-live">Live</span>
        </header>
        <div class="space-y-3 px-6 py-6">
            @foreach ($newUsers as $user)
                <div class="admin-list-card admin-list-card-cyan">
                    <div class="flex items-center gap-3">
                        <span class="admin-avatar-mini">{{ strtoupper(substr($user['name'], 0, 1)) }}</span>
                        <div>
                            <p class="font-roboto text-sm font-semibold text-black">{{ $user['name'] }}</p>
                            <p class="font-roboto text-xs text-black/70">{{ $user['email'] }}</p>
                            <p class="font-roboto text-xs text-black/50">{{ $user['time'] }}</p>
                        </div>
                    </div>
                    <span class="admin-status-badge admin-status-outline">{{ $user['id'] }}</span>
                </div>
            @endforeach
        </div>
    </article>
</section>
@endsection
