<?php

namespace App\Reports\Opportunities;

use App\Models\Opportunity;
use App\Models\User;
use App\Reports\PrebuiltReport;
use App\Support\PicklistOptions;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class PotentialRevenueSourceCurrentYearReport extends PrebuiltReport
{
    public function key(): string
    {
        return 'potential-revenue-source-current-year';
    }

    public function title(): string
    {
        return 'Potential Revenue Source – Current Year';
    }

    public function category(): string
    {
        return 'Opportunities';
    }

    public function columns(): array
    {
        return ['Lead Source', 'Potential Revenue'];
    }

    public function rows(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $labels = PicklistOptions::options('lead_source');

        return Opportunity::query()
            ->visibleTo($user)
            ->notArchived()
            ->where('is_closed', false)
            ->whereBetween('close_date', [$from->toDateString(), $to->toDateString()])
            ->selectRaw("COALESCE(lead_source, 'other') as source, COALESCE(SUM(amount * probability / 100.0), 0) as revenue")
            ->groupBy('source')
            ->orderByDesc('revenue')
            ->get()
            ->map(fn ($row) => [
                'Lead Source' => $labels[$row->source] ?? $row->source,
                'Potential Revenue' => number_format((float) $row->revenue, 2),
                '_chart_label' => $labels[$row->source] ?? $row->source,
                '_chart_value' => (float) $row->revenue,
            ]);
    }

    public function chart(User $user, CarbonInterface $from, CarbonInterface $to): ?array
    {
        $rows = $this->rows($user, $from, $to);

        return [
            'type' => 'donut',
            'labels' => $rows->pluck('_chart_label')->all(),
            'values' => $rows->pluck('_chart_value')->all(),
            'label' => 'Revenue',
        ];
    }
}
