<?php

namespace App\Reports\Leads;

use App\Models\Lead;
use App\Models\User;
use App\Reports\PrebuiltReport;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class LeadsConvertedThisFyReport extends PrebuiltReport
{
    public function key(): string
    {
        return 'leads-converted-this-fy';
    }

    public function title(): string
    {
        return 'Leads Converted This FY';
    }

    public function category(): string
    {
        return 'Leads';
    }

    public function columns(): array
    {
        return ['Name', 'Company', 'Owner', 'Created'];
    }

    public function rows(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Lead::query()
            ->visibleTo($user)
            ->where('is_converted', true)
            ->where(function ($query) use ($from, $to): void {
                $query->whereBetween('converted_at', [$from, $to])
                    ->orWhere(function ($inner) use ($from, $to): void {
                        $inner->whereNull('converted_at')
                            ->whereBetween('updated_at', [$from, $to]);
                    });
            })
            ->with('owner')
            ->orderByDesc('converted_at')
            ->get()
            ->map(fn (Lead $lead) => [
                'Name' => $lead->displayName(),
                'Company' => $lead->company,
                'Owner' => $lead->owner?->name ?? '—',
                'Created' => $lead->converted_at?->toDateString() ?? $lead->updated_at?->toDateString() ?? '—',
                '_url' => route('leads.show', $lead),
            ]);
    }
}
