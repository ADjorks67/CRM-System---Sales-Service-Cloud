@props([
    'title' => 'Related',
    'empty' => 'No related records.',
])

<section {{ $attributes->merge(['class' => 'rounded bg-card p-4 shadow-[var(--shadow-card)]']) }} aria-label="{{ $title }}">
    <div class="mb-3 flex items-center justify-between gap-2">
        <h2 class="text-base font-semibold text-primary">{{ $title }}</h2>
        {{ $actions ?? '' }}
    </div>

    @if ($slot->isEmpty())
        <p class="text-sm text-text/60">{{ $empty }}</p>
    @else
        {{ $slot }}
    @endif
</section>
