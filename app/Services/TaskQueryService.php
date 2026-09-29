<?php

namespace App\Services;

use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Query API for Home widgets (FR-HOME-004) — Dev B consumes these methods.
 * Do not render Home widgets here.
 */
class TaskQueryService
{
    /**
     * @return Collection<int, Task>
     */
    public function openForUser(User $user, int $limit = 10): Collection
    {
        return Task::query()
            ->visibleTo($user)
            ->open()
            ->with(['owner', 'related', 'contact'])
            ->orderByRaw('due_date ASC NULLS LAST')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Task>
     */
    public function dueToday(User $user, int $limit = 25): Collection
    {
        return Task::query()
            ->visibleTo($user)
            ->dueToday()
            ->with(['owner', 'related', 'contact'])
            ->orderBy('priority')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Task>
     */
    public function overdue(User $user, int $limit = 25): Collection
    {
        return Task::query()
            ->visibleTo($user)
            ->overdue()
            ->with(['owner', 'related', 'contact'])
            ->orderBy('due_date')
            ->limit($limit)
            ->get();
    }

    /**
     * Open tasks related to a CRM record (related lists).
     *
     * @return Collection<int, Task>
     */
    public function openRelatedTo(string $morphType, int $relatedId, User $user, int $limit = 10): Collection
    {
        return Task::query()
            ->visibleTo($user)
            ->open()
            ->where('related_type', $morphType)
            ->where('related_id', $relatedId)
            ->with('owner')
            ->orderByRaw('due_date ASC NULLS LAST')
            ->limit($limit)
            ->get();
    }
}
