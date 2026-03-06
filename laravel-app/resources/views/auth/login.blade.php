<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login - Zippd</title>

    @vite(['resources/css/app.css', 'resources/css/auth.css'])
</head>
<body class="text-[#171717] antialiased">
    <header class="topbar-animate">
        <div class="flex w-full items-center justify-between px-5 py-5 md:px-10 md:py-6 lg:px-14">
            <a href="/" class="font-display text-2xl leading-none tracking-tight md:text-[30px]">Zippd</a>

            <div class="flex items-center gap-6">
                @auth
                    <a href="{{ route('dashboard') }}" class="rounded-full border border-black/20 px-5 py-2.5 text-sm font-semibold uppercase tracking-wide transition hover:border-black hover:bg-black hover:text-white">
                        Dashboard
                    </a>
                @endauth
            </div>
        </div>
    </header>

    <main class="flex items-center justify-center min-h-[calc(100vh-80px)] py-12">
        <div class="w-full max-w-[420px] px-5">
            <div class="auth-container p-8 md:p-10 rounded-2xl">
                <!-- Session Status -->
                @if (session('status'))
                    <div class="mb-4 p-4 bg-green-50 border border-green-200 rounded-lg text-sm text-green-700">
                        {{ session('status') }}
                    </div>
                @endif

                <h1 class="font-display text-3xl leading-tight tracking-tight text-black mb-2">Welcome back</h1>
                <p class="text-black/60 text-sm mb-8">Sign in to your Zippd account</p>

                <form method="POST" action="{{ route('login') }}">
                    @csrf

                    <!-- Email Address -->
                    <div class="auth-form-group mb-5">
                        <label for="email" class="block text-sm font-semibold text-black mb-2">Email Address</label>
                        <input id="email" class="auth-input w-full px-4 py-3 border border-black/15 rounded-lg focus:border-black/50 focus:outline-none focus:ring-1 focus:ring-black/20 transition @error('email') border-red-500 @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="auth-form-group mb-5">
                        <label for="password" class="block text-sm font-semibold text-black mb-2">Password</label>
                        <input id="password" class="auth-input w-full px-4 py-3 border border-black/15 rounded-lg focus:border-black/50 focus:outline-none focus:ring-1 focus:ring-black/20 transition @error('password') border-red-500 @enderror" type="password" name="password" required autocomplete="current-password" />
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Remember Me -->
                    <div class="auth-form-group mb-6">
                        <label for="remember_me" class="inline-flex items-center">
                            <input id="remember_me" type="checkbox" class="rounded border-black/20 text-black focus:ring-black" name="remember">
                            <span class="ms-2 text-sm text-black/70">Remember me</span>
                        </label>
                    </div>

                    <button type="submit" class="auth-button w-full bg-black text-white font-semibold py-3 rounded-lg transition hover:opacity-90 mb-4">
                        Sign In
                    </button>
                </form>

                <div class="auth-footer flex items-center justify-between text-sm">
                    @if (Route::has('password.request'))
                        <a href="{{ route('password.request') }}" class="text-black/60 hover:text-black transition">
                            Forgot password?
                        </a>
                    @endif

                    <a href="{{ route('register') }}" class="font-semibold text-black hover:text-black/70 transition">
                        Create Account
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
