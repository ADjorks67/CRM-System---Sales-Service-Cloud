<?php

namespace App\Reports\Leads;

use App\Models\Lead;
use App\Models\User;
use App\Reports\PrebuiltReport;
use App\Support\PicklistOptions;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class LeadsBySourceThisFyReport extends PrebuiltReport
{
    public function key(): string
    {
        return 'leads-by-source-this-fy';
    }

    public function title(): string
    {
        return 'Leads by Source This FY';
    }

    public function category(): string
    {
        return 'Leads';
    }

    public function columns(): array
    {
        return ['Lead Source', 'Count'];
    }

    public function rows(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $labels = PicklistOptions::options('lead_source');

        return Lead::query()
            ->visibleTo($user)
            ->whereBetween('created_at', [$from, $to])
            ->selectRaw("COALESCE(lead_source, 'other') as source, COUNT(*) as total")
            ->groupBy('source')
            ->orderByDesc('total')
            ->get()
            ->map(fn ($row) => [
                'Lead Source' => $labels[$row->source] ?? $row->source,
                'Count' => (int) $row->total,
                '_chart_label' => $labels[$row->source] ?? $row->source,
                '_chart_value' => (int) $row->total,
            ]);
    }

    public function chart(User $user, CarbonInterface $from, CarbonInterface $to): ?array
    {
        $rows = $this->rows($user, $from, $to);

        return [
            'type' => 'donut',
            'labels' => $rows->pluck('_chart_label')->all(),
            'values' => $rows->pluck('_chart_value')->all(),
            'label' => 'Leads',
        ];
    }
}
