<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskDigestNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendTaskDigestsCommand extends Command
{
    protected $signature = 'crm:send-task-digests';

    protected $description = 'Send daily digests for tasks due today [FR-TASK-003]';

    public function handle(): int
    {
        $tasks = Task::query()
            ->dueToday()
            ->with('owner')
            ->get()
            ->groupBy('owner_id');

        foreach ($tasks as $ownerId => $ownerTasks) {
            /** @var User|null $owner */
            $owner = $ownerTasks->first()?->owner;
            if ($owner === null) {
                continue;
            }

            Notification::send($owner, new TaskDigestNotification($ownerTasks->values()));
        }

        $this->info('Sent digests to '.$tasks->count().' user(s).');

        return self::SUCCESS;
    }
}
