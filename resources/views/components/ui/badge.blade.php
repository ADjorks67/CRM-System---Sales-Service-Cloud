@props([
    'tone' => 'neutral',
])

@php
    $classes = match ($tone) {
        'success' => 'bg-success/12 text-success border-success/30',
        'warning' => 'bg-warning/15 text-warning border-warning/30',
        'danger', 'error' => 'bg-error/15 text-error border-error/30',
        'info', 'primary' => 'bg-secondary/12 text-secondary border-secondary/25',
        'violet', 'navy' => 'bg-accent-navy text-accent-violet-bright border-[#1B3045]',
        default => 'bg-[#242627] text-muted border-[#353738]',
    };
@endphp

<span {{ $attributes->merge(['class' => "crm-badge {$classes}"]) }}>{{ $slot }}</span>
