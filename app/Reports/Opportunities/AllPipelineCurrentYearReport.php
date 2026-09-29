<?php

namespace App\Reports\Opportunities;

use App\Models\Opportunity;
use App\Models\User;
use App\Reports\PrebuiltReport;
use App\Support\PicklistOptions;
use Carbon\CarbonInterface;
use Illuminate\Support\Collection;

class AllPipelineCurrentYearReport extends PrebuiltReport
{
    public function key(): string
    {
        return 'all-pipeline-current-year';
    }

    public function title(): string
    {
        return 'All Pipeline – Current Year';
    }

    public function category(): string
    {
        return 'Opportunities';
    }

    public function columns(): array
    {
        return ['Opportunity', 'Account', 'Amount', 'Stage', 'Close Date', 'Owner'];
    }

    public function rows(User $user, CarbonInterface $from, CarbonInterface $to): Collection
    {
        $stages = PicklistOptions::options('opportunity_stage');

        return Opportunity::query()
            ->visibleTo($user)
            ->notArchived()
            ->where('is_closed', false)
            ->whereBetween('close_date', [$from->toDateString(), $to->toDateString()])
            ->with(['account', 'owner'])
            ->orderByDesc('amount')
            ->get()
            ->map(fn (Opportunity $opp) => [
                'Opportunity' => $opp->name,
                'Account' => $opp->account?->name ?? '—',
                'Amount' => $opp->amount !== null ? number_format((float) $opp->amount, 2) : '—',
                'Stage' => $stages[$opp->stage] ?? $opp->stage,
                'Close Date' => $opp->close_date?->toDateString() ?? '—',
                'Owner' => $opp->owner?->name ?? '—',
                '_url' => route('opportunities.show', $opp),
            ]);
    }
}
