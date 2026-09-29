<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Models\User;
use App\Notifications\TaskOverdueNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendOverdueTaskNotificationsCommand extends Command
{
    protected $signature = 'crm:send-overdue-task-notifications';

    protected $description = 'Notify owners of overdue open tasks [FR-TASK-003]';

    public function handle(): int
    {
        $tasks = Task::query()
            ->overdue()
            ->with('owner')
            ->get()
            ->groupBy('owner_id');

        foreach ($tasks as $ownerTasks) {
            /** @var User|null $owner */
            $owner = $ownerTasks->first()?->owner;
            if ($owner === null) {
                continue;
            }

            Notification::send($owner, new TaskOverdueNotification($ownerTasks->values()));
        }

        $this->info('Sent overdue notices to '.$tasks->count().' user(s).');

        return self::SUCCESS;
    }
}
