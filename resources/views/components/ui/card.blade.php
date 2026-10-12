@props([
    'padding' => true,
])

<div {{ $attributes->merge(['class' => 'crm-card'.($padding ? ' p-4 sm:p-5' : '')]) }}>
    {{ $slot }}
</div>
