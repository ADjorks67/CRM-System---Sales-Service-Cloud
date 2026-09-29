<?php

namespace App\Services;

use App\Models\RecentRecord;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;

class RecentRecordService
{
    public function recordView(User $user, Model $record): void
    {
        RecentRecord::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'viewable_type' => $record->getMorphClass(),
                'viewable_id' => $record->getKey(),
            ],
            [
                'viewed_at' => now(),
            ],
        );
    }

    /**
     * @return Collection<int, RecentRecord>
     */
    public function recentFor(User $user, int $limit = 5): Collection
    {
        return RecentRecord::query()
            ->where('user_id', $user->id)
            ->with('viewable')
            ->orderByDesc('viewed_at')
            ->limit($limit)
            ->get()
            ->filter(fn (RecentRecord $row) => $row->viewable !== null)
            ->values();
    }
}
