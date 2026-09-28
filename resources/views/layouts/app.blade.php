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
<body class="min-h-screen flex flex-col">
    <a href="#main-content" class="sr-only focus:not-sr-only focus:absolute focus:left-4 focus:top-4 focus:z-50 focus:rounded focus:bg-card focus:px-3 focus:py-2">
        Skip to main content
    </a>

    <header class="bg-primary text-white shadow">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-4 px-4 py-3">
            <a href="{{ route('home') }}" class="text-lg font-semibold text-white no-underline hover:text-white/90">
                {{ config('app.name', 'CRM System') }}
            </a>

            <form action="#" method="get" role="search" class="ml-auto flex min-w-[12rem] flex-1 max-w-md" aria-label="Global search">
                <label for="global-search" class="sr-only">Search</label>
                <input
                    id="global-search"
                    type="search"
                    name="q"
                    placeholder="Search…"
                    minlength="2"
                    class="w-full rounded border-0 bg-white px-3 py-2 text-sm text-text placeholder:text-text/50"
                    autocomplete="off"
                >
            </form>
        </div>

        <nav class="border-t border-white/15" aria-label="Primary">
            <ul class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-2 py-1 text-sm">
                @php
                    $tabs = [
                        'Home' => 'home',
                        'Leads' => null,
                        'Accounts' => null,
                        'Contacts' => null,
                        'Opportunities' => null,
                        'Cases' => null,
                        'Tasks' => null,
                        'Calendar' => null,
                        'Reports' => null,
                        'Dashboards' => null,
                    ];
                @endphp
                @foreach ($tabs as $label => $routeName)
                    <li>
                        @if ($routeName)
                            <a
                                href="{{ route($routeName) }}"
                                @class([
                                    'inline-flex min-h-11 items-center whitespace-nowrap rounded px-3 py-2 text-white no-underline hover:bg-white/10',
                                    'bg-white/15 font-semibold' => request()->routeIs($routeName),
                                ])
                            >{{ $label }}</a>
                        @else
                            <span class="inline-flex min-h-11 items-center whitespace-nowrap rounded px-3 py-2 text-white/60" title="Coming soon">{{ $label }}</span>
                        @endif
                    </li>
                @endforeach
            </ul>
        </nav>
    </header>

    <main id="main-content" class="mx-auto w-full max-w-7xl flex-1 px-4 py-6">
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

        @yield('content')
    </main>

    <footer class="border-t border-black/10 bg-card py-4 text-center text-xs text-text/70">
        <div class="mx-auto max-w-7xl px-4">
            {{ config('app.name', 'CRM System') }} &mdash; Sales &amp; Service Cloud
        </div>
    </footer>

    @stack('scripts')
</body>
</html>
