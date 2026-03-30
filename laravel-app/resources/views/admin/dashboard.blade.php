<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Admin Dashboard - Zippd</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-50 text-slate-900 antialiased">
    <main class="mx-auto max-w-5xl px-5 py-8 md:px-8">
        <header class="mb-8 flex flex-wrap items-start justify-between gap-4 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm">
            <div>
                <p class="text-xs font-semibold uppercase tracking-[0.2em] text-slate-500">Admin Panel</p>
                <h1 class="mt-2 text-3xl font-semibold">Welcome, {{ $adminInfo['full_name'] ?? 'Admin' }}</h1>
                <p class="mt-1 text-sm text-slate-600">
                    Admin ID: {{ $adminInfo['admin_id'] ?? '-' }} | Email: {{ $adminInfo['email'] ?? '-' }}
                </p>
            </div>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="rounded-lg bg-black px-4 py-2 text-sm font-semibold text-white transition hover:opacity-90">
                    Logout
                </button>
            </form>
        </header>

        <section class="rounded-xl border border-slate-200 bg-white p-6 shadow-sm">
            <p class="text-sm text-slate-600">
                Admin authentication is active. You can now build the dashboard UI and features here.
            </p>
        </section>
    </main>

    @include('partials.mssql-console-debug')
</body>
</html>
