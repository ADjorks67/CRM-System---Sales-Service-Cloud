<?php

namespace App\Reports\Cases;

use App\Models\CrmCase;
use App\Models\User;
use App\Reports\PrebuiltReport;
use App\Support\PicklistOptions;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class MonthlyCaseVolumeByChannelReport extends PrebuiltReport
{
    public function key(): string
    {
        return 'monthly-case-volume-by-channel';
    }

    public function title(): string
    {
        return 'Monthly Case Volume by Channel';
    }

    public function category(): string
    {
        return 'Cases';
    }

    public function columns(): array
    {
        return ['Month', 'Origin', 'Count'];
    }

    public function rows(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $origins = PicklistOptions::options('case_origin');

        return CrmCase::query()
            ->visibleTo($user)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("to_char(created_at, 'YYYY-MM') as month, COALESCE(origin, 'web') as origin, COUNT(*) as total")
            ->groupBy('month', 'origin')
            ->orderBy('month')
            ->orderBy('origin')
            ->get()
            ->map(fn ($row) => [
                'Month' => $row->month,
                'Origin' => $origins[$row->origin] ?? $row->origin,
                'Count' => (int) $row->total,
            ]);
    }
}
