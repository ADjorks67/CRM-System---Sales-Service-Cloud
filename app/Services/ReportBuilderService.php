<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\SavedReport;
use App\Models\User;
use App\Support\ReportFieldCatalog;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use InvalidArgumentException;

class ReportBuilderService
{
    /**
     * @param  array<string, mixed>|null  $definition
     * @param  array{date_from?: string, date_to?: string, owner_id?: int|null}|null  $globalFilters
     * @return array{
     *     columns: list<string>,
     *     column_labels: list<string>,
     *     rows: Collection<int, array<string, mixed>>,
     *     chart: array{type: string, labels: list<string>, values: list<float|int>, label?: string}|null
     * }
     */
    public function run(User $user, string $reportType, ?array $definition, ?array $globalFilters = null): array
    {
        $definition = ReportFieldCatalog::sanitizeDefinition(
            $reportType,
            $definition ?? ['columns' => [array_key_first(ReportFieldCatalog::fields($reportType))]],
        );

        $fields = ReportFieldCatalog::fields($reportType);
        $query = $this->baseQuery($reportType, $user);

        $this->applyGlobalFilters($query, $reportType, $globalFilters);
        $this->applyFilters($query, $reportType, $definition['filters'], $fields);

        if ($definition['group_by'] !== null) {
            return $this->runGrouped($query, $reportType, $definition, $fields);
        }

        $rows = $query->limit(500)->get()->map(function (Model $model) use ($definition, $fields, $reportType): array {
            $row = [];
            foreach ($definition['columns'] as $columnKey) {
                $row[$columnKey] = $this->formatValue($reportType, $columnKey, $model->getAttribute($fields[$columnKey]['column']));
            }

            return $row;
        });

        return [
            'columns' => $definition['columns'],
            'column_labels' => collect($definition['columns'])->map(fn (string $key) => $fields[$key]['label'])->all(),
            'rows' => $rows,
            'chart' => $this->buildChart($rows, $definition['chart'], $fields),
        ];
    }

