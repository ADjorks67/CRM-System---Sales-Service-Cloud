<?php

namespace App\Reports\Leads;

use App\Models\Lead;
use App\Models\User;
use App\Reports\PrebuiltReport;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class LeadsCreatedByMonthReport extends PrebuiltReport
{
    public function key(): string
    {
        return 'leads-created-by-month';
    }

    public function title(): string
    {
        return 'Leads Created by Month';
    }

    public function category(): string
    {
        return 'Leads';
    }

    public function columns(): array
    {
        return ['Month', 'Count'];
    }

    public function rows(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Lead::query()
            ->visibleTo($user)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("to_char(created_at, 'YYYY-MM') as month, COUNT(*) as total")
            ->groupBy('month')
            ->orderBy('month')
            ->get()
            ->map(fn ($row) => [
                'Month' => $row->month,
                'Count' => (int) $row->total,
                '_chart_label' => $row->month,
                '_chart_value' => (int) $row->total,
            ]);
    }

    public function chart(User $user, CarbonInterface $from, CarbonInterface $to): ?array
    {
        $rows = $this->rows($user, $from, $to);

        return [
            'type' => 'bar',
            'labels' => $rows->pluck('_chart_label')->all(),
            'values' => $rows->pluck('_chart_value')->all(),
            'label' => 'Leads',
        ];
    }
}
