<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Orders - Zippd</title>
    @vite(['resources/css/app.css'])
</head>
<body class="bg-white text-black antialiased">
<main class="p-8">
    <h1 class="text-2xl font-semibold">Orders</h1>
    <p class="mt-3 text-sm text-black/70">Blank page for orders. We will build this later.</p>
    <a href="{{ route('dashboard') }}" class="mt-6 inline-block rounded border border-black/20 px-4 py-2 text-sm">Back to Dashboard</a>
</main>
</body>
</html>
