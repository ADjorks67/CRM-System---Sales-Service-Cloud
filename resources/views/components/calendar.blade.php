@props([
    'eventsUrl' => null,
    'view' => 'dayGridMonth',
    'height' => '36rem',
])

<div
    {{ $attributes->class(['w-full rounded bg-card p-3 shadow-[var(--shadow-card)]']) }}
    data-crm-calendar
    data-crm-calendar-view="{{ $view }}"
    @if ($eventsUrl) data-crm-calendar-events="{{ $eventsUrl }}" @endif
    style="min-height: {{ $height }}"
    role="region"
    aria-label="{{ $attributes->get('aria-label', 'Calendar') }}"
></div>

@once
    @push('scripts')
        @vite('resources/js/calendar.js')
    @endpush
@endonce
