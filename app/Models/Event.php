<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasRecordAccess;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

/**
 * Calendar Event record. CRUD/calendar UI is Dev B (FR-CAL-*).
 * This model exists so Dev A can transfer activities and expose EventQueryService.
 */
#[Fillable([
    'subject',
    'starts_at',
    'ends_at',
    'all_day',
    'location',
    'description',
    'is_private',
    'related_type',
    'related_id',
    'owner_id',
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasAuditFields, HasFactory, HasRecordAccess;

    protected $attributes = [
        'all_day' => false,
        'is_private' => false,
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'all_day' => 'boolean',
            'is_private' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function displayName(): string
    {
        return (string) $this->subject;
    }

    /**
     * Open = not ended yet (for transfer_open_activities).
     *
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('ends_at', '>=', now());
    }

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeStartingToday(Builder $query): Builder
    {
        return $query->whereDate('starts_at', now()->toDateString());
    }
}
