@props([
    'title',
    'subtitle' => null,
])

<div {{ $attributes->merge(['class' => 'mb-6 flex flex-wrap items-end justify-between gap-3 border-b border-border pb-4']) }}>
    <div class="min-w-0">
        @isset($back)
            <div class="mb-2">
                {{ $back }}
            </div>
        @endisset
        <h1 class="font-headline text-xl font-semibold tracking-tight text-primary sm:text-2xl">{{ $title }}</h1>
        @if ($subtitle)
            <p class="mt-1 text-sm text-muted">{{ $subtitle }}</p>
        @endif
        {{ $slot }}
    </div>
    @isset($actions)
        <div class="flex flex-wrap items-center gap-2">
            {{ $actions }}
        </div>
    @endisset
</div>
