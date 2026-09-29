<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class AdvancedSearchService
{
    /** @var array<string, class-string<Model>> */
    public const OBJECTS = [
        'lead' => Lead::class,
        'account' => Account::class,
        'contact' => Contact::class,
        'opportunity' => Opportunity::class,
        'case' => CrmCase::class,
    ];

    /** @var list<string> */
    public const OPERATORS = ['eq', 'neq', 'contains', 'starts_with', 'gte', 'lte', 'gt', 'lt'];

    /**
     * @return array<string, string>
     */
    public function objectLabels(): array
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
     * Allow-listed searchable fields per object (FR-SRCH-002 fields + dates).
     *
     * @return array<string, array<string, array{label: string, type: string}>>
     */
    public function fieldCatalog(): array
    {
        return [
            'lead' => [
                'first_name' => ['label' => 'First Name', 'type' => 'string'],
                'last_name' => ['label' => 'Last Name', 'type' => 'string'],
                'company' => ['label' => 'Company', 'type' => 'string'],
                'email' => ['label' => 'Email', 'type' => 'string'],
                'phone' => ['label' => 'Phone', 'type' => 'string'],
                'status' => ['label' => 'Status', 'type' => 'string'],
                'created_at' => ['label' => 'Created Date', 'type' => 'date'],
                'updated_at' => ['label' => 'Last Modified', 'type' => 'date'],
            ],
            'account' => [
                'name' => ['label' => 'Account Name', 'type' => 'string'],
                'phone' => ['label' => 'Phone', 'type' => 'string'],
                'website' => ['label' => 'Website', 'type' => 'string'],
                'industry' => ['label' => 'Industry', 'type' => 'string'],
                'type' => ['label' => 'Type', 'type' => 'string'],
                'created_at' => ['label' => 'Created Date', 'type' => 'date'],
                'updated_at' => ['label' => 'Last Modified', 'type' => 'date'],
            ],
            'contact' => [
                'first_name' => ['label' => 'First Name', 'type' => 'string'],
                'last_name' => ['label' => 'Last Name', 'type' => 'string'],
                'email' => ['label' => 'Email', 'type' => 'string'],
                'phone' => ['label' => 'Phone', 'type' => 'string'],
                'title' => ['label' => 'Title', 'type' => 'string'],
                'created_at' => ['label' => 'Created Date', 'type' => 'date'],
                'updated_at' => ['label' => 'Last Modified', 'type' => 'date'],
            ],
            'opportunity' => [
                'name' => ['label' => 'Opportunity Name', 'type' => 'string'],
                'stage' => ['label' => 'Stage', 'type' => 'string'],
                'amount' => ['label' => 'Amount', 'type' => 'number'],
                'close_date' => ['label' => 'Close Date', 'type' => 'date'],
                'created_at' => ['label' => 'Created Date', 'type' => 'date'],
                'updated_at' => ['label' => 'Last Modified', 'type' => 'date'],
            ],
            'case' => [
                'case_number' => ['label' => 'Case Number', 'type' => 'string'],
                'subject' => ['label' => 'Subject', 'type' => 'string'],
                'description' => ['label' => 'Description', 'type' => 'string'],
                'status' => ['label' => 'Status', 'type' => 'string'],
                'priority' => ['label' => 'Priority', 'type' => 'string'],
                'created_at' => ['label' => 'Created Date', 'type' => 'date'],
                'updated_at' => ['label' => 'Last Modified', 'type' => 'date'],
            ],
        ];
    }

    /**
     * @param  array{
     *     object_type: string,
     *     conditions?: list<array{field: string, operator: string, value?: mixed, logic?: string}>,
     *     date_from?: string|null,
     *     date_to?: string|null,
     *     date_field?: string|null
     * }  $definition
     * @return Collection<int, Model>
     */
    public function search(User $user, array $definition, int $limit = 100): Collection
    {
        $objectType = $definition['object_type'] ?? '';
        if (! isset(self::OBJECTS[$objectType])) {
            throw ValidationException::withMessages([
                'object_type' => 'Invalid object type.',
            ]);
        }

        $modelClass = self::OBJECTS[$objectType];

        if (! $user->can('viewAny', $modelClass)) {
            return new Collection;
        }

        /** @var Builder<Model> $builder */
        $builder = $modelClass::query()->visibleTo($user);

        if ($modelClass === Opportunity::class) {
            $builder->notArchived();
        }

        $fields = $this->fieldCatalog()[$objectType] ?? [];
        $conditions = $definition['conditions'] ?? [];

        foreach ($conditions as $index => $condition) {
            $field = (string) ($condition['field'] ?? '');
            $operator = (string) ($condition['operator'] ?? 'eq');
            $value = $condition['value'] ?? null;
            $logic = strtoupper((string) ($condition['logic'] ?? 'AND'));

            if (! isset($fields[$field]) || ! in_array($operator, self::OPERATORS, true)) {
                continue;
            }

            $apply = function (Builder $query) use ($field, $operator, $value, $fields): void {
                $this->applyCondition($query, $field, $operator, $value, $fields[$field]['type']);
            };

            if ($index === 0) {
                $builder->where($apply);
            } elseif ($logic === 'OR') {
                $builder->orWhere($apply);
            } else {
                $builder->where($apply);
            }
        }

        $dateField = (string) ($definition['date_field'] ?? 'created_at');
        if (! isset($fields[$dateField]) || $fields[$dateField]['type'] !== 'date') {
            $dateField = 'created_at';
        }

        if (! empty($definition['date_from'])) {
            $builder->whereDate($dateField, '>=', $definition['date_from']);
        }

        if (! empty($definition['date_to'])) {
            $builder->whereDate($dateField, '<=', $definition['date_to']);
        }

        return $builder
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * Sanitize a definition to allow-listed fields/operators only.
     *
     * @param  array<string, mixed>  $definition
     * @return array{
     *     object_type: string,
     *     conditions: list<array{field: string, operator: string, value: mixed, logic: string}>,
     *     date_from: string|null,
     *     date_to: string|null,
     *     date_field: string
     * }
     */
    public function sanitizeDefinition(array $definition): array
    {
        $objectType = (string) ($definition['object_type'] ?? '');
        if (! isset(self::OBJECTS[$objectType])) {
            throw ValidationException::withMessages([
                'object_type' => 'Invalid object type.',
            ]);
        }

        $fields = $this->fieldCatalog()[$objectType];
        $conditions = [];

        foreach ($definition['conditions'] ?? [] as $condition) {
            if (! is_array($condition)) {
                continue;
            }

            $field = (string) ($condition['field'] ?? '');
            $operator = (string) ($condition['operator'] ?? '');
            if (! isset($fields[$field]) || ! in_array($operator, self::OPERATORS, true)) {
                continue;
            }

            $logic = strtoupper((string) ($condition['logic'] ?? 'AND'));
            if (! in_array($logic, ['AND', 'OR'], true)) {
                $logic = 'AND';
            }

            $conditions[] = [
                'field' => $field,
                'operator' => $operator,
                'value' => $condition['value'] ?? null,
                'logic' => $logic,
            ];
        }

        $dateField = (string) ($definition['date_field'] ?? 'created_at');
        if (! isset($fields[$dateField]) || $fields[$dateField]['type'] !== 'date') {
            $dateField = 'created_at';
        }

        return [
            'object_type' => $objectType,
            'conditions' => $conditions,
            'date_from' => $definition['date_from'] ?? null,
            'date_to' => $definition['date_to'] ?? null,
            'date_field' => $dateField,
        ];
    }

    private function applyCondition(Builder $query, string $field, string $operator, mixed $value, string $type): void
    {
        $value = is_string($value) ? trim($value) : $value;

        match ($operator) {
            'eq' => $query->where($field, '=', $value),
            'neq' => $query->where($field, '!=', $value),
            'contains' => $query->where($field, 'ilike', '%'.$value.'%'),
            'starts_with' => $query->where($field, 'ilike', $value.'%'),
            'gte' => $query->where($field, '>=', $value),
            'lte' => $query->where($field, '<=', $value),
            'gt' => $query->where($field, '>', $value),
            'lt' => $query->where($field, '<', $value),
            default => null,
        };
    }
}
