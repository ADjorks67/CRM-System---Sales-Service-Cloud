<?php

namespace App\Models;

use App\Enums\SharingAccessLevel;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['shareable_type', 'shareable_id', 'user_id', 'access_level'])]
class RecordShare extends Model
{
    protected function casts(): array
    {
        return [
            'access_level' => SharingAccessLevel::class,
        ];
    }

    public function shareable(): MorphTo
    {
        return $this->morphTo();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
