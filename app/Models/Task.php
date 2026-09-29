<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasRecordAccess;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'subject',
    'status',
    'priority',
    'due_date',
    'comments',
    'reminder_set',
    'reminder_at',
    'completed_at',
    'reminder_sent_at',
    'related_type',
    'related_id',
    'contact_id',
    'owner_id',
])]
class Task extends Model
{
    /** @use HasFactory<TaskFactory> */
    use HasAuditFields, HasFactory, HasRecordAccess;

    protected $attributes = [
        'status' => 'not_started',
        'priority' => 'normal',
        'reminder_set' => false,
    ];

    protected function casts(): array
    {
        return [
            'due_date' => 'date',
            'reminder_set' => 'boolean',
            'reminder_at' => 'datetime',
            'completed_at' => 'datetime',
            'reminder_sent_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function related(): MorphTo
    {
        return $this->morphTo();
    }

    public function displayName(): string
    {
        return (string) $this->subject;
    }

    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    public function isOpen(): bool
    {
        return ! $this->isCompleted();
    }

    public function isOverdue(): bool
    {
        return $this->isOpen()
            && $this->due_date !== null
            && $this->due_date->isBefore(now()->startOfDay());
    }

    /**
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function scopeOpen(Builder $query): Builder
    {
        return $query->where('status', '!=', 'completed');
    }

    /**
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function scopeCompleted(Builder $query): Builder
    {
        return $query->where('status', 'completed');
    }

    /**
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function scopeDueToday(Builder $query): Builder
    {
        return $query->open()->whereDate('due_date', now()->toDateString());
    }

    /**
     * @param  Builder<Task>  $query
     * @return Builder<Task>
     */
    public function scopeOverdue(Builder $query): Builder
    {
        return $query->open()->whereDate('due_date', '<', now()->toDateString());
    }
}
