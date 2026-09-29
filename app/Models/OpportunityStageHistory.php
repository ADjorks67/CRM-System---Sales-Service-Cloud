<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'opportunity_id',
    'from_stage',
    'to_stage',
    'probability',
    'changed_by',
])]
class OpportunityStageHistory extends Model
{
    public const UPDATED_AT = null;

    protected function casts(): array
    {
        return [
            'probability' => 'integer',
            'created_at' => 'datetime',
        ];
    }

    public function opportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class);
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
