<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin Login - Zippd</title>
    @vite(['resources/css/app.css', 'resources/css/dashboard.css', 'resources/css/admin.css', 'resources/js/app.js'])
</head>
<body class="dashboard-theme text-[#171717] antialiased">
<header class="topbar-animate border-b border-black/10">
    <div class="flex w-full items-center justify-between px-5 py-5 md:px-10 md:py-6 lg:px-14">
        <a href="/" class="font-display text-2xl leading-none tracking-tight md:text-[30px]">Zippd</a>
        <div class="flex items-center gap-3">
            <a href="{{ route('login') }}"
               class="sidebar-link rounded-xl bg-white/70 px-4 py-2 text-xs font-semibold uppercase tracking-wide text-black/80 hover:bg-white">
                User Login
            </a>
        </div>
    </div>
</header>

<main class="flex min-h-[calc(100vh-88px)] items-center justify-center px-5 py-12 md:px-10">
    <section class="dashboard-solid-card w-full max-w-[440px] rounded-2xl p-8 md:p-10">
        <h1 class="font-mono text-4xl leading-[0.95] tracking-tight text-black">Admin Sign In</h1>
        <p class="font-roboto mt-2 text-sm text-black/65">Use your admin credentials to access dashboard pages.</p>

        @if (session('status'))
            <div class="mt-5 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-700">
                {{ session('status') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" class="mt-6 space-y-5">
            @csrf
            <div>
                <label for="login_id" class="font-roboto mb-2 block text-sm font-semibold text-black">Admin Email</label>
                <input id="login_id"
                       type="text"
                       name="login_id"
                       value="{{ old('login_id') }}"
                       autocomplete="username"
                       required
                       autofocus
                       class="admin-auth-input @error('login_id') admin-auth-input-error @enderror" />
                @error('login_id')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="font-roboto mb-2 block text-sm font-semibold text-black">Password</label>
                <input id="password"
                       type="password"
                       name="password"
                       autocomplete="current-password"
                       required
                       class="admin-auth-input @error('password') admin-auth-input-error @enderror" />
                @error('password')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <label class="inline-flex items-center gap-2">
                <input id="remember_me" type="checkbox" class="rounded border-black/20 text-black focus:ring-black" name="remember">
                <span class="font-roboto text-sm text-black/70">Remember me</span>
            </label>

            <button type="submit" class="admin-auth-submit">Sign In As Admin</button>
        </form>
    </section>
</main>

@include('partials.mssql-console-debug')
</body>
</html>
