<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class TaskOverdueNotification extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * @param  Collection<int, Task>  $tasks
     */
    public function __construct(public Collection $tasks) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Overdue tasks — '.$this->tasks->count())
            ->markdown('mail.task-overdue', [
                'tasks' => $this->tasks,
                'notifiable' => $notifiable,
                'url' => route('tasks.index', ['view' => 'overdue']),
            ]);
    }
}
