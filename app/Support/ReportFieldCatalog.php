<?php

namespace App\Support;

use Illuminate\Validation\Rule;
use InvalidArgumentException;

class ReportFieldCatalog
{
    /** @var list<string> */
    public const REPORT_TYPES = ['lead', 'account', 'contact', 'opportunity', 'case'];

    /** @var list<string> */
    public const FILTER_OPERATORS = ['eq', 'neq', 'contains', 'gte', 'lte'];

    /**
     * @return array<string, array{label: string, column: string, filterable: bool, groupable: bool}>
     */
    public static function fields(string $reportType): array
    {
        return match ($reportType) {
            'lead' => [
                'company' => ['label' => 'Company', 'column' => 'company', 'filterable' => true, 'groupable' => true],
                'first_name' => ['label' => 'First Name', 'column' => 'first_name', 'filterable' => true, 'groupable' => false],
                'last_name' => ['label' => 'Last Name', 'column' => 'last_name', 'filterable' => true, 'groupable' => false],
                'status' => ['label' => 'Status', 'column' => 'status', 'filterable' => true, 'groupable' => true],
                'email' => ['label' => 'Email', 'column' => 'email', 'filterable' => true, 'groupable' => false],
                'lead_source' => ['label' => 'Lead Source', 'column' => 'lead_source', 'filterable' => true, 'groupable' => true],
                'rating' => ['label' => 'Rating', 'column' => 'rating', 'filterable' => true, 'groupable' => true],
                'is_converted' => ['label' => 'Converted', 'column' => 'is_converted', 'filterable' => true, 'groupable' => true],
                'converted_at' => ['label' => 'Converted At', 'column' => 'converted_at', 'filterable' => true, 'groupable' => false],
                'created_at' => ['label' => 'Created', 'column' => 'created_at', 'filterable' => true, 'groupable' => false],
            ],
            'account' => [
                'name' => ['label' => 'Account Name', 'column' => 'name', 'filterable' => true, 'groupable' => true],
                'type' => ['label' => 'Type', 'column' => 'type', 'filterable' => true, 'groupable' => true],
                'industry' => ['label' => 'Industry', 'column' => 'industry', 'filterable' => true, 'groupable' => true],
                'phone' => ['label' => 'Phone', 'column' => 'phone', 'filterable' => true, 'groupable' => false],
                'billing_city' => ['label' => 'Billing City', 'column' => 'billing_city', 'filterable' => true, 'groupable' => true],
                'created_at' => ['label' => 'Created', 'column' => 'created_at', 'filterable' => true, 'groupable' => false],
            ],
            'contact' => [
                'last_name' => ['label' => 'Last Name', 'column' => 'last_name', 'filterable' => true, 'groupable' => false],
                'first_name' => ['label' => 'First Name', 'column' => 'first_name', 'filterable' => true, 'groupable' => false],
                'email' => ['label' => 'Email', 'column' => 'email', 'filterable' => true, 'groupable' => false],
                'phone' => ['label' => 'Phone', 'column' => 'phone', 'filterable' => true, 'groupable' => false],
                'title' => ['label' => 'Title', 'column' => 'title', 'filterable' => true, 'groupable' => true],
                'lead_source' => ['label' => 'Lead Source', 'column' => 'lead_source', 'filterable' => true, 'groupable' => true],
                'created_at' => ['label' => 'Created', 'column' => 'created_at', 'filterable' => true, 'groupable' => false],
            ],
            'opportunity' => [
                'name' => ['label' => 'Name', 'column' => 'name', 'filterable' => true, 'groupable' => false],
                'stage' => ['label' => 'Stage', 'column' => 'stage', 'filterable' => true, 'groupable' => true],
                'amount' => ['label' => 'Amount', 'column' => 'amount', 'filterable' => true, 'groupable' => false],
                'close_date' => ['label' => 'Close Date', 'column' => 'close_date', 'filterable' => true, 'groupable' => false],
                'lead_source' => ['label' => 'Lead Source', 'column' => 'lead_source', 'filterable' => true, 'groupable' => true],
                'probability' => ['label' => 'Probability', 'column' => 'probability', 'filterable' => true, 'groupable' => false],
                'created_at' => ['label' => 'Created', 'column' => 'created_at', 'filterable' => true, 'groupable' => false],
            ],
            'case' => [
                'case_number' => ['label' => 'Case Number', 'column' => 'case_number', 'filterable' => true, 'groupable' => false],
                'subject' => ['label' => 'Subject', 'column' => 'subject', 'filterable' => true, 'groupable' => false],
                'status' => ['label' => 'Status', 'column' => 'status', 'filterable' => true, 'groupable' => true],
                'priority' => ['label' => 'Priority', 'column' => 'priority', 'filterable' => true, 'groupable' => true],
                'origin' => ['label' => 'Origin', 'column' => 'origin', 'filterable' => true, 'groupable' => true],
                'created_at' => ['label' => 'Created', 'column' => 'created_at', 'filterable' => true, 'groupable' => false],
            ],
            default => throw new InvalidArgumentException("Unknown report type [{$reportType}]."),
        };
    }

