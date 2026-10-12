@extends('layouts.app')

@section('title', $report->title().' — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm"><a href="{{ route('reports.index') }}" class="text-secondary no-underline">Reports</a></p>
            <h1>{{ $report->title() }}</h1>
            <p class="text-sm text-muted">
                {{ $from->toDateString() }} – {{ $to->toDateString() }} · {{ $rows->count() }} row(s)
                @if ($report->description() !== '')
                    · {{ $report->description() }}
                @endif
            </p>
        </div>
        <form method="get" action="{{ route('reports.show', $report->key()) }}" class="flex flex-wrap items-end gap-2">
            <x-form-field name="year" label="Fiscal year" type="number" :value="$year" class="min-w-[8rem]" />
            @if ($sort !== '')
                <input type="hidden" name="sort" value="{{ $sort }}">
                <input type="hidden" name="direction" value="{{ $direction }}">
            @endif
            <button type="submit" class="crm-btn-secondary">Refresh</button>
        </form>
    </div>

    @if ($chart && count($chart['values'] ?? []) > 0)
        <div class="mb-4 crm-card p-4 sm:p-5">
            <x-chart
                :type="$chart['type']"
                :config="$chart"
                height="14rem"
                :aria-label="$report->title().' chart'"
            />
        </div>
    @endif

    <div class="overflow-x-auto rounded bg-card shadow-[var(--shadow-card)]">
        <table class="min-w-full text-left text-sm">
            <thead class="bg-page/80 text-muted">
                <tr>
                    @foreach ($report->columns() as $column)
                        @php
                            $nextDirection = ($sort === $column && $direction === 'asc') ? 'desc' : 'asc';
                        @endphp
                        <th class="px-3 py-3 font-medium">
                            <a
                                href="{{ route('reports.show', ['report' => $report->key(), 'year' => $year, 'sort' => $column, 'direction' => $nextDirection]) }}"
                                class="text-inherit no-underline hover:text-secondary"
                            >{{ $column }}</a>
                        </th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse ($rows as $index => $row)
                    <tr @class(['border-t border-border', 'bg-page/80' => $index % 2 === 1, 'hover:bg-page'])>
                        @foreach ($report->columns() as $column)
                            <td class="px-3 py-3">
                                @if ($loop->first && isset($row['_url']))
                                    <a href="{{ $row['_url'] }}">{{ $row[$column] ?? '—' }}</a>
                                @else
                                    {{ $row[$column] ?? '—' }}
                                @endif
                            </td>
                        @endforeach
                    </tr>
                @empty
                    <tr>
                        <td colspan="{{ count($report->columns()) }}" class="px-3 py-6 text-center text-muted">No rows for this period.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
@endsection
