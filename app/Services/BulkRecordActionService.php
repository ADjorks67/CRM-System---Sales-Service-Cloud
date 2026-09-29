<?php

namespace App\Services;

use App\Enums\LeadStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BulkRecordActionService
{
    public function __construct(private readonly OwnershipHistoryService $ownershipHistory) {}

    /**
     * @param  Collection<int, Model>  $records
     * @param  array{notify_new_owner?: bool, transfer_open_activities?: bool, notes?: string|null, status?: string|null}  $options
     * @return array{processed: int, skipped: int}
     */
    public function run(string $action, Collection $records, User $actor, ?User $newOwner = null, array $options = []): array
    {
        return match ($action) {
            'change_owner' => $this->changeOwner($records, $actor, $newOwner, $options),
            'delete' => $this->delete($records, $actor),
            'change_status' => $this->changeStatus($records, $actor, $options['status'] ?? null),
            default => throw ValidationException::withMessages([
                'action' => 'Unsupported bulk action.',
            ]),
        };
    }

    /**
     * @param  Collection<int, Model>  $records
     * @param  array{notify_new_owner?: bool, transfer_open_activities?: bool, notes?: string|null}  $options
     * @return array{processed: int, skipped: int}
     */
    private function changeOwner(Collection $records, User $actor, ?User $newOwner, array $options): array
    {
        if ($newOwner === null) {
            throw ValidationException::withMessages([
                'owner_id' => 'A new owner is required.',
            ]);
        }

        $processed = 0;
        $skipped = 0;

        DB::transaction(function () use ($records, $actor, $newOwner, $options, &$processed, &$skipped): void {
            foreach ($records as $record) {
                if (! $actor->can('changeOwner', $record)) {
                    $skipped++;

                    continue;
                }

                $this->ownershipHistory->changeOwner($record, $newOwner, $actor, $options);
                $processed++;
            }
        });

        return compact('processed', 'skipped');
    }

    /**
     * @param  Collection<int, Model>  $records
     * @return array{processed: int, skipped: int}
     */
    private function delete(Collection $records, User $actor): array
    {
        $processed = 0;
        $skipped = 0;

        DB::transaction(function () use ($records, $actor, &$processed, &$skipped): void {
            foreach ($records as $record) {
                if (! $actor->can('delete', $record)) {
                    $skipped++;

                    continue;
                }

                $record->delete();
                $processed++;
            }
        });

        return compact('processed', 'skipped');
    }

    /**
     * @param  Collection<int, Model>  $records
     * @return array{processed: int, skipped: int}
     */
    private function changeStatus(Collection $records, User $actor, ?string $status): array
    {
        if ($status === null || $status === '') {
            throw ValidationException::withMessages([
                'status' => 'A status is required.',
            ]);
        }

        if ($status === LeadStatus::Converted->value) {
            throw ValidationException::withMessages([
                'status' => 'Converted status is set by lead conversion (Phase 4).',
            ]);
        }

        $processed = 0;
        $skipped = 0;

        DB::transaction(function () use ($records, $actor, $status, &$processed, &$skipped): void {
            foreach ($records as $record) {
                if (! $actor->can('changeStatus', $record)) {
                    $skipped++;

                    continue;
                }

                $record->forceFill(['status' => $status])->save();
                $processed++;
            }
        });

        return compact('processed', 'skipped');
    }
}
