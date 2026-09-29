<?php

namespace App\Services;

use App\Models\Event;
use App\Models\OwnershipHistory;
use App\Models\Task;
use App\Models\User;
use App\Notifications\OwnershipChangedNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class OwnershipHistoryService
{
    /**
     * @param  array{notify_new_owner?: bool, transfer_open_activities?: bool, notes?: string|null}  $options
     */
    public function changeOwner(Model $record, User $newOwner, User $changedBy, array $options = []): OwnershipHistory
    {
        return DB::transaction(function () use ($record, $newOwner, $changedBy, $options): OwnershipHistory {
            $previousOwnerId = $record->getAttribute('owner_id');

            $record->forceFill(['owner_id' => $newOwner->id])->save();

            $history = OwnershipHistory::query()->create([
                'ownable_type' => $record->getMorphClass(),
                'ownable_id' => $record->getKey(),
                'previous_owner_id' => $previousOwnerId,
                'new_owner_id' => $newOwner->id,
                'changed_by' => $changedBy->id,
                'notify_new_owner' => (bool) ($options['notify_new_owner'] ?? false),
                'transfer_open_activities' => (bool) ($options['transfer_open_activities'] ?? false),
                'notes' => $options['notes'] ?? null,
            ]);

            if ($history->transfer_open_activities) {
                $this->transferOpenActivities($record, $newOwner);
            }

            if ($history->notify_new_owner && (int) $newOwner->id !== (int) $changedBy->id) {
                Notification::send(
                    $newOwner,
                    new OwnershipChangedNotification($record, $history, $changedBy),
                );
            }

            return $history;
        });
    }

    /**
     * Reassign open tasks and future events related to the record.
     */
    private function transferOpenActivities(Model $record, User $newOwner): void
    {
        Task::query()
            ->open()
            ->where('related_type', $record->getMorphClass())
            ->where('related_id', $record->getKey())
            ->update(['owner_id' => $newOwner->id]);

        Event::query()
            ->open()
            ->where('related_type', $record->getMorphClass())
            ->where('related_id', $record->getKey())
            ->update(['owner_id' => $newOwner->id]);
    }
}
