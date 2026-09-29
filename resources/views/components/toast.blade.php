@props([
    'type' => 'success',
    'message' => null,
])

@php
    $message = $message ?? session($type);
    $styles = match ($type) {
        'error' => 'border-error/30 bg-error/10 text-error',
        'warning' => 'border-warning/30 bg-warning/10 text-warning',
        'info' => 'border-info/30 bg-info/10 text-info',
        default => 'border-success/30 bg-success/10 text-success',
    };
@endphp

@if ($message)
    <div
        {{ $attributes->merge(['class' => "mb-4 rounded border px-4 py-3 text-sm {$styles}", 'role' => $type === 'error' ? 'alert' : 'status']) }}
        data-crm-toast
    >
        {{ $message }}
    </div>
@endif
