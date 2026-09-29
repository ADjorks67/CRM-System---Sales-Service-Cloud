<?php

namespace App\Http\Controllers\Concerns;

use App\Http\Requests\BulkRecordActionRequest;
use App\Http\Requests\ChangeOwnerRequest;
use App\Models\User;
use App\Services\BulkRecordActionService;
use App\Services\OwnershipHistoryService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Gate;

trait HandlesRecordOwnership
{
    abstract protected function ownershipHistory(): OwnershipHistoryService;

    abstract protected function bulkRecordActions(): BulkRecordActionService;

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function changeRecordOwner(ChangeOwnerRequest $request, Model $record, string $showRoute): RedirectResponse
    {
        Gate::authorize('changeOwner', $record);

        /** @var User $newOwner */
        $newOwner = User::query()->findOrFail($request->validated('owner_id'));

        $this->ownershipHistory()->changeOwner(
            $record,
            $newOwner,
            $request->user(),
            [
                'notify_new_owner' => $request->boolean('notify_new_owner'),
                'transfer_open_activities' => $request->boolean('transfer_open_activities'),
                'notes' => $request->validated('notes'),
            ],
        );

        return redirect()
            ->route($showRoute, $record)
            ->with('success', 'Owner updated successfully.');
    }

    /**
     * @param  class-string<Model>  $modelClass
     */
    protected function runBulkAction(
        BulkRecordActionRequest $request,
        string $modelClass,
        string $indexRoute,
    ): RedirectResponse {
        Gate::authorize('viewAny', $modelClass);

        /** @var User $actor */
        $actor = $request->user();

        $records = $modelClass::query()
            ->visibleTo($actor)
            ->whereIn('id', $request->validated('ids'))
            ->get();

        $newOwner = null;
        if ($request->validated('action') === 'change_owner') {
            $newOwner = User::query()->findOrFail($request->validated('owner_id'));
        }

        $result = $this->bulkRecordActions()->run(
            $request->validated('action'),
            $records,
            $actor,
            $newOwner,
            [
                'notify_new_owner' => $request->boolean('notify_new_owner'),
                'transfer_open_activities' => $request->boolean('transfer_open_activities'),
                'notes' => $request->validated('notes'),
                'status' => $request->validated('status'),
            ],
        );

        $message = sprintf(
            'Bulk action complete: %d processed, %d skipped.',
            $result['processed'],
            $result['skipped'],
        );

        return redirect()
            ->route($indexRoute)
            ->with('success', $message);
    }

    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function applySearch(Builder $query, ?string $search, array $columns): Builder
    {
        $search = trim((string) $search);

        if ($search === '') {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($search, $columns): void {
            foreach ($columns as $column) {
                $inner->orWhere($column, 'ilike', '%'.$search.'%');
            }
        });
    }
}
