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

                if (localStorage.getItem('crm-sidebar-collapsed') === '1') {
                    document.documentElement.classList.add('sidebar-collapsed');
                }
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

    <x-layout.sidebar />

    <div class="crm-shell flex min-h-screen flex-col">
        <x-layout.topbar />

        <main id="main-content" class="mx-auto w-full max-w-[90rem] flex-1 px-4 py-6 lg:px-8">
            <x-toast type="success" />
            <x-toast type="error" />

            @yield('content')
        </main>

        <footer class="border-t border-border py-4 text-center text-xs text-muted">
            {{ config('app.name', 'CRM System') }} — Sales &amp; Service Cloud
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
