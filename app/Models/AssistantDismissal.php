<?php

namespace App\Models;

use Database\Factories\AssistantDismissalFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'user_id',
    'recommendation_key',
    'dismissed_at',
])]
class AssistantDismissal extends Model
{
    /** @use HasFactory<AssistantDismissalFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'dismissed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
