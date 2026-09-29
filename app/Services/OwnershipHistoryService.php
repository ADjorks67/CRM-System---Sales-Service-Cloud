<?php

namespace App\Services;

use App\Models\OwnershipHistory;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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

            return OwnershipHistory::query()->create([
                'ownable_type' => $record->getMorphClass(),
                'ownable_id' => $record->getKey(),
                'previous_owner_id' => $previousOwnerId,
                'new_owner_id' => $newOwner->id,
                'changed_by' => $changedBy->id,
                'notify_new_owner' => (bool) ($options['notify_new_owner'] ?? false),
                'transfer_open_activities' => (bool) ($options['transfer_open_activities'] ?? false),
                'notes' => $options['notes'] ?? null,
            ]);
        });
    }
}
