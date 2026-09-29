<?php

namespace App\Services;

use App\Models\Opportunity;
use App\Models\OpportunityStageHistory;
use App\Models\User;
use App\Support\PicklistOptions;
use Illuminate\Support\Facades\DB;

class StageService
{
    public function changeStage(Opportunity $opportunity, string $stage, User $actor): Opportunity
    {
        return DB::transaction(function () use ($opportunity, $stage, $actor): Opportunity {
            $fromStage = $opportunity->stageHistories()->exists()
                ? $opportunity->getAttribute('stage')
                : null;
            $probability = PicklistOptions::probabilityForStage($stage);
            [$isClosed, $isWon] = $this->closedFlagsForStage($stage);

            $opportunity->forceFill([
                'stage' => $stage,
                'probability' => $probability,
                'is_closed' => $isClosed,
                'is_won' => $isWon,
            ]);
            $opportunity->recomputeExpectedRevenue();
            $opportunity->save();

            OpportunityStageHistory::query()->create([
                'opportunity_id' => $opportunity->id,
                'from_stage' => $fromStage,
                'to_stage' => $stage,
                'probability' => $probability,
                'changed_by' => $actor->id,
            ]);

            return $opportunity->fresh();
        });
    }

    /**
     * @return array{0: bool, 1: bool}
     */
    private function closedFlagsForStage(string $stage): array
    {
        return match ($stage) {
            'closed_won' => [true, true],
            'closed_lost' => [true, false],
            default => [false, false],
        };
    }
}
