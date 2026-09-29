<?php

namespace App\Reports\Leads;

use App\Models\Lead;
use App\Models\User;
use App\Reports\PrebuiltReport;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class NewLeadsThisFyByOwnerReport extends PrebuiltReport
{
    public function key(): string
    {
        return 'new-leads-this-fy-by-owner';
    }

    public function title(): string
    {
        return 'New Leads This FY By Owner';
    }

    public function category(): string
    {
        return 'Leads';
    }

    public function columns(): array
    {
        return ['Owner', 'Count'];
    }

    public function rows(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Lead::query()
            ->visibleTo($user)
            ->whereBetween('created_at', [$from, $to])
            ->with('owner')
            ->get()
            ->groupBy('owner_id')
            ->map(fn (Collection $group) => [
                'Owner' => $group->first()?->owner?->name ?? '—',
                'Count' => $group->count(),
            ])
            ->sortByDesc('Count')
            ->values();
    }
}