    /**
     * @param  array{date_from?: string, date_to?: string, owner_id?: int|null}|null  $globalFilters
     * @return array{
     *     columns: list<string>,
     *     column_labels: list<string>,
     *     rows: Collection<int, array<string, mixed>>,
     *     chart: array{type: string, labels: list<string>, values: list<float|int>, label?: string}|null
     * }
     */
    public function runSaved(User $user, SavedReport $savedReport, ?array $globalFilters = null): array
    {
        return $this->run($user, $savedReport->report_type, $savedReport->definition, $globalFilters);
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array<string, mixed>  $definition
     * @param  array<string, array{label: string, column: string, filterable: bool, groupable: bool}>  $fields
     * @return array{
     *     columns: list<string>,
     *     column_labels: list<string>,
     *     rows: Collection<int, array<string, mixed>>,
     *     chart: array{type: string, labels: list<string>, values: list<float|int>, label?: string}|null
     * }
     */
    private function runGrouped(Builder $query, string $reportType, array $definition, array $fields): array
    {
        $groupKey = (string) $definition['group_by'];
        $column = $fields[$groupKey]['column'];
        $table = $query->getModel()->getTable();

        $aggregated = $query
            ->selectRaw("{$table}.{$column} as group_key, COUNT(*) as record_count")
            ->groupBy("{$table}.{$column}")
            ->orderByDesc('record_count')
            ->limit(100)
            ->get();

        $rows = $aggregated->map(fn ($row) => [
            $groupKey => $this->formatValue($reportType, $groupKey, $row->group_key),
            'record_count' => (int) $row->record_count,
        ]);

        $columns = [$groupKey, 'record_count'];
        $labels = [$fields[$groupKey]['label'], 'Count'];

        $chart = null;
        if ($definition['chart'] !== null) {
            $chart = [
                'type' => $definition['chart']['type'],
                'labels' => $rows->pluck($groupKey)->map(fn ($v) => (string) $v)->all(),
                'values' => $rows->pluck('record_count')->map(fn ($v) => (int) $v)->all(),
                'label' => 'Count',
            ];
        }

        return [
            'columns' => $columns,
            'column_labels' => $labels,
            'rows' => $rows,
            'chart' => $chart,
        ];
    }

    /**
     * @param  Collection<int, array<string, mixed>>  $rows
     * @param  array{type: string, column: string}|null  $chartDefinition
     * @param  array<string, array{label: string, column: string, filterable: bool, groupable: bool}>  $fields
     * @return array{type: string, labels: list<string>, values: list<float|int>, label?: string}|null
     */
    private function buildChart(Collection $rows, ?array $chartDefinition, array $fields): ?array
    {
        if ($chartDefinition === null || $rows->isEmpty()) {
            return null;
        }

        $column = $chartDefinition['column'];
        $counts = $rows->groupBy(fn (array $row) => (string) ($row[$column] ?? '—'))
            ->map(fn (Collection $group) => $group->count())
            ->sortDesc()
            ->take(12);

        return [
            'type' => $chartDefinition['type'],
            'labels' => $counts->keys()->values()->all(),
            'values' => $counts->values()->map(fn (int $count) => $count)->all(),
            'label' => $fields[$column]['label'] ?? $column,
        ];
    }

    /**
     * @return Builder<Model>
     */
    private function baseQuery(string $reportType, User $user): Builder
    {
        return match ($reportType) {
            'lead' => Lead::query()->visibleTo($user),
            'account' => Account::query()->visibleTo($user),
            'contact' => Contact::query()->visibleTo($user),
            'opportunity' => Opportunity::query()->visibleTo($user)->notArchived(),
            'case' => CrmCase::query()->visibleTo($user),
            default => throw new InvalidArgumentException("Unknown report type [{$reportType}]."),
        };
    }

    /**
     * @param  Builder<Model>  $query
     * @param  array{date_from?: string, date_to?: string, owner_id?: int|null}|null  $globalFilters
     */
    private function applyGlobalFilters(Builder $query, string $reportType, ?array $globalFilters): void
    {
        if ($globalFilters === null) {
            return;
        }

        $table = $query->getModel()->getTable();

        if (! empty($globalFilters['owner_id'])) {
            $query->where("{$table}.owner_id", (int) $globalFilters['owner_id']);
        }

        $dateColumn = match ($reportType) {
            'opportunity' => 'close_date',
            default => 'created_at',
        };

        if (! empty($globalFilters['date_from'])) {
            $query->whereDate("{$table}.{$dateColumn}", '>=', $globalFilters['date_from']);
        }

        if (! empty($globalFilters['date_to'])) {
            $query->whereDate("{$table}.{$dateColumn}", '<=', $globalFilters['date_to']);
        }
    }

    /**
     * @param  Builder<Model>  $query
     * @param  list<array{field: string, operator: string, value: mixed}>  $filters
     * @param  array<string, array{label: string, column: string, filterable: bool, groupable: bool}>  $fields
     */
    private function applyFilters(Builder $query, string $reportType, array $filters, array $fields): void
    {
        $table = $query->getModel()->getTable();

        foreach ($filters as $filter) {
            $column = $fields[$filter['field']]['column'];
            $qualified = "{$table}.{$column}";
            $value = $filter['value'];
            $operator = $filter['operator'];

            if (in_array($column, ['is_converted'], true)) {
                $value = filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? $value;
            }

            match ($operator) {
                'neq' => $query->where($qualified, '!=', $value),
                'contains' => $query->where($qualified, 'ilike', '%'.$value.'%'),
                'gte' => $query->where($qualified, '>=', $value),
                'lte' => $query->where($qualified, '<=', $value),
                default => $query->where($qualified, $value),
            };
        }
    }

    private function formatValue(string $reportType, string $fieldKey, mixed $value): mixed
    {
        if ($value === null) {
            return '—';
        }

        if ($fieldKey === 'is_converted') {
            return $value ? 'Yes' : 'No';
        }

        if ($value instanceof \BackedEnum) {
            return $value->value;
        }

        if ($value instanceof CarbonInterface) {
            return $value->toDateTimeString();
        }

        if ($reportType === 'lead' && $fieldKey === 'status' && is_string($value)) {
            return ucfirst(str_replace('_', ' ', $value));
        }

        return $value;
    }
}