    /**
     * @return array<string, string>
     */
    public static function typeOptions(): array
    {
        return [
            'lead' => 'Leads',
            'account' => 'Accounts',
            'contact' => 'Contacts',
            'opportunity' => 'Opportunities',
            'case' => 'Cases',
        ];
    }

    /**
     * @param  array<string, mixed>  $definition
     * @return array<string, mixed>
     */
    public static function sanitizeDefinition(string $reportType, array $definition): array
    {
        $fields = self::fields($reportType);
        $columns = collect($definition['columns'] ?? [])
            ->filter(fn ($key) => is_string($key) && isset($fields[$key]))
            ->values()
            ->all();

        if ($columns === []) {
            $columns = [array_key_first($fields)];
        }

        $groupBy = $definition['group_by'] ?? null;
        if ($groupBy !== null && (! is_string($groupBy) || ! isset($fields[$groupBy]) || ! $fields[$groupBy]['groupable'])) {
            $groupBy = null;
        }

        $filters = [];
        foreach ($definition['filters'] ?? [] as $filter) {
            if (! is_array($filter)) {
                continue;
            }

            $field = $filter['field'] ?? null;
            $operator = $filter['operator'] ?? 'eq';
            $value = $filter['value'] ?? null;

            if (! is_string($field) || ! isset($fields[$field]) || ! $fields[$field]['filterable']) {
                continue;
            }

            if (! in_array($operator, self::FILTER_OPERATORS, true)) {
                $operator = 'eq';
            }

            if ($value === null || $value === '') {
                continue;
            }

            $filters[] = compact('field', 'operator', 'value');
        }

        $chart = null;
        if (isset($definition['chart']) && is_array($definition['chart'])) {
            $chartType = $definition['chart']['type'] ?? 'bar';
            $chartColumn = $definition['chart']['column'] ?? null;

            if (is_string($chartColumn) && isset($fields[$chartColumn]) && in_array($chartType, ['bar', 'donut', 'line'], true)) {
                $chart = ['type' => $chartType, 'column' => $chartColumn];
            }
        }

        return [
            'columns' => $columns,
            'filters' => $filters,
            'group_by' => $groupBy,
            'chart' => $chart,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function validationRules(string $reportType): array
    {
        $fieldKeys = array_keys(self::fields($reportType));

        return [
            'report_type' => ['required', 'string', Rule::in(self::REPORT_TYPES)],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:500'],
            'folder' => ['nullable', 'string', 'max:64'],
            'is_private' => ['sometimes', 'boolean'],
            'definition.columns' => ['required', 'array', 'min:1'],
            'definition.columns.*' => ['string', Rule::in($fieldKeys)],
            'definition.filters' => ['nullable', 'array'],
            'definition.filters.*.field' => ['required', 'string', Rule::in($fieldKeys)],
            'definition.filters.*.operator' => ['nullable', 'string', Rule::in(self::FILTER_OPERATORS)],
            'definition.filters.*.value' => ['nullable'],
            'definition.group_by' => ['nullable', 'string', Rule::in($fieldKeys)],
            'definition.chart.type' => ['nullable', 'string', Rule::in(['bar', 'donut', 'line'])],
            'definition.chart.column' => ['nullable', 'string', Rule::in($fieldKeys)],
        ];
    }
}
