@props([
    'items' => [],
])

@if (count($items) > 0)
    <nav class="mb-3 text-sm text-text/70" aria-label="Breadcrumb">
        <ol class="flex flex-wrap items-center gap-1">
            @foreach ($items as $index => $item)
                <li class="inline-flex items-center gap-1">
                    @if ($index > 0)
                        <span class="text-text/40" aria-hidden="true">/</span>
                    @endif
                    @if (! empty($item['url']) && ! $loop->last)
                        <a href="{{ $item['url'] }}" class="text-secondary no-underline hover:underline">{{ $item['label'] }}</a>
                    @else
                        <span @if ($loop->last) aria-current="page" class="text-text" @endif>{{ $item['label'] }}</span>
                    @endif
                </li>
            @endforeach
        </ol>
    </nav>
@endif
