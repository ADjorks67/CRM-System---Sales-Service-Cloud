@props([
    'href',
    'label' => 'Back',
])

<a
    href="{{ $href }}"
    {{ $attributes->merge(['class' => 'crm-btn-secondary']) }}
>
    <svg class="size-4 shrink-0" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path d="M15 6l-6 6 6 6" />
    </svg>
    <span>{{ $label }}</span>
</a>
