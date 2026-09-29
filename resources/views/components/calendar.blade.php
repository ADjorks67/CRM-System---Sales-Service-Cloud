@props([
    'eventsUrl' => null,
    'view' => 'dayGridMonth',
    'height' => '36rem',
    'createUrl' => null,
    'rescheduleUrlTemplate' => null,
])

<div
    {{ $attributes->class(['w-full rounded bg-card p-3 shadow-[var(--shadow-card)]']) }}
    data-crm-calendar
    data-crm-calendar-view="{{ $view }}"
    @if ($eventsUrl) data-crm-calendar-events="{{ $eventsUrl }}" @endif
    @if ($createUrl) data-crm-calendar-create="{{ $createUrl }}" @endif
    @if ($rescheduleUrlTemplate) data-crm-calendar-reschedule="{{ $rescheduleUrlTemplate }}" @endif
    data-crm-csrf="{{ csrf_token() }}"
    style="min-height: {{ $height }}"
    role="region"
    aria-label="{{ $attributes->get('aria-label', 'Calendar') }}"
></div>

@once
    @push('scripts')
        @vite('resources/js/calendar.js')
    @endpush
@endonce
