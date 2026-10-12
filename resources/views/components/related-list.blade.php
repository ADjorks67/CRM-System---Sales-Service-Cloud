@props([
    'title' => 'Related',
    'empty' => 'No related records.',
])

<section {{ $attributes->merge(['class' => 'crm-card p-4 sm:p-5']) }} aria-label="{{ $title }}">
    <div class="mb-3 flex items-center justify-between gap-2">
        <h2 class="text-base font-semibold text-primary">{{ $title }}</h2>
        {{ $actions ?? '' }}
    </div>

    @if ($slot->isEmpty())
        <p class="text-sm text-muted">{{ $empty }}</p>
    @else
        {{ $slot }}
    @endif
</section>
