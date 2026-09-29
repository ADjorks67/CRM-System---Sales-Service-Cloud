@if ($dashboard->widgets->isEmpty())
    <p class="text-sm text-text/70 col-span-full">No widgets on this dashboard yet. Edit the dashboard to add charts, tables, or metrics.</p>
@else
    @foreach ($dashboard->widgets as $index => $widget)
        @php $payload = $widgetPayloads[$index] ?? []; @endphp
        <section
            class="rounded bg-card p-4 shadow-[var(--shadow-card)]"
            style="grid-column: {{ $widget->grid_x + 1 }} / span {{ $widget->grid_w }}; grid-row: span {{ $widget->grid_h }};"
            data-widget-id="{{ $widget->id }}"
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
                @php
                    $rows = $payload['rows'] ?? collect();
                    $columns = $payload['columns'] ?? [];
                    $columnLabels = $payload['column_labels'] ?? [];
                @endphp
                @if (count($columns) === 0 || (method_exists($rows, 'isEmpty') ? $rows->isEmpty() : empty($rows)))
                    <p class="text-sm text-text/70">No data for this widget.</p>
                @else
                    <div class="max-h-64 overflow-auto">
                        <table class="min-w-full text-left text-xs">
                            <thead>
                                <tr>
                                    @foreach ($columnLabels as $label)
                                        <th class="px-2 py-1 font-semibold">{{ $label }}</th>
                                    @endforeach
                                </tr>
                            </thead>
                            <tbody>
                                @foreach (collect($rows)->take(20) as $row)
                                    <tr class="border-t border-black/5">
                                        @foreach ($columns as $column)
                                            <td class="px-2 py-1">{{ is_array($row) ? ($row[$column] ?? '—') : ($row->{$column} ?? '—') }}</td>
                                        @endforeach
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @endif
        </section>
    @endforeach
@endif
