<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Contact;
use App\Models\CrmCase;
use App\Models\Lead;
use App\Models\Opportunity;
use App\Models\RecentSearch;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as SupportCollection;

class GlobalSearchService
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
     * @return array<string, Collection<int, Model>>
     */
    public function suggest(User $user, string $query, int $perObject = 5): array
    {
        $query = trim($query);

        if (mb_strlen($query) < 2) {
            return [];
        }

        $results = [];

        foreach (self::OBJECTS as $key => $modelClass) {
            if (! $user->can('viewAny', $modelClass)) {
                continue;
            }

            $results[$key] = $this->searchObject($user, $modelClass, $query, $perObject);
        }

        return $results;
    }

    /**
     * @return array{results: Collection<int, Model>, type: string|null}
     */
    public function search(User $user, string $query, ?string $type = null, int $limit = 50): array
    {
        $query = trim($query);
        $this->rememberSearch($user, $query);

        if ($type !== null && isset(self::OBJECTS[$type])) {
            $modelClass = self::OBJECTS[$type];
            $results = $user->can('viewAny', $modelClass)
                ? $this->searchObject($user, $modelClass, $query, $limit)
                : new Collection;

            return ['results' => $results, 'type' => $type];
        }

        $merged = new Collection;

        foreach (self::OBJECTS as $key => $modelClass) {
            if (! $user->can('viewAny', $modelClass)) {
                continue;
            }

            foreach ($this->searchObject($user, $modelClass, $query, $limit) as $record) {
                $merged->push($record);
            }
        }

        return ['results' => $merged->take($limit)->values(), 'type' => null];
    }

    /**
     * @return SupportCollection<int, string>
     */
    public function recentQueries(User $user, int $limit = 8): SupportCollection
    {
        return RecentSearch::query()
            ->where('user_id', $user->id)
            ->orderByDesc('searched_at')
            ->limit($limit)
            ->pluck('query')
            ->unique()
            ->values();
    }

    public function rememberSearch(User $user, string $query): void
    {
        $query = trim($query);

        if (mb_strlen($query) < 2) {
            return;
        }

        RecentSearch::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'query' => mb_substr($query, 0, 255),
            ],
            [
                'searched_at' => now(),
            ],
        );
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return Collection<int, Model>
     */
    private function searchObject(User $user, string $modelClass, string $query, int $limit): Collection
    {
        /** @var Builder<Model> $builder */
        $builder = $modelClass::query()->visibleTo($user);

        if ($modelClass === Opportunity::class) {
            $builder->notArchived();
        }

        $like = '%'.$query.'%';

        $builder->where(function (Builder $inner) use ($modelClass, $like): void {
            foreach ($this->searchColumns($modelClass) as $column) {
                if (str_contains($column, '.')) {
                    [$relation, $relColumn] = explode('.', $column, 2);
                    $inner->orWhereHas($relation, function (Builder $rel) use ($relColumn, $like): void {
                        $rel->where($relColumn, 'ilike', $like);
                    });
                } else {
                    $inner->orWhere($column, 'ilike', $like);
                }
            }
        });

        return $builder
            ->orderByDesc('updated_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @param  class-string<Model>  $modelClass
     * @return list<string>
     */
    private function searchColumns(string $modelClass): array
    {
        return match ($modelClass) {
            Lead::class => ['first_name', 'last_name', 'company', 'email', 'phone'],
            Account::class => ['name', 'phone', 'website'],
            Contact::class => ['first_name', 'last_name', 'email', 'phone', 'account.name'],
            Opportunity::class => ['name', 'account.name', 'amount'],
            CrmCase::class => ['case_number', 'subject', 'description'],
            default => [],
        };
    }
}
