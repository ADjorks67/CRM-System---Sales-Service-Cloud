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

            <div class="relative ml-auto min-w-[12rem] flex-1 max-w-md">
                <form action="{{ route('search.index') }}" method="get" role="search" class="flex" aria-label="Global search" id="global-search-form">
                    <label for="global-search" class="sr-only">Search</label>
                    <input
                        id="global-search"
                        type="search"
                        name="q"
                        value="{{ request('q') }}"
                        placeholder="Search…"
                        minlength="2"
                        class="w-full rounded border-0 bg-white px-3 py-2 text-sm text-text placeholder:text-text/50"
                        autocomplete="off"
                        data-suggest-url="{{ route('search.suggest') }}"
                    >
                </form>
                <div id="global-search-suggest" class="absolute left-0 right-0 z-40 mt-1 hidden max-h-80 overflow-auto rounded border border-black/10 bg-card text-sm text-text shadow-lg" role="listbox"></div>
            </div>

            @auth
                <div class="relative flex items-center gap-3 text-sm">
                    <span class="hidden sm:inline text-white/90">{{ auth()->user()->name }}</span>
                    @can('viewAny', App\Models\User::class)
                        <a href="{{ route('users.index') }}" class="inline-flex min-h-11 items-center rounded px-2 text-white no-underline hover:bg-white/10">Users</a>
                        <a href="{{ route('gdpr.index') }}" class="inline-flex min-h-11 items-center rounded px-2 text-white no-underline hover:bg-white/10">Privacy</a>
                    @endcan
                    <a href="{{ route('mfa.edit') }}" class="inline-flex min-h-11 items-center rounded px-2 text-white no-underline hover:bg-white/10">MFA</a>
                    <a href="{{ route('api-tokens.index') }}" class="inline-flex min-h-11 items-center rounded px-2 text-white no-underline hover:bg-white/10">API</a>
                    <form method="post" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="inline-flex min-h-11 items-center rounded px-2 text-white hover:bg-white/10" title="Sign out" aria-label="Sign out">
                            Sign out
                        </button>
                    </form>
                </div>
            @endauth
        </div>

        <nav class="border-t border-white/15" aria-label="Primary">
            <ul class="mx-auto flex max-w-7xl gap-1 overflow-x-auto px-2 py-1 text-sm">
                @php
                    $tabs = [
                        'Home' => 'home',
                        'Leads' => 'leads.index',
                        'Accounts' => 'accounts.index',
                        'Contacts' => 'contacts.index',
                        'Opportunities' => 'opportunities.index',
                        'Cases' => 'cases.index',
                        'Tasks' => 'tasks.index',
                        'Calendar' => 'calendar.index',
                        'Reports' => 'reports.index',
                        'Dashboards' => 'dashboards.index',
                    ];
                @endphp
                @foreach ($tabs as $label => $routeName)
                    <li>
                        @if ($routeName)
                            <a
                                href="{{ route($routeName) }}"
                                @class([
                                    'inline-flex min-h-11 items-center whitespace-nowrap rounded px-3 py-2 text-white no-underline hover:bg-white/10',
                                    'bg-white/15 font-semibold' => request()->routeIs($routeName) || request()->routeIs(str_replace('.index', '.*', $routeName)),
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
        <x-toast type="success" />
        <x-toast type="error" />

        @yield('content')
    </main>

    <footer class="border-t border-black/10 bg-card py-4 text-center text-xs text-text/70">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center justify-center gap-3 px-4">
            <span>{{ config('app.name', 'CRM System') }} &mdash; Sales &amp; Service Cloud</span>
            @auth
                @can('permission', 'leads.create')
                    <a href="{{ route('imports.index') }}" class="text-secondary no-underline">Import / Export</a>
                @endcan
            @endauth
        </div>
    </footer>

    @stack('scripts')
    <script>
        (() => {
            const input = document.getElementById('global-search');
            const panel = document.getElementById('global-search-suggest');
            if (! input || ! panel) return;

            let timer = null;
            const labels = { leads: 'Leads', accounts: 'Accounts', contacts: 'Contacts', opportunities: 'Opportunities', cases: 'Cases' };

            const hide = () => panel.classList.add('hidden');
            const show = (html) => {
                panel.innerHTML = html;
                panel.classList.toggle('hidden', ! html);
            };

            input.addEventListener('input', () => {
                clearTimeout(timer);
                const q = input.value.trim();
                if (q.length < 2) {
                    hide();
                    return;
                }
                timer = setTimeout(async () => {
                    try {
                        const url = new URL(input.dataset.suggestUrl, window.location.origin);
                        url.searchParams.set('q', q);
                        const res = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
                        const data = await res.json();
                        const groups = data.groups || {};
                        let html = '';
                        for (const [key, items] of Object.entries(groups)) {
                            if (! items.length) continue;
                            html += `<div class="border-b border-black/10 px-3 py-2 font-semibold text-primary">${labels[key] || key}</div>`;
                            for (const item of items) {
                                html += `<a href="${item.url}" class="block px-3 py-2 text-text no-underline hover:bg-secondary/5" role="option"><span class="font-medium">${item.label}</span><span class="block text-xs text-text/60">${item.meta || ''}</span></a>`;
                            }
                        }
                        if (! html) {
                            html = '<p class="px-3 py-2 text-text/70">No matches</p>';
                        }
                        show(html);
                    } catch (e) {
                        hide();
                    }
                }, 250);
            });

            document.addEventListener('click', (e) => {
                if (! panel.contains(e.target) && e.target !== input) hide();
            });
        })();
    </script>
</body>
</html>
