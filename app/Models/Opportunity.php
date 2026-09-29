<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasRecordAccess;
use Database\Factories\OpportunityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'name',
    'account_id',
    'amount',
    'close_date',
    'stage',
    'probability',
    'type',
    'lead_source',
    'next_step',
    'description',
    'expected_revenue',
    'is_closed',
    'is_won',
    'archived_at',
    'owner_id',
])]
class Opportunity extends Model
{
    /** @use HasFactory<OpportunityFactory> */
    use HasAuditFields, HasFactory, HasRecordAccess;

    protected $attributes = [
        'stage' => 'qualification',
        'probability' => 10,
        'is_closed' => false,
        'is_won' => false,
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'expected_revenue' => 'decimal:2',
            'close_date' => 'date',
            'probability' => 'integer',
            'is_closed' => 'boolean',
            'is_won' => 'boolean',
            'archived_at' => 'datetime',
        ];
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function stageHistories(): HasMany
    {
        return $this->hasMany(OpportunityStageHistory::class)->orderByDesc('created_at');
    }

    public function ownershipHistories(): MorphMany
    {
        return $this->morphMany(OwnershipHistory::class, 'ownable');
    }

    public function attachments(): MorphMany
    {
        return $this->morphMany(Attachment::class, 'attachable')->latest();
    }

    public function displayName(): string
    {
        return (string) $this->name;
    }

    public function isArchived(): bool
    {
        return $this->archived_at !== null;
    }

    /**
     * @param  Builder<Opportunity>  $query
     * @return Builder<Opportunity>
     */
    public function scopeNotArchived(Builder $query): Builder
    {
        return $query->whereNull('archived_at');
    }

    public function computeExpectedRevenue(): ?string
    {
        if ($this->amount === null) {
            return null;
        }

        $amount = (float) $this->amount;
        $probability = (int) $this->probability;

        return number_format($amount * $probability / 100, 2, '.', '');
    }

    public function recomputeExpectedRevenue(): void
    {
        $this->expected_revenue = $this->computeExpectedRevenue();
    }
}
