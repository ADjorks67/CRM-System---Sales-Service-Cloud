@props([
    'brand' => config('app.name', 'CRM System'),
])

@php
    $homeItem = ['label' => 'Home', 'route' => 'home', 'match' => 'home', 'icon' => 'home'];
    $navGroups = [
        'Sales' => [
            ['label' => 'Leads', 'route' => 'leads.index', 'match' => 'leads.*', 'icon' => 'user-plus'],
            ['label' => 'Accounts', 'route' => 'accounts.index', 'match' => 'accounts.*', 'icon' => 'building'],
            ['label' => 'Contacts', 'route' => 'contacts.index', 'match' => 'contacts.*', 'icon' => 'users'],
            ['label' => 'Opportunities', 'route' => 'opportunities.index', 'match' => 'opportunities.*', 'icon' => 'target-arrow'],
        ],
        'Service' => [
            ['label' => 'Cases', 'route' => 'cases.index', 'match' => 'cases.*', 'icon' => 'headset'],
            ['label' => 'Tasks', 'route' => 'tasks.index', 'match' => 'tasks.*', 'icon' => 'checklist'],
            ['label' => 'Calendar', 'route' => 'calendar.index', 'match' => ['calendar.*', 'events.*'], 'icon' => 'calendar-event'],
        ],
        'Analytics' => [
            ['label' => 'Reports', 'route' => 'reports.index', 'match' => ['reports.*', 'saved-reports.*', 'subscriptions.*'], 'icon' => 'file-analytics'],
            ['label' => 'Dashboards', 'route' => 'dashboards.index', 'match' => 'dashboards.*', 'icon' => 'layout-dashboard'],
        ],
    ];

    $isActive = function (array|string $match): bool {
        foreach ((array) $match as $pattern) {
            if (request()->routeIs($pattern)) {
                return true;
            }
        }

        return false;
    };
@endphp

<aside
    id="crm-sidebar"
    class="fixed inset-y-0 left-0 z-40 flex w-[var(--sidebar-width)] -translate-x-full flex-col border-r border-border bg-sidebar text-sidebar-text transition-transform duration-200 lg:translate-x-0"
    aria-label="Application"
    tabindex="-1"
>
    <div class="flex h-16 items-center gap-3 border-b border-border px-4">
        <span class="flex size-9 items-center justify-center rounded-lg bg-secondary text-[#171717]" aria-hidden="true">
            <x-ui.icon name="building-skyscraper" :size="20" />
        </span>
        <div class="min-w-0">
            <a href="{{ route('home') }}" class="block truncate font-headline text-base font-semibold text-primary no-underline hover:text-primary">
                {{ $brand }}
            </a>
            <p class="truncate text-[0.65rem] font-semibold uppercase tracking-wider text-muted">Sales &amp; Service</p>
        </div>
    </div>

    <nav class="flex-1 overflow-y-auto px-2 py-4" aria-label="Primary">
        @php $homeActive = $isActive($homeItem['match']); @endphp
        <p class="mb-1 px-3 text-[0.65rem] font-semibold uppercase tracking-wider text-muted">Core</p>
        <ul class="mb-4 space-y-0.5">
            <li>
                <a
                    href="{{ route($homeItem['route']) }}"
                    @class(['crm-nav-link', 'is-active' => $homeActive])
                    @if ($homeActive) aria-current="page" @endif
                >
                    <x-ui.icon :name="$homeItem['icon']" :size="18" />
                    <span>{{ $homeItem['label'] }}</span>
                </a>
            </li>
        </ul>
        @foreach ($navGroups as $group => $items)
            <p class="mb-1 mt-3 px-3 text-[0.65rem] font-semibold uppercase tracking-wider text-muted">{{ $group }}</p>
            <ul class="space-y-0.5">
                @foreach ($items as $item)
                    @php $active = $isActive($item['match']); @endphp
                    <li>
                        <a
                            href="{{ route($item['route']) }}"
                            @class(['crm-nav-link', 'is-active' => $active])
                            @if ($active) aria-current="page" @endif
                        >
                            <x-ui.icon :name="$item['icon']" :size="18" />
                            <span>{{ $item['label'] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        @endforeach
    </nav>

    <div class="border-t border-border p-3 text-[0.65rem] uppercase tracking-wider text-muted">
        Enterprise CRM
    </div>
</aside>

<button
    type="button"
    id="crm-sidebar-backdrop"
    class="fixed inset-0 z-30 hidden bg-black/75 lg:hidden"
    aria-label="Close navigation"
    aria-hidden="true"
    tabindex="-1"
></button>
