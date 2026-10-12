@props([
    'name',
    'size' => 20,
])

@php
    $paths = match ($name) {
        'home' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M5 12l-2 0l9 -9l9 9l-2 0" />
            <path d="M5 12v7a2 2 0 0 0 2 2h10a2 2 0 0 0 2 -2v-7" />
            <path d="M9 21v-6a2 2 0 0 1 2 -2h2a2 2 0 0 1 2 2v6" />
        SVG,
        'user-plus' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M8 7a4 4 0 1 0 8 0a4 4 0 0 0 -8 0" />
            <path d="M16 19h6" />
            <path d="M19 16v6" />
            <path d="M6 21v-2a4 4 0 0 1 4 -4h4" />
        SVG,
        'building' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M3 21l18 0" />
            <path d="M9 8l1 0" />
            <path d="M9 12l1 0" />
            <path d="M9 16l1 0" />
            <path d="M14 8l1 0" />
            <path d="M14 12l1 0" />
            <path d="M14 16l1 0" />
            <path d="M5 21v-16a2 2 0 0 1 2 -2h10a2 2 0 0 1 2 2v16" />
        SVG,
        'building-skyscraper' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M3 21l18 0" />
            <path d="M5 21v-14l8 -4v18" />
            <path d="M19 21v-10l-6 -4" />
            <path d="M9 9l0 .01" />
            <path d="M9 12l0 .01" />
            <path d="M9 15l0 .01" />
            <path d="M9 18l0 .01" />
        SVG,
        'users' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M5 7a4 4 0 1 0 8 0a4 4 0 1 0 -8 0" />
            <path d="M3 21v-2a4 4 0 0 1 4 -4h4a4 4 0 0 1 4 4v2" />
            <path d="M16 3.13a4 4 0 0 1 0 7.75" />
            <path d="M21 21v-2a4 4 0 0 0 -3 -3.85" />
        SVG,
        'target-arrow' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M11 12a1 1 0 1 0 2 0a1 1 0 1 0 -2 0" />
            <path d="M12 7a5 5 0 1 0 5 5" />
            <path d="M13 3.055a9 9 0 1 0 7.941 7.945" />
            <path d="M15 6v3h3l3 -3h-3v-3l-3 3" />
            <path d="M15 9l-3 3" />
        SVG,
        'headset' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M4 14v-3a8 8 0 1 1 16 0v3" />
            <path d="M18 19c0 1.657 -2.686 3 -6 3" />
            <path d="M4 14a2 2 0 0 1 2 -2h1a2 2 0 0 1 2 2v3a2 2 0 0 1 -2 2h-1a2 2 0 0 1 -2 -2v-3" />
            <path d="M15 14a2 2 0 0 1 2 -2h1a2 2 0 0 1 2 2v3a2 2 0 0 1 -2 2h-1a2 2 0 0 1 -2 -2v-3" />
        SVG,
        'checklist' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M9.615 20h-2.615a2 2 0 0 1 -2 -2v-12a2 2 0 0 1 2 -2h8a2 2 0 0 1 2 2v8" />
            <path d="M14 19l2 2l4 -4" />
            <path d="M9 8h4" />
            <path d="M9 12h2" />
        SVG,
        'layout-dashboard' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M5 4h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1" />
            <path d="M5 16h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1" />
            <path d="M15 12h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-6a1 1 0 0 1 1 -1" />
            <path d="M15 4h4a1 1 0 0 1 1 1v2a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1v-2a1 1 0 0 1 1 -1" />
        SVG,
        'file-analytics' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M14 3v4a1 1 0 0 0 1 1h4" />
            <path d="M17 21h-10a2 2 0 0 1 -2 -2v-14a2 2 0 0 1 2 -2h7l5 5v11a2 2 0 0 1 -2 2" />
            <path d="M9 17l0 -5" />
            <path d="M12 17l0 -1" />
            <path d="M15 17l0 -3" />
        SVG,
        'currency-dollar' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M16.7 8a3 3 0 0 0 -2.7 -2h-4a3 3 0 0 0 0 6h4a3 3 0 0 1 0 6h-4a3 3 0 0 1 -2.7 -2" />
            <path d="M12 3v3m0 12v3" />
        SVG,
        'chart-pie' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M10 3.2a9 9 0 1 0 10.8 10.8" />
            <path d="M16.8 13.4a5 5 0 1 0 -7.2 -7.2" />
            <path d="M13 3.2v7.8h7.8" />
        SVG,
        'list-check' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M3.5 5.5l1.5 1.5l2.5 -2.5" />
            <path d="M3.5 11.5l1.5 1.5l2.5 -2.5" />
            <path d="M3.5 17.5l1.5 1.5l2.5 -2.5" />
            <path d="M11 6h9" />
            <path d="M11 12h9" />
            <path d="M11 18h9" />
        SVG,
        'calendar-event' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M4 5m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
            <path d="M16 3v4" />
            <path d="M8 3v4" />
            <path d="M4 11h16" />
            <path d="M8 15h2v2h-2z" />
        SVG,
        'briefcase' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M3 7m0 2a2 2 0 0 1 2 -2h14a2 2 0 0 1 2 2v9a2 2 0 0 1 -2 2h-14a2 2 0 0 1 -2 -2z" />
            <path d="M8 7v-2a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v2" />
            <path d="M12 12l0 .01" />
            <path d="M3 13a20 20 0 0 0 18 0" />
        SVG,
        'chart-bar' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M3 12m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v6a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
            <path d="M9 8m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v10a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
            <path d="M15 4m0 1a1 1 0 0 1 1 -1h4a1 1 0 0 1 1 1v14a1 1 0 0 1 -1 1h-4a1 1 0 0 1 -1 -1z" />
            <path d="M4 20l14 0" />
        SVG,
        'circle-check' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
            <path d="M9 12l2 2l4 -4" />
        SVG,
        'menu-2' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M4 6l16 0" />
            <path d="M4 12l16 0" />
            <path d="M4 18l16 0" />
        SVG,
        'x' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M18 6l-12 12" />
            <path d="M6 6l12 12" />
        SVG,
        'layout-sidebar-left-collapse' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
            <path d="M9 4v16" />
            <path d="M15 10l-2 2l2 2" />
        SVG,
        'layout-sidebar-left-expand' => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M4 4m0 2a2 2 0 0 1 2 -2h12a2 2 0 0 1 2 2v12a2 2 0 0 1 -2 2h-12a2 2 0 0 1 -2 -2z" />
            <path d="M9 4v16" />
            <path d="M14 10l2 2l-2 2" />
        SVG,
        default => <<<'SVG'
            <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
            <path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" />
        SVG,
    };
@endphp

<svg
    xmlns="http://www.w3.org/2000/svg"
    width="{{ $size }}"
    height="{{ $size }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="2"
    stroke-linecap="round"
    stroke-linejoin="round"
    {{ $attributes->merge(['class' => 'shrink-0']) }}
    aria-hidden="true"
>
    {!! $paths !!}
</svg>
