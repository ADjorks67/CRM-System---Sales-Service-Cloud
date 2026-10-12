@props([
    'title' => 'Nothing here yet',
    'description' => null,
])

<div {{ $attributes->merge(['class' => 'rounded-lg border border-dashed border-border bg-surface/60 px-6 py-10 text-center']) }}>
    <p class="font-headline text-sm font-semibold text-primary">{{ $title }}</p>
    @if ($description)
        <p class="mt-1 text-sm text-muted">{{ $description }}</p>
    @endif
    @if ($slot->isNotEmpty())
        <div class="mt-4 flex justify-center gap-2">{{ $slot }}</div>
    @endif
</div>
