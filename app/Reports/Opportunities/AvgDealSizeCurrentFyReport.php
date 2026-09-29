<?php

namespace App\Reports\Opportunities;

use App\Models\Opportunity;
use App\Models\User;
use App\Reports\PrebuiltReport;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AvgDealSizeCurrentFyReport extends PrebuiltReport
{
    public function key(): string
    {
        return 'avg-deal-size-current-fy';
    }

    public function title(): string
    {
        return 'Avg Deal Size – Current FY';
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
        $query = Opportunity::query()
            ->visibleTo($user)
            ->notArchived()
            ->where('is_won', true)
            ->whereBetween('close_date', [$from->toDateString(), $to->toDateString()]);

        $count = (clone $query)->count();
        $avg = (float) (clone $query)->avg('amount');

        return collect([
            ['Metric' => 'Closed won deals', 'Value' => $count],
            ['Metric' => 'Average deal size', 'Value' => number_format($avg, 2)],
        ]);
    }
}
