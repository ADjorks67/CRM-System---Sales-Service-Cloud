<?php

namespace App\Console\Commands;

use App\Models\Opportunity;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('crm:archive-old-records {--days= : Override CRM_ARCHIVE_CLOSED_OPPORTUNITY_DAYS} {--dry-run : Count only, do not write}')]
#[Description('Archive closed opportunities older than the configured retention (NFR-SCAL-002)')]
class ArchiveOldRecordsCommand extends Command
{
    public function handle(): int
    {
        $days = (int) ($this->option('days') ?: config('crm.archive.closed_opportunity_days', 365));
        $dryRun = (bool) $this->option('dry-run');
        $cutoff = now()->subDays(max(1, $days));

        $query = Opportunity::query()
            ->where('is_closed', true)
            ->whereNull('archived_at')
            ->where(function ($q) use ($cutoff): void {
                $q->where('close_date', '<=', $cutoff->toDateString())
                    ->orWhere(function ($inner) use ($cutoff): void {
                        $inner->whereNull('close_date')
                            ->where('updated_at', '<=', $cutoff);
                    });
            });

        $count = (clone $query)->count();

        if ($dryRun) {
            $this->info("Dry run: {$count} closed opportunities would be archived (cutoff {$cutoff->toDateString()}).");

            return self::SUCCESS;
        }

        $updated = $query->update(['archived_at' => now()]);

        $this->info("Archived {$updated} closed opportunities older than {$days} days.");

        return self::SUCCESS;
    }
}
