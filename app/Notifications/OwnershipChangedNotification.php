<?php

namespace App\Notifications;

use App\Models\OwnershipHistory;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OwnershipChangedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public Model $record,
        public OwnershipHistory $history,
        public User $changedBy,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $label = method_exists($this->record, 'displayName')
            ? $this->record->displayName()
            : class_basename($this->record).' #'.$this->record->getKey();

        return (new MailMessage)
            ->subject('Record assigned to you: '.$label)
            ->markdown('mail.ownership-changed', [
                'record' => $this->record,
                'history' => $this->history,
                'changedBy' => $this->changedBy,
                'label' => $label,
                'objectType' => $this->record->getMorphClass(),
                'notifiable' => $notifiable,
            ]);
    }
}
