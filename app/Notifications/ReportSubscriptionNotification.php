<?php

namespace App\Notifications;

use App\Models\ReportSubscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReportSubscriptionNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public ReportSubscription $subscription,
        public string $csvContent,
        public string $filename,
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
        $report = $this->subscription->savedReport;

        return (new MailMessage)
            ->subject('Report: '.($report?->name ?? 'Subscription'))
            ->markdown('mail.report-subscription', [
                'subscription' => $this->subscription,
                'report' => $report,
                'notifiable' => $notifiable,
            ])
            ->attachData($this->csvContent, $this->filename, [
                'mime' => 'text/csv',
            ]);
    }
}
