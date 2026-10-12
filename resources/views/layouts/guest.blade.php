<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', config('app.name', 'CRM System'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700&display=swap" rel="stylesheet">
    <script>
        (() => {
            try {
                const theme = localStorage.getItem('crm-theme-v2') || 'dark';
                if (theme === 'light') {
                    document.documentElement.classList.add('light');
                } else {
                    document.documentElement.classList.remove('light');
                    localStorage.setItem('crm-theme-v2', 'dark');
                }
                localStorage.removeItem('crm-theme');
            } catch (e) {}
        })();
    </script>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen bg-page text-text antialiased">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded-lg focus:bg-card focus:px-3 focus:py-2">
        Skip to main content
    </a>

    <header class="border-b border-border bg-sidebar text-sidebar-text">
        <div class="mx-auto flex max-w-lg items-center justify-between px-4 py-4">
            <a href="{{ route('login') }}" class="flex items-center gap-2 font-headline text-lg font-semibold text-primary no-underline hover:text-primary">
                <span class="flex size-9 items-center justify-center rounded-lg bg-secondary text-sm font-bold text-[#171717]" aria-hidden="true">C</span>
                {{ config('app.name', 'CRM System') }}
            </a>
            <button type="button" id="crm-theme-toggle" class="crm-btn-secondary size-11 p-0" aria-label="Toggle dark mode" title="Toggle theme">
                <svg class="size-5 light:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                <svg class="hidden size-5 light:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
            </button>
        </div>
    </header>

    <main id="main-content" class="mx-auto flex w-full max-w-lg flex-1 flex-col justify-center px-4 py-10">
        @if (session('success'))
            <div class="mb-4 rounded-lg border border-success/30 bg-success/10 px-4 py-3 text-sm text-success" role="status">
                {{ session('success') }}
            </div>
        @endif

        @if (session('error'))
            <div class="mb-4 rounded-lg border border-error/30 bg-error/10 px-4 py-3 text-sm text-error" role="alert">
                {{ session('error') }}
            </div>
        @endif

        <div class="crm-card p-6 sm:p-8">
            @yield('content')
        </div>
    </main>

    <footer class="border-t border-border py-4 text-center text-xs text-muted">
        {{ config('app.name', 'CRM System') }} — Sales &amp; Service Cloud
    </footer>

    @stack('scripts')
</body>
</html>
