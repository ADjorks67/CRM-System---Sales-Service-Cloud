<?php

namespace App\Console\Commands;

use App\Models\Task;
use App\Notifications\TaskReminderNotification;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendTaskRemindersCommand extends Command
{
    protected $signature = 'crm:send-task-reminders';

    protected $description = 'Send due task reminders that are ready [FR-TASK-003]';

    public function handle(): int
    {
        $tasks = Task::query()
            ->open()
            ->where('reminder_set', true)
            ->whereNotNull('reminder_at')
            ->whereNull('reminder_sent_at')
            ->where('reminder_at', '<=', now())
            ->with('owner')
            ->get();

        $sent = 0;

        foreach ($tasks as $task) {
            if ($task->owner === null) {
                continue;
            }

            Notification::send($task->owner, new TaskReminderNotification($task));
            $task->forceFill(['reminder_sent_at' => now()])->save();
            $sent++;
        }

        $this->info("Sent {$sent} reminder(s).");

        return self::SUCCESS;
    }
}
