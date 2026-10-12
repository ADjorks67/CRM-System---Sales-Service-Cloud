@props([
    'label',
    'value',
    'hint' => null,
    'tone' => 'primary',
    'icon' => null,
])

@php
    $accent = match ($tone) {
        'success' => 'bg-success/15 text-success border border-success/30',
        'warning' => 'bg-warning/15 text-warning border border-warning/30',
        'danger' => 'bg-error/15 text-error border border-error/30',
        'info' => 'bg-secondary/15 text-secondary border border-secondary/25',
        'violet', 'navy' => 'bg-accent-navy text-accent-violet-bright border border-[#1B3045]',
        'espresso' => 'bg-[#5c4131]/40 text-[#e5bfa9] border border-[#5c4131]',
        default => 'bg-secondary/15 text-secondary border border-secondary/25',
    };

    $valueClass = match ($tone) {
        'success' => 'text-success',
        'warning' => 'text-warning',
        'danger' => 'text-error',
        'violet', 'navy' => 'text-accent-violet-bright',
        'espresso' => 'text-[#ffdbc8]',
        'info' => 'text-secondary',
        default => 'text-secondary',
    };

    $resolvedIcon = $icon ?? match ($tone) {
        'navy', 'violet' => 'chart-pie',
        'espresso', 'warning' => 'list-check',
        'info' => 'calendar-event',
        'success' => 'circle-check',
        default => 'currency-dollar',
    };
@endphp

<div {{ $attributes->merge(['class' => 'crm-card flex flex-col justify-between p-4 sm:p-5']) }}>
    <div class="flex items-start justify-between gap-3 pb-2">
        <p class="text-[0.65rem] font-semibold uppercase tracking-wider text-muted">{{ $label }}</p>
        <span class="flex size-9 shrink-0 items-center justify-center rounded-lg {{ $accent }}" aria-hidden="true">
            @if ($slot->isNotEmpty())
                {{ $slot }}
            @else
                <x-ui.icon :name="$resolvedIcon" :size="20" />
            @endif
        </span>
    </div>
    <div>
        <p class="tabular-metrics truncate font-headline text-3xl font-semibold tracking-tight {{ $valueClass }}">{{ $value }}</p>
        @if ($hint)
            <p class="mt-1 text-xs text-muted">{{ $hint }}</p>
        @endif
    </div>
</div>
