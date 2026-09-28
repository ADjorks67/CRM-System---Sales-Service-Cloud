@props([
    'type' => 'bar',
    'config' => null,
    'height' => '16rem',
])

@php
    $json = $config === null ? null : (is_string($config) ? $config : json_encode($config, JSON_THROW_ON_ERROR));
@endphp

<div {{ $attributes->class(['relative w-full']) }} style="height: {{ $height }}">
    <canvas
        data-crm-chart="{{ $type }}"
        @if ($json) data-crm-chart-config='{!! $json !!}' @endif
        role="img"
        aria-label="{{ $attributes->get('aria-label', 'Chart') }}"
    ></canvas>
</div>

@once
    @push('scripts')
        @vite('resources/js/charts.js')
    @endpush
@endonce
