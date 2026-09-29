<?php

namespace App\Services;

use App\Models\Event;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

/**
 * Query API for Home widgets (FR-HOME-005) — Dev B calendar/widgets consume these.
 * No calendar UI here.
 */
class EventQueryService
{
    /**
     * @return Collection<int, Event>
     */
    public function todayForUser(User $user, int $limit = 25): Collection
    {
        return Event::query()
            ->visibleTo($user)
            ->startingToday()
            ->with(['owner', 'related'])
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Event>
     */
    public function upcomingForUser(User $user, int $limit = 10): Collection
    {
        return Event::query()
            ->visibleTo($user)
            ->where('starts_at', '>=', now())
            ->with(['owner', 'related'])
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }

    /**
     * @return Collection<int, Event>
     */
    public function openRelatedTo(string $morphType, int $relatedId, User $user, int $limit = 10): Collection
    {
        return Event::query()
            ->visibleTo($user)
            ->open()
            ->where('related_type', $morphType)
            ->where('related_id', $relatedId)
            ->with('owner')
            ->orderBy('starts_at')
            ->limit($limit)
            ->get();
    }
}
