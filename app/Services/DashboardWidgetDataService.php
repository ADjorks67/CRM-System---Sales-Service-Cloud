<?php

namespace App\Services;

use App\Models\DashboardWidget;
use App\Models\User;
use App\Reports\ReportCatalog;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class DashboardWidgetDataService
{
    public function __construct(
        private readonly ReportBuilderService $reportBuilder,
        private readonly DashboardFilterService $filters,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function resolve(User $user, DashboardWidget $widget, Request $request): array
    {
        $globalFilters = $this->filters->get($request);

        if ($widget->source_type === 'saved_report') {
            $saved = $widget->savedReport;
            if ($saved === null) {
                throw new InvalidArgumentException('Widget saved report missing.');
            }

            $result = $this->reportBuilder->runSaved($user, $saved, $globalFilters);

            return $this->formatWidgetPayload($widget, $result);
        }

        if ($widget->source_type === 'prebuilt') {
            $definition = ReportCatalog::find((string) $widget->source_key);
            $from = Carbon::parse($globalFilters['date_from'] ?? now()->startOfYear());
            $to = Carbon::parse($globalFilters['date_to'] ?? now()->endOfYear());
            $rows = $definition->rows($user, $from, $to);
            $chart = $definition->chart($user, $from, $to);

            return [
                'title' => $widget->title,
                'widget_type' => $widget->widget_type,
                'columns' => $definition->columns(),
                'column_labels' => $definition->columns(),
                'rows' => $rows,
                'chart' => $chart,
                'metric' => $this->metricFromRows($widget->widget_type, $rows),
            ];
        }

        throw new InvalidArgumentException('Unknown widget source.');
    }

    /**
     * @param  array<string, mixed>  $result
     * @return array<string, mixed>
     */
    private function formatWidgetPayload(DashboardWidget $widget, array $result): array
    {
        return [
            'title' => $widget->title,
            'widget_type' => $widget->widget_type,
            'columns' => $result['columns'],
            'column_labels' => $result['column_labels'],
            'rows' => $result['rows'],
            'chart' => $result['chart'],
            'metric' => $this->metricFromRows($widget->widget_type, $result['rows']),
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @return array{value: float|int, label: string, suffix: string, detail: string|null}|null
     */
    private function metricFromRows(string $widgetType, Collection $rows): ?array
    {
        if (! in_array($widgetType, ['metric', 'gauge'], true)) {
            return null;
        }

        $count = $rows->count();

        return [
            'value' => $widgetType === 'gauge' ? min(100, $count) : $count,
            'label' => $widgetType === 'gauge' ? 'Progress' : 'Records',
            'suffix' => $widgetType === 'gauge' ? '%' : '',
            'detail' => null,
        ];
    }
}
