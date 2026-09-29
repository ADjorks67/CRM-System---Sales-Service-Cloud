<?php

namespace App\Reports\Opportunities;

use App\Models\Opportunity;
use App\Models\User;
use App\Reports\PrebuiltReport;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AvgDealLengthReport extends PrebuiltReport
{
    public function key(): string
    {
        return 'avg-deal-length';
    }

    public function title(): string
    {
        return 'Avg. Deal Length';
    }

    public function category(): string
    {
        return 'Opportunities';
    }

    public function columns(): array
    {
        return ['Metric', 'Value'];
    }

    public function rows(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $avgDays = Opportunity::query()
            ->visibleTo($user)
            ->notArchived()
            ->where('is_won', true)
            ->whereBetween('close_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (close_date::timestamp - created_at)) / 86400.0) as avg_days')
            ->value('avg_days');

        return collect([
            ['Metric' => 'Average days to close (won)', 'Value' => $avgDays !== null ? round((float) $avgDays, 1) : '—'],
        ]);
    }
}
