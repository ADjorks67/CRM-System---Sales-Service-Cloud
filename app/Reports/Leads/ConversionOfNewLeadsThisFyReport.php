<?php

namespace App\Reports\Leads;

use App\Models\Lead;
use App\Models\User;
use App\Reports\PrebuiltReport;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ConversionOfNewLeadsThisFyReport extends PrebuiltReport
{
    public function key(): string
    {
        return 'conversion-of-new-leads-this-fy';
    }

    public function title(): string
    {
        return 'Conversion of New Leads This FY';
    }

    public function category(): string
    {
        return 'Leads';
    }

    public function description(): string
    {
        return 'Converted counts stay low until lead conversion ships in Phase 4.';
    }

    public function columns(): array
    {
        return ['Metric', 'Value'];
    }

    public function rows(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $base = Lead::query()
            ->visibleTo($user)
            ->whereBetween('created_at', [$from, $to]);

        $total = (clone $base)->count();
        $converted = (clone $base)->where('is_converted', true)->count();
        $rate = $total > 0 ? round(($converted / $total) * 100, 1) : 0;

        return collect([
            ['Metric' => 'New leads', 'Value' => $total],
            ['Metric' => 'Converted', 'Value' => $converted],
            ['Metric' => 'Conversion rate %', 'Value' => $rate],
        ]);
    }
}
