@props([
    'columns' => [],
    'empty' => 'No records to display.',
    'sort' => null,
    'direction' => 'asc',
    'sortable' => [],
    'paginator' => null,
    'hasRows' => true,
])

<div {{ $attributes->merge(['class' => 'overflow-x-auto rounded bg-card shadow-[var(--shadow-card)]']) }}>
    <table class="min-w-full border-collapse text-sm">
        <thead class="bg-primary/5 text-left">
            <tr>
                @foreach ($columns as $key => $label)
                    <th scope="col" class="px-3 py-3 font-semibold text-primary">
                        @if (in_array($key, $sortable, true) && $sort !== null)
                            @php
                                $nextDir = ($sort === $key && $direction === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a
                                href="{{ request()->fullUrlWithQuery(['sort' => $key, 'direction' => $nextDir]) }}"
                                class="inline-flex min-h-11 items-center gap-1 text-primary no-underline hover:underline"
                            >
                                {{ $label }}
                                @if ($sort === $key)
                                    <span aria-hidden="true">{{ $direction === 'asc' ? '↑' : '↓' }}</span>
                                @endif
                            </a>
                        @else
                            {{ $label }}
                        @endif
                    </th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if ($hasRows)
                {{ $slot }}
            @else
                <tr>
                    <td colspan="{{ max(count($columns), 1) }}" class="px-3 py-8 text-center text-text/60">
                        {{ $empty }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    @if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator)
        <div class="border-t border-black/10 px-3 py-3 text-xs text-text/70">
            Showing {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }}
            <div class="mt-2">
                {{ $paginator->links() }}
            </div>
        </div>
    @endif
</div>
