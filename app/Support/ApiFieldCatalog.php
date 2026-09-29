<?php

namespace App\Support;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class ApiFieldCatalog
{
    /** @var array<string, class-string<Model>> */
    public const OBJECTS = [
        'leads' => Lead::class,
        'accounts' => Account::class,
        'contacts' => Contact::class,
        'opportunities' => Opportunity::class,
        'cases' => CrmCase::class,
    ];

    /**
     * @return array<string, list<string>>
     */
    public static function filterable(): array
    {
        return [
            'leads' => ['id', 'first_name', 'last_name', 'company', 'email', 'status', 'owner_id', 'created_at'],
            'accounts' => ['id', 'name', 'type', 'industry', 'owner_id', 'created_at'],
            'contacts' => ['id', 'first_name', 'last_name', 'email', 'account_id', 'owner_id', 'created_at'],
            'opportunities' => ['id', 'name', 'stage', 'account_id', 'owner_id', 'is_closed', 'close_date', 'created_at'],
            'cases' => ['id', 'case_number', 'subject', 'status', 'priority', 'account_id', 'owner_id', 'created_at'],
        ];
    }

    /**
     * @return array<string, list<string>>
     */
    public static function sortable(): array
    {
        return self::filterable();
    }

    /**
     * @return array<string, array<string, string>>
     */
    public static function metadata(string $object): array
    {
        $fields = self::filterable()[$object] ?? [];
        $meta = [];
        foreach ($fields as $field) {
            $meta[$field] = [
                'name' => $field,
                'filterable' => true,
                'sortable' => true,
            ];
        }

        return $meta;
    }

    public static function applyFilters(Builder $query, Request $request, string $object): Builder
    {
        $allowed = self::filterable()[$object] ?? [];
        $filters = $request->input('filter', []);

        if (! is_array($filters)) {
            return $query;
        }

        foreach ($filters as $field => $value) {
            if (! in_array($field, $allowed, true)) {
                continue;
            }

            if (is_array($value)) {
                if (isset($value['eq'])) {
                    $query->where($field, $value['eq']);
                }
                if (isset($value['like'])) {
                    $query->where($field, 'ilike', '%'.$value['like'].'%');
                }
                if (isset($value['gte'])) {
                    $query->where($field, '>=', $value['gte']);
                }
                if (isset($value['lte'])) {
                    $query->where($field, '<=', $value['lte']);
                }
            } else {
                $query->where($field, $value);
            }
        }

        return $query;
    }

    public static function applySort(Builder $query, Request $request, string $object, string $default = 'id'): Builder
    {
        $allowed = self::sortable()[$object] ?? [];
        $sort = $request->string('sort')->toString();
        $direction = strtolower($request->string('direction')->toString()) === 'desc' ? 'desc' : 'asc';

        if (! in_array($sort, $allowed, true)) {
            $sort = $default;
        }

        return $query->orderBy($sort, $direction);
    }

    public static function perPage(Request $request): int
    {
        $perPage = (int) $request->integer('per_page', ListQuery::DEFAULT_PER_PAGE);

        return max(1, min($perPage, ListQuery::MAX_PER_PAGE));
    }
}
