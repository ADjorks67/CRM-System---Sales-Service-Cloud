<?php

namespace App\Reports\Opportunities;

use App\Models\Opportunity;
use App\Models\User;
use App\Reports\PrebuiltReport;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class ClosedWonThisFyReport extends PrebuiltReport
{
    public function key(): string
    {
        return 'closed-won-this-fy';
    }

    public function title(): string
    {
        return 'Closed (Won) Opportunities This FY';
    }

    public function category(): string
    {
        return 'Opportunities';
    }

    public function columns(): array
    {
        return ['Opportunity', 'Account', 'Amount', 'Close Date', 'Owner'];
    }

    public function rows(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        return Opportunity::query()
            ->visibleTo($user)
            ->notArchived()
            ->where('is_won', true)
            ->whereBetween('close_date', [$from->toDateString(), $to->toDateString()])
            ->with(['account', 'owner'])
            ->orderByDesc('close_date')
            ->get()
            ->map(fn (Opportunity $opp) => [
                'Opportunity' => $opp->name,
                'Account' => $opp->account?->name ?? '—',
                'Amount' => $opp->amount !== null ? number_format((float) $opp->amount, 2) : '—',
                'Close Date' => $opp->close_date?->toDateString() ?? '—',
                'Owner' => $opp->owner?->name ?? '—',
                '_url' => route('opportunities.show', $opp),
            ]);
    }
}
