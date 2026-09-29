<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasRecordAccess;
use Database\Factories\AccountFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'name',
    'parent_account_id',
    'phone',
    'fax',
    'website',
    'type',
    'industry',
    'employees',
    'annual_revenue',
    'billing_street',
    'billing_city',
    'billing_state',
    'billing_postal_code',
    'billing_country',
    'shipping_street',
    'shipping_city',
    'shipping_state',
    'shipping_postal_code',
    'shipping_country',
    'description',
    'owner_id',
])]
class Account extends Model
{
    /** @use HasFactory<AccountFactory> */
    use HasAuditFields, HasFactory, HasRecordAccess;

    protected function casts(): array
    {
        return [
            'employees' => 'integer',
            'annual_revenue' => 'decimal:2',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function parentAccount(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_account_id');
    }

    public function childAccounts(): HasMany
    {
        return $this->hasMany(self::class, 'parent_account_id');
    }

    public function contacts(): HasMany
    {
        return $this->hasMany(Contact::class);
    }

    public function ownershipHistories(): MorphMany
    {
        return $this->morphMany(OwnershipHistory::class, 'ownable');
    }

    public function displayName(): string
    {
        return (string) $this->name;
    }
}
