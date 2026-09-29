<?php

namespace App\Reports\Cases;

use App\Models\CrmCase;
use App\Models\User;
use App\Reports\PrebuiltReport;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AverageCaseAgeReport extends PrebuiltReport
{
    public function key(): string
    {
        return 'average-case-age';
    }

    public function title(): string
    {
        return 'Average Case Age';
    }

    public function category(): string
    {
        return 'Cases';
    }

    public function columns(): array
    {
        return ['Metric', 'Value'];
    }

    public function rows(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $openAvg = CrmCase::query()
            ->visibleTo($user)
            ->where('status', '!=', 'closed')
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (NOW() - created_at)) / 86400.0) as avg_days')
            ->value('avg_days');

        $closedAvg = CrmCase::query()
            ->visibleTo($user)
            ->where('status', 'closed')
            ->whereBetween('closed_at', [$from, $to])
            ->selectRaw('AVG(EXTRACT(EPOCH FROM (closed_at - created_at)) / 86400.0) as avg_days')
            ->value('avg_days');

        return collect([
            ['Metric' => 'Avg age open cases (days)', 'Value' => $openAvg !== null ? round((float) $openAvg, 1) : '—'],
            ['Metric' => 'Avg age closed this FY (days)', 'Value' => $closedAvg !== null ? round((float) $closedAvg, 1) : '—'],
        ]);
    }
}
