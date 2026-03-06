<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Create Account - Zippd</title>

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
                <h1 class="font-display text-3xl leading-tight tracking-tight text-black mb-2">Get Started</h1>
                <p class="text-black/60 text-sm mb-8">Create your Zippd account to start shopping</p>

                <form method="POST" action="{{ route('register') }}">
                    @csrf

                    <!-- Full Name -->
                    <div class="auth-form-group mb-5">
                        <label for="full_name" class="block text-sm font-semibold text-black mb-2">Full Name</label>
                        <input id="full_name" class="auth-input w-full px-4 py-3 border border-black/15 rounded-lg focus:border-black/50 focus:outline-none focus:ring-1 focus:ring-black/20 transition @error('full_name') border-red-500 @enderror" type="text" name="full_name" value="{{ old('full_name') }}" required autofocus autocomplete="name" />
                        @error('full_name')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email Address -->
                    <div class="auth-form-group mb-5">
                        <label for="email" class="block text-sm font-semibold text-black mb-2">Email Address</label>
                        <input id="email" class="auth-input w-full px-4 py-3 border border-black/15 rounded-lg focus:border-black/50 focus:outline-none focus:ring-1 focus:ring-black/20 transition @error('email') border-red-500 @enderror" type="email" name="email" value="{{ old('email') }}" required autocomplete="username" />
                        @error('email')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Phone -->
                    <div class="auth-form-group mb-5">
                        <label for="phone" class="block text-sm font-semibold text-black mb-2">Phone Number</label>
                        <input id="phone" class="auth-input w-full px-4 py-3 border border-black/15 rounded-lg focus:border-black/50 focus:outline-none focus:ring-1 focus:ring-black/20 transition @error('phone') border-red-500 @enderror" type="tel" name="phone" value="{{ old('phone') }}" required autocomplete="tel" />
                        @error('phone')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Gender -->
                    <div class="auth-form-group mb-5">
                        <label for="gender" class="block text-sm font-semibold text-black mb-2">Gender</label>
                        <select id="gender" class="auth-input w-full px-4 py-3 border border-black/15 rounded-lg focus:border-black/50 focus:outline-none focus:ring-1 focus:ring-black/20 transition @error('gender') border-red-500 @enderror" name="gender" required>
                            <option value="">Select Gender</option>
                            <option value="Male" {{ old('gender') === 'Male' ? 'selected' : '' }}>Male</option>
                            <option value="Female" {{ old('gender') === 'Female' ? 'selected' : '' }}>Female</option>
                            <option value="Other" {{ old('gender') === 'Other' ? 'selected' : '' }}>Other</option>
                        </select>
                        @error('gender')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Password -->
                    <div class="auth-form-group mb-5">
                        <label for="password" class="block text-sm font-semibold text-black mb-2">Password</label>
                        <input id="password" class="auth-input w-full px-4 py-3 border border-black/15 rounded-lg focus:border-black/50 focus:outline-none focus:ring-1 focus:ring-black/20 transition @error('password') border-red-500 @enderror" type="password" name="password" required autocomplete="new-password" />
                        @error('password')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Confirm Password -->
                    <div class="auth-form-group mb-6">
                        <label for="password_confirmation" class="block text-sm font-semibold text-black mb-2">Confirm Password</label>
                        <input id="password_confirmation" class="auth-input w-full px-4 py-3 border border-black/15 rounded-lg focus:border-black/50 focus:outline-none focus:ring-1 focus:ring-black/20 transition @error('password_confirmation') border-red-500 @enderror" type="password" name="password_confirmation" required autocomplete="new-password" />
                        @error('password_confirmation')
                            <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <button type="submit" class="auth-button w-full bg-black text-white font-semibold py-3 rounded-lg transition hover:opacity-90 mb-4">
                        Create Account
                    </button>
                </form>

                <div class="auth-footer text-center text-sm">
                    <span class="text-black/60">Already have an account? </span>
                    <a href="{{ route('login') }}" class="font-semibold text-black hover:text-black/70 transition">
                        Sign In
                    </a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>
