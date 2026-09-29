<?php

namespace App\Reports\Opportunities;

use App\Models\Opportunity;
use App\Models\User;
use App\Reports\PrebuiltReport;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ClosedWonByOwnerReport extends PrebuiltReport
{
    public function key(): string
    {
        return 'closed-won-by-owner';
    }

    public function title(): string
    {
        return 'Closed Won Opportunities by Owner';
    }

    public function category(): string
    {
        return 'Opportunities';
    }

    public function columns(): array
    {
        return ['Owner', 'Deals', 'Total Amount'];
    }

    public function rows(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Opportunity::query()
            ->visibleTo($user)
            ->notArchived()
            ->where('is_won', true)
            ->whereBetween('close_date', [$from->toDateString(), $to->toDateString()])
            ->with('owner')
            ->get()
            ->groupBy('owner_id')
            ->map(fn (Collection $group) => [
                'Owner' => $group->first()?->owner?->name ?? '—',
                'Deals' => $group->count(),
                'Total Amount' => number_format((float) $group->sum('amount'), 2),
            ])
            ->sortByDesc('Deals')
            ->values();
    }
}
