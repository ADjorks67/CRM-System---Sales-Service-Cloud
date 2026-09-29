<?php

namespace App\Models;

use App\Enums\RoleSlug;
use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasRecordAccess;
use Carbon\CarbonInterface;
use Database\Factories\EventFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'subject',
    'starts_at',
    'ends_at',
    'is_all_day',
    'location',
    'description',
    'show_as',
    'is_private',
    'calendar_type',
    'color',
    'owner_id',
    'related_type',
    'related_id',
    'name_contact_id',
])]
class Event extends Model
{
    /** @use HasFactory<EventFactory> */
    use HasAuditFields;

    use HasFactory;
    use HasRecordAccess {
        scopeVisibleTo as protected scopeRecordVisibleTo;
        isVisibleBy as protected recordIsVisibleBy;
        isWritableBy as protected recordIsWritableBy;
    }

    protected $attributes = [
        'is_all_day' => false,
        'show_as' => 'busy',
        'is_private' => false,
        'calendar_type' => 'my_events',
    ];

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_all_day' => 'boolean',
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

    public function nameContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'name_contact_id');
    }

    public function displayName(): string
    {
        return (string) $this->subject;
    }

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        $this->scopeRecordVisibleTo($query, $user);

        if (! $user->hasRole(RoleSlug::SystemAdministrator)) {
            $query->where(function (Builder $inner) use ($user): void {
                $inner->where('is_private', false)
                    ->orWhere($this->qualifyColumn('owner_id'), $user->id);
            });
        }

        return $query;
    }

    public function isVisibleBy(User $user): bool
    {
        if ($this->is_private && ! $user->hasRole(RoleSlug::SystemAdministrator) && (int) $this->owner_id !== (int) $user->id) {
            return false;
        }

        return $this->recordIsVisibleBy($user);
    }

    public function isWritableBy(User $user): bool
    {
        if ($this->is_private && ! $user->hasRole(RoleSlug::SystemAdministrator) && (int) $this->owner_id !== (int) $user->id) {
            return false;
        }

        return $this->recordIsWritableBy($user);
    }

    /**
     * Events owned by the user (My Events calendar — FR-CAL-004).
     *
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where($this->qualifyColumn('owner_id'), $user->id);
    }

    /**
     * Non-private events the user can see (Public / team calendar — FR-CAL-004).
     *
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopePublicTeam(Builder $query): Builder
    {
        return $query->where('is_private', false);
    }

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeOccurringOn(Builder $query, CarbonInterface $date): Builder
    {
        $start = $date->copy()->startOfDay();
        $end = $date->copy()->endOfDay();

        return $query
            ->where('starts_at', '<=', $end)
            ->where('ends_at', '>=', $start);
    }

    /**
     * @param  Builder<Event>  $query
     * @return Builder<Event>
     */
    public function scopeBetween(Builder $query, CarbonInterface $from, CarbonInterface $to): Builder
    {
        return $query
            ->where('starts_at', '<=', $to)
            ->where('ends_at', '>=', $from);
    }

    /**
     * @return array{id: int, title: string, start: string, end: string, allDay: bool, url: string, backgroundColor: string, borderColor: string, extendedProps: array<string, mixed>}
     */
    public function toFullCalendar(): array
    {
        $color = $this->color ?: ($this->is_private ? '#5c6b7a' : '#0176d3');

        return [
            'id' => $this->id,
            'title' => $this->subject,
            'start' => $this->starts_at->toIso8601String(),
            'end' => $this->ends_at->toIso8601String(),
            'allDay' => $this->is_all_day,
            'url' => route('events.show', $this),
            'backgroundColor' => $color,
            'borderColor' => $color,
            'extendedProps' => [
                'location' => $this->location,
                'show_as' => $this->show_as,
                'is_private' => $this->is_private,
                'calendar_type' => $this->calendar_type,
                'owner_id' => $this->owner_id,
            ],
        ];
    }
}
