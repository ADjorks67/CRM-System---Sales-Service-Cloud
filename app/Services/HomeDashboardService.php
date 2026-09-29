<?php

namespace App\Services;

use App\Models\Opportunity;
use App\Models\User;
use App\Support\PicklistOptions;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;

class HomeDashboardService
{
    public function __construct(private readonly RecentRecordService $recentRecords) {}

    /**
     * @return array{
     *     recent: Collection,
     *     funnel: array{labels: list<string>, values: list<float>, total: float},
     *     revenueBySource: array{labels: list<string>, values: list<float>, total: float},
     *     keyDeals: Collection<int, Opportunity>,
     *     period: string,
     *     from: CarbonInterface,
     *     to: CarbonInterface
     * }
     */
    public function build(User $user, string $period = 'current_year'): array
    {
        [$from, $to] = $this->periodBounds($period);

        $base = Opportunity::query()
            ->visibleTo($user)
            ->notArchived()
            ->whereBetween('close_date', [$from->toDateString(), $to->toDateString()]);

        return [
            'recent' => $this->recentRecords->recentFor($user, 5),
            'funnel' => $this->pipelineFunnel(clone $base),
            'revenueBySource' => $this->revenueBySource(clone $base),
            'keyDeals' => $this->keyDeals($user),
            'period' => $period,
            'from' => $from,
            'to' => $to,
        ];
    }

    /**
     * @param  Builder<Opportunity>  $query
     * @return array{labels: list<string>, values: list<float>, total: float}
     */
    private function pipelineFunnel(Builder $query): array
    {
        $stages = PicklistOptions::options('opportunity_stage');
        $labels = [];
        $values = [];
        $total = 0.0;

        $open = (clone $query)->where('is_closed', false);

        foreach ($stages as $value => $label) {
            if (in_array($value, ['closed_won', 'closed_lost'], true)) {
                continue;
            }

            $amount = (float) (clone $open)->where('stage', $value)->sum('amount');
            $labels[] = $label;
            $values[] = $amount;
            $total += $amount;
        }

        return compact('labels', 'values', 'total');
    }

    /**
     * @param  Builder<Opportunity>  $query
     * @return array{labels: list<string>, values: list<float>, total: float}
     */
    private function revenueBySource(Builder $query): array
    {
        $rows = (clone $query)
            ->where('is_closed', false)
            ->selectRaw("COALESCE(lead_source, 'other') as source, COALESCE(SUM(amount * probability / 100.0), 0) as revenue")
            ->groupBy('source')
            ->orderByDesc('revenue')
            ->get();

        $sourceLabels = PicklistOptions::options('lead_source');
        $labels = [];
        $values = [];
        $total = 0.0;

        foreach ($rows as $row) {
            $labels[] = $sourceLabels[$row->source] ?? ucfirst(str_replace('_', ' ', (string) $row->source));
            $values[] = (float) $row->revenue;
            $total += (float) $row->revenue;
        }

        return compact('labels', 'values', 'total');
    }

    /**
     * @return Collection<int, Opportunity>
     */
    private function keyDeals(User $user): Collection
    {
        return Opportunity::query()
            ->visibleTo($user)
            ->notArchived()
            ->where('is_closed', false)
            ->where(function (Builder $inner): void {
                $inner->where('probability', '>=', 80)
                    ->orWhere('amount', '>=', 50000);
            })
            ->with('account')
            ->orderByDesc('amount')
            ->limit(8)
            ->get();
    }

    /**
     * @return array{0: CarbonInterface, 1: CarbonInterface}
     */
    private function periodBounds(string $period): array
    {
        $now = now();

        return match ($period) {
            'last_year' => [$now->copy()->subYear()->startOfYear(), $now->copy()->subYear()->endOfYear()],
            default => [$now->copy()->startOfYear(), $now->copy()->endOfYear()],
        };
    }
}
