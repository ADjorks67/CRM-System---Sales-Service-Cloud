<?php

namespace App\Console\Commands;

use App\Models\ReportSubscription;
use App\Notifications\ReportSubscriptionNotification;
use App\Services\ReportBuilderService;
use App\Services\ReportExportService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Notification;

class SendReportSubscriptionsCommand extends Command
{
    protected $signature = 'crm:send-report-subscriptions';

    protected $description = 'Send due report subscription emails with CSV attachments [FR-RPT-006]';

    public function __construct(
        private readonly ReportBuilderService $reportBuilder,
        private readonly ReportExportService $reportExport,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $due = ReportSubscription::query()
            ->where('next_run_at', '<=', now())
            ->with(['user', 'savedReport'])
            ->get();

        $sent = 0;

        foreach ($due as $subscription) {
            $user = $subscription->user;
            $report = $subscription->savedReport;

            if ($user === null || $report === null) {
                continue;
            }

            if (! $user->can('view', $report)) {
                continue;
            }

            $result = $this->reportBuilder->runSaved($user, $report);
            $filename = str($report->name)->slug()->append('.csv')->toString();
            $csv = $this->reportExport->csvContent(
                $result['columns'],
                $result['column_labels'],
                $result['rows'],
            );

            Notification::send($user, new ReportSubscriptionNotification($subscription, $csv, $filename));

            $subscription->forceFill([
                'last_sent_at' => now(),
                'next_run_at' => $subscription->computeNextRunAt(now()),
            ])->save();

            $sent++;
        }

        $this->info("Sent {$sent} subscription(s).");

        return self::SUCCESS;
    }
}
