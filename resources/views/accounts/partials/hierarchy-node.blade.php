@php
    $account = $node['account'];
    $isCurrent = (int) $account->id === (int) $current->id;
@endphp
<li class="{{ ($depth ?? 0) > 0 ? 'ms-4 border-l border-black/10 pl-3' : '' }}">
    <div class="flex flex-wrap items-baseline gap-2 py-1 text-sm">
        @if ($isCurrent)
            <span class="font-semibold text-primary" aria-current="page">{{ $account->name }}</span>
        @else
            <a href="{{ route('accounts.show', $account) }}" class="text-secondary no-underline">{{ $account->name }}</a>
        @endif
        <span class="text-text/60">
            Emp {{ $account->employees ?? 0 }}
            · Rev {{ number_format((float) ($account->annual_revenue ?? 0), 2) }}
        </span>
    </div>
    @if (! empty($node['children']))
        <ul class="list-none" role="group">
            @foreach ($node['children'] as $child)
                @include('accounts.partials.hierarchy-node', [
                    'node' => $child,
                    'current' => $current,
                    'depth' => ($depth ?? 0) + 1,
                ])
            @endforeach
        </ul>
    @endif
</li>
