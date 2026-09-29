<?php

namespace App\Services;

use App\Models\Account;
use App\Models\AssistantDismissal;
use App\Models\Event;
use App\Models\Opportunity;
use App\Models\Task;
use App\Models\User;
use Illuminate\Support\Collection;

class AssistantRecommendationService
{
    private const INACTIVE_DAYS = 30;

    private const CLOSE_SOON_DAYS = 7;

    private const STALE_UPDATE_DAYS = 14;

    private const LIMIT = 12;

    /**
     * @return Collection<int, array{
     *     key: string,
     *     type: string,
     *     title: string,
     *     reason: string,
     *     url: string,
     *     action_label: string
     * }>
     */
    public function recommendationsFor(User $user): Collection
    {
        $dismissed = AssistantDismissal::query()
            ->where('user_id', $user->id)
            ->pluck('recommendation_key')
            ->all();

        return $this->inactiveAccounts($user)
            ->concat($this->staleClosingOpportunities($user))
            ->reject(fn (array $item) => in_array($item['key'], $dismissed, true))
            ->take(self::LIMIT)
            ->values();
    }

    public function dismiss(User $user, string $recommendationKey): void
    {
        AssistantDismissal::query()->updateOrCreate(
            [
                'user_id' => $user->id,
                'recommendation_key' => $recommendationKey,
            ],
            [
                'dismissed_at' => now(),
            ],
        );
    }

    /**
     * @return Collection<int, array{key: string, type: string, title: string, reason: string, url: string, action_label: string}>
     */
    private function inactiveAccounts(User $user): Collection
    {
        $cutoff = now()->subDays(self::INACTIVE_DAYS);

        $recentTaskAccountIds = Task::query()
            ->where('related_type', 'account')
            ->where(function ($query) use ($cutoff): void {
                $query->where('created_at', '>=', $cutoff)
                    ->orWhere('updated_at', '>=', $cutoff);
            })
            ->pluck('related_id');

        $recentEventAccountIds = Event::query()
            ->where('related_type', 'account')
            ->where(function ($query) use ($cutoff): void {
                $query->where('created_at', '>=', $cutoff)
                    ->orWhere('updated_at', '>=', $cutoff)
                    ->orWhere('starts_at', '>=', $cutoff);
            })
            ->pluck('related_id');

        $activeAccountIds = $recentTaskAccountIds->merge($recentEventAccountIds)->unique()->filter()->all();

        return Account::query()
            ->visibleTo($user)
            ->when($activeAccountIds !== [], fn ($q) => $q->whereNotIn('id', $activeAccountIds))
            ->orderBy('name')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Account $account) => [
                'key' => 'inactive_account:'.$account->id,
                'type' => 'inactive_account',
                'title' => $account->name,
                'reason' => 'No Task or Event activity in '.self::INACTIVE_DAYS.'+ days.',
                'url' => route('accounts.show', $account),
                'action_label' => 'View account',
            ]);
    }

    /**
     * @return Collection<int, array{key: string, type: string, title: string, reason: string, url: string, action_label: string}>
     */
    private function staleClosingOpportunities(User $user): Collection
    {
        $today = now()->startOfDay();
        $closeBy = now()->copy()->addDays(self::CLOSE_SOON_DAYS)->endOfDay();
        $staleBefore = now()->subDays(self::STALE_UPDATE_DAYS);

        return Opportunity::query()
            ->visibleTo($user)
            ->notArchived()
            ->where('is_closed', false)
            ->whereBetween('close_date', [$today->toDateString(), $closeBy->toDateString()])
            ->where('updated_at', '<', $staleBefore)
            ->with('account')
            ->orderBy('close_date')
            ->limit(self::LIMIT)
            ->get()
            ->map(fn (Opportunity $opportunity) => [
                'key' => 'stale_opportunity:'.$opportunity->id,
                'type' => 'stale_opportunity',
                'title' => $opportunity->name,
                'reason' => 'Closes '.$opportunity->close_date?->toDateString().' and has not been updated in '.self::STALE_UPDATE_DAYS.'+ days.',
                'url' => route('opportunities.show', $opportunity),
                'action_label' => 'Update opportunity',
            ]);
    }
}
