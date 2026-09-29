<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'ownable_type',
    'ownable_id',
    'previous_owner_id',
    'new_owner_id',
    'changed_by',
    'notify_new_owner',
    'transfer_open_activities',
    'notes',
])]
class OwnershipHistory extends Model
{
    protected function casts(): array
    {
        return [
            'notify_new_owner' => 'boolean',
            'transfer_open_activities' => 'boolean',
        ];
    }

    public function ownable(): MorphTo
    {
        return $this->morphTo();
    }

    public function previousOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'previous_owner_id');
    }

    public function newOwner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'new_owner_id');
    }

    public function changedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'changed_by');
    }
}
