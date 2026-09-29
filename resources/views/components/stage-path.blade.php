@props([
    'stages' => [],
    'stageValues' => [],
    'currentStage' => '',
    'action' => null,
    'method' => 'POST',
    'disabled' => false,
])

@php
    $ordered = collect($stageValues)->map(fn ($value) => [
        'value' => $value,
        'label' => $stages[$value] ?? $value,
    ]);
    $currentIndex = $ordered->search(fn ($item) => $item['value'] === $currentStage);
@endphp

<nav aria-label="Opportunity stage path" class="overflow-x-auto">
    <ol class="flex min-w-max items-center gap-1 text-sm">
        @foreach ($ordered as $index => $item)
            @php
                $isCurrent = $item['value'] === $currentStage;
                $isPast = $currentIndex !== false && $index < $currentIndex;
            @endphp
            <li class="flex items-center gap-1">
                @if ($index > 0)
                    <span class="text-text/30" aria-hidden="true">›</span>
                @endif
                @if ($action && ! $disabled && ! $isCurrent)
                    <form method="post" action="{{ $action }}" class="inline">
                        @csrf
                        <input type="hidden" name="stage" value="{{ $item['value'] }}">
                        <button
                            type="submit"
                            @class([
                                'rounded px-2 py-1 text-left no-underline hover:bg-secondary/10',
                                'font-semibold text-secondary' => $isPast,
                                'text-text/70' => ! $isPast && ! $isCurrent,
                            ])
                        >
                            {{ $item['label'] }}
                        </button>
                    </form>
                @else
                    <span
                        @class([
                            'rounded px-2 py-1',
                            'bg-secondary/15 font-semibold text-secondary ring-1 ring-secondary/30' => $isCurrent,
                            'font-medium text-secondary' => $isPast && ! $isCurrent,
                            'text-text/60' => ! $isPast && ! $isCurrent,
                        ])
                    >
                        {{ $item['label'] }}
                    </span>
                @endif
            </li>
        @endforeach
    </ol>
</nav>
