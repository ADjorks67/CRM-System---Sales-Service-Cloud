@props([
    'columns' => [],
    'empty' => 'No records to display.',
    'sort' => null,
    'direction' => 'asc',
    'sortable' => [],
    'paginator' => null,
    'hasRows' => true,
])

<div {{ $attributes->merge(['class' => 'crm-card overflow-x-auto']) }}>
    <table class="min-w-full border-collapse text-sm">
        <thead class="bg-surface text-left">
            <tr>
                @foreach ($columns as $key => $label)
                    <th scope="col" class="border-b border-border px-3 py-3 font-semibold text-primary">
                        @if (in_array($key, $sortable, true) && $sort !== null)
                            @php
                                $nextDir = ($sort === $key && $direction === 'asc') ? 'desc' : 'asc';
                            @endphp
                            <a
                                href="{{ request()->fullUrlWithQuery(['sort' => $key, 'direction' => $nextDir]) }}"
                                class="inline-flex min-h-11 items-center gap-1 text-primary no-underline hover:text-secondary"
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
                    <td colspan="{{ max(count($columns), 1) }}" class="px-3 py-10 text-center text-muted">
                        {{ $empty }}
                    </td>
                </tr>
            @endif
        </tbody>
    </table>

    @if ($paginator instanceof \Illuminate\Contracts\Pagination\Paginator)
        <div class="border-t border-border px-3 py-3 text-xs text-muted">
            Showing {{ $paginator->firstItem() ?? 0 }}–{{ $paginator->lastItem() ?? 0 }} of {{ $paginator->total() }}
            <div class="mt-2">
                {{ $paginator->links() }}
            </div>
        </div>
    @endif
</div>
