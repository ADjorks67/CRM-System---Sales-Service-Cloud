<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;

class ListQuery
{
    public const DEFAULT_PER_PAGE = 25;

    public const MAX_PER_PAGE = 200;

    /**
     * @param  list<string>  $allowedSorts
     * @param  list<string>  $defaultColumns
     * @param  list<string>  $availableColumns
     * @return array{query: Builder, per_page: int, sort: string, direction: string, columns: list<string>}
     */
    public static function apply(
        Builder $query,
        Request $request,
        array $allowedSorts,
        string $defaultSort = 'id',
        string $defaultDirection = 'asc',
        array $defaultColumns = [],
        array $availableColumns = [],
    ): array {
        $sort = $request->string('sort')->toString();
        if (! in_array($sort, $allowedSorts, true)) {
            $sort = $defaultSort;
        }

        $direction = strtolower($request->string('direction')->toString()) === 'desc' ? 'desc' : 'asc';
        if (! in_array($direction, ['asc', 'desc'], true)) {
            $direction = $defaultDirection;
        }

        $query->orderBy($sort, $direction);

        $perPage = (int) $request->integer('per_page', self::DEFAULT_PER_PAGE);
        $perPage = max(1, min($perPage, self::MAX_PER_PAGE));

        $columns = $defaultColumns;
        if ($availableColumns !== [] && $request->filled('columns')) {
            $requested = array_filter(array_map('trim', explode(',', $request->string('columns')->toString())));
            $columns = array_values(array_intersect($requested, $availableColumns));
            if ($columns === []) {
                $columns = $defaultColumns;
            }
        }

        return [
            'query' => $query,
            'per_page' => $perPage,
            'sort' => $sort,
            'direction' => $direction,
            'columns' => $columns,
        ];
    }

    /**
     * @param  array{query: Builder, per_page: int, sort: string, direction: string, columns: list<string>}  $applied
     */
    public static function paginate(array $applied): LengthAwarePaginator
    {
        return $applied['query']
            ->paginate($applied['per_page'])
            ->withQueryString();
    }
}
