<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'CRM System'))</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen flex flex-col bg-page">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-card focus:px-3 focus:py-2">
        Skip to main content
    </a>

    <header class="bg-primary text-white shadow">
        <div class="mx-auto flex max-w-lg items-center justify-between px-4 py-4">
            <a href="{{ route('login') }}" class="text-lg font-semibold text-white no-underline hover:text-white/90">
                {{ config('app.name', 'CRM System') }}
            </a>
        </div>
    </header>

    <main id="main-content" class="mx-auto flex w-full max-w-lg flex-1 flex-col justify-center px-4 py-10">
        @if (session('success'))
            <div class="mb-4 rounded border border-success/30 bg-success/10 px-4 py-3 text-sm text-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded border border-error/30 bg-error/10 px-4 py-3 text-sm text-error" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="rounded bg-card p-6 shadow-[var(--shadow-card)]">
            @yield('content')
        </div>
    </main>

    <footer class="border-t border-black/10 bg-card py-4 text-center text-xs text-text/70">
        {{ config('app.name', 'CRM System') }} &mdash; Sales &amp; Service Cloud
    </footer>

    @stack('scripts')
</body>
</html>
