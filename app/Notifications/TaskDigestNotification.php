<?php

namespace App\Notifications;

use App\Models\Task;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Collection;

class TaskDigestNotification extends Notification implements ShouldQueue
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
            ->subject('Daily task digest — '.$this->tasks->count().' due today')
            ->markdown('mail.task-digest', [
                'tasks' => $this->tasks,
                'notifiable' => $notifiable,
                'url' => route('tasks.index', ['view' => 'today']),
            ]);
    }
}
