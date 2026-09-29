@extends('layouts.app')

@section('title', $dashboard->name.' — '.config('app.name'))

@section('content')
    <div class="mb-4 flex flex-wrap items-end justify-between gap-3">
        <div>
            <p class="text-sm"><a href="{{ route('dashboards.index') }}" class="text-secondary no-underline">Dashboards</a></p>
            <h1>{{ $dashboard->name }}</h1>
        </div>
        <div class="flex flex-wrap gap-2">
            <button type="button" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm" onclick="window.print()">Print</button>
            @can('update', $dashboard)
                <a href="{{ route('dashboards.edit', $dashboard) }}" class="inline-flex min-h-11 items-center rounded bg-secondary px-4 py-2 text-sm font-semibold text-white no-underline">Edit</a>
            @endcan
        </div>
    </div>

    <form method="post" action="{{ route('dashboards.filters.store') }}" class="mb-4 flex flex-wrap items-end gap-2 rounded bg-card p-4 shadow-[var(--shadow-card)]">
        @csrf
        <input type="hidden" name="redirect" value="{{ route('dashboards.show', $dashboard) }}">
        <x-form-field name="date_from" label="From" type="date" :value="$filters['date_from']" />
        <x-form-field name="date_to" label="To" type="date" :value="$filters['date_to']" />
        <x-form-field name="owner_id" label="Owner" :value="$filters['owner_id']" :options="['' => 'All owners'] + $owners->mapWithKeys(fn ($u) => [$u->id => $u->name])->all()" />
        <button type="submit" class="inline-flex min-h-11 items-center rounded border border-black/20 px-4 py-2 text-sm">Apply filters</button>
    </form>

    <div id="dashboard-grid" class="grid gap-4" style="grid-template-columns: repeat(12, minmax(0, 1fr));">
        @foreach ($dashboard->widgets as $index => $widget)
            @php $payload = $widgetPayloads[$index] ?? []; @endphp
            <section
                class="rounded bg-card p-4 shadow-[var(--shadow-card)]"
                style="grid-column: {{ $widget->grid_x + 1 }} / span {{ $widget->grid_w }}; grid-row: span {{ $widget->grid_h }};"
            >
                <div class="mb-2 flex items-center justify-between gap-2">
                    <h2 class="text-base font-semibold text-primary">{{ $payload['title'] ?? $widget->title }}</h2>
                    @if ($widget->source_type === 'saved_report' && $widget->saved_report_id)
                        <a href="{{ route('saved-reports.show', $widget->saved_report_id) }}" class="text-xs text-secondary no-underline">Drill to report</a>
                    @elseif ($widget->source_type === 'prebuilt' && $widget->source_key)
                        <a href="{{ route('reports.show', $widget->source_key) }}" class="text-xs text-secondary no-underline">Drill to report</a>
                    @endif
                </div>

                @if (! empty($payload['error']))
                    <p class="text-sm text-error">{{ $payload['error'] }}</p>
                @elseif (($payload['widget_type'] ?? $widget->widget_type) === 'metric' || ($payload['widget_type'] ?? $widget->widget_type) === 'gauge')
                    @php $metric = $payload['metric'] ?? null; @endphp
                    <p class="text-3xl font-semibold text-primary">{{ $metric['value'] ?? 0 }}{{ $metric['suffix'] ?? '' }}</p>
                    <p class="text-sm text-text/70">{{ $metric['label'] ?? 'Records' }}</p>
                @elseif (($payload['chart'] ?? null) && ($payload['widget_type'] ?? $widget->widget_type) === 'chart')
                    <x-chart :type="$payload['chart']['type']" :config="$payload['chart']" height="12rem" :aria-label="$widget->title" />
                @else
                    <div class="max-h-64 overflow-auto">
                        <table class="min-w-full text-left text-xs">
                            <thead>
                                <tr>
                                    @foreach ($payload['column_labels'] ?? [] as $label)
                                        <th class="px-2 py-1 font-semibold">{{ $label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (($payload['rows'] ?? collect())->take(20) as $row)
                                    <tr class="border-t border-black/5">
                                        @foreach ($payload['columns'] ?? [] as $column)
                                            <td class="px-2 py-1">{{ is_array($row) ? ($row[$column] ?? '—') : ($row->{$column} ?? '—') }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        @endforeach
    </div>
@endsection
