@props([
    'title' => null,
])

<header class="sticky top-0 z-20 border-b border-border bg-[#0b0c0c]/90 backdrop-blur-xl">
    <div class="flex flex-wrap items-center gap-3 px-4 py-3 lg:px-8">
        <button
            type="button"
            id="crm-sidebar-toggle"
            class="crm-sidebar-toggle"
            aria-controls="crm-sidebar"
            aria-expanded="true"
            aria-label="Hide navigation"
            title="Hide navigation"
        >
            <span class="sr-only">Toggle navigation</span>
            {{-- Tabler: menu-2 — mobile open --}}
            <svg data-sidebar-icon="menu" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M4 6l16 0" />
                <path d="M4 12l16 0" />
                <path d="M4 18l16 0" />
            </svg>
            {{-- Tabler: x — mobile close --}}
            <svg data-sidebar-icon="close" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M18 6l-12 12" />
                <path d="M6 6l12 12" />
            </svg>
            {{-- Tabler: layout-sidebar-left-collapse — desktop hide --}}
            <svg data-sidebar-icon="expanded" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
                <path d="M9 4v16" />
                <path d="M15 10l-2 2l2 2" />
            </svg>
            {{-- Tabler: layout-sidebar-left-expand — desktop show --}}
            <svg data-sidebar-icon="collapsed" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
                <path d="M9 4v16" />
                <path d="M14 10l2 2l-2 2" />
            </svg>
        </button>

        <div class="min-w-0 flex-1">
            @hasSection('page-title')
                <h1 class="truncate font-headline text-lg font-semibold text-primary">@yield('page-title')</h1>
            @elseif ($title)
                <h1 class="truncate font-headline text-lg font-semibold text-primary">{{ $title }}</h1>
            @endif
            @hasSection('breadcrumbs')
                <div class="mt-0.5">@yield('breadcrumbs')</div>
            @endif
        </div>

        <div class="relative order-last w-full max-w-md sm:order-none sm:ml-auto sm:w-72 lg:w-80">
            <form action="{{ route('search.index') }}" method="get" role="search" class="flex" aria-label="Global search" id="global-search-form">
                <label for="global-search" class="sr-only">Search</label>
                <input
                    id="global-search"
                    type="search"
                    name="q"
                    value="{{ request('q') }}"
                    placeholder="Search CRM…"
                    minlength="2"
                    class="crm-input"
                    autocomplete="off"
                    data-suggest-url="{{ route('search.suggest') }}"
                >
            </form>
            <div id="global-search-suggest" class="absolute left-0 right-0 z-40 mt-1 hidden max-h-80 overflow-auto rounded-lg border border-border bg-card text-sm text-text shadow-lg" role="listbox"></div>
        </div>

        @auth
            <div class="hidden items-center gap-1.5 xl:flex">
                @can('create', App\Models\Lead::class)
                    <a href="{{ route('leads.create') }}" class="crm-btn-secondary !min-h-8 !px-2.5 !text-xs">+ Lead</a>
                @endcan
                @can('create', App\Models\Opportunity::class)
                    <a href="{{ route('opportunities.create') }}" class="crm-btn-secondary !min-h-8 !px-2.5 !text-xs">+ Deal</a>
                @endcan
                @can('create', App\Models\CrmCase::class)
                    <a href="{{ route('cases.create') }}" class="crm-btn-secondary !min-h-8 !px-2.5 !text-xs">+ Case</a>
                @endcan
            </div>

            <div class="flex items-center gap-2">
                <button
                    type="button"
                    id="crm-theme-toggle"
                    class="crm-btn-secondary size-11 p-0"
                    aria-label="Toggle dark mode"
                    title="Toggle theme"
                >
                    <svg class="size-5 light:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.9 4.9l1.4 1.4M17.7 17.7l1.4 1.4M2 12h2M20 12h2M4.9 19.1l1.4-1.4M17.7 6.3l1.4-1.4"/></svg>
                    <svg class="hidden size-5 light:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>

                <details class="relative">
                    <summary class="crm-btn-secondary list-none cursor-pointer gap-2 pr-3 [&::-webkit-details-marker]:hidden">
                        <span class="flex size-8 items-center justify-center rounded-full bg-secondary/20 text-xs font-bold text-secondary">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                        <span class="hidden max-w-[8rem] truncate sm:inline">{{ auth()->user()->name }}</span>
                    </summary>
                    <div class="absolute right-0 z-50 mt-2 w-56 rounded-lg border border-border bg-card py-2 text-sm shadow-lg">
                        <p class="border-b border-border px-3 py-2 text-xs text-muted">
                            {{ auth()->user()->role?->name ?? 'User' }}
                        </p>
                        @can('viewAny', App\Models\User::class)
                            <a href="{{ route('users.index') }}" class="block px-3 py-2 text-text no-underline hover:bg-surface">Users</a>
                            <a href="{{ route('gdpr.index') }}" class="block px-3 py-2 text-text no-underline hover:bg-surface">Privacy</a>
                        @endcan
                        <a href="{{ route('mfa.edit') }}" class="block px-3 py-2 text-text no-underline hover:bg-surface">MFA</a>
                        <a href="{{ route('api-tokens.index') }}" class="block px-3 py-2 text-text no-underline hover:bg-surface">API tokens</a>
                        @can('permission', 'leads.create')
                            <a href="{{ route('imports.index') }}" class="block px-3 py-2 text-text no-underline hover:bg-surface">Import / Export</a>
                        @endcan
                        <form method="post" action="{{ route('logout') }}" class="border-t border-border">
                            @csrf
                            <button type="submit" class="w-full px-3 py-2 text-left text-error hover:bg-surface">Sign out</button>
                        </form>
                    </div>
                </details>
            </div>
        @endauth
    </div>
</header>
