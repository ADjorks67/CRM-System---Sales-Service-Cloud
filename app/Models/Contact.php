<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasRecordAccess;
use Database\Factories\ContactFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'account_id',
    'salutation',
    'first_name',
    'middle_name',
    'last_name',
    'title',
    'department',
    'phone',
    'mobile',
    'home_phone',
    'other_phone',
    'email',
    'fax',
    'reports_to_id',
    'assistant',
    'asst_phone',
    'mailing_street',
    'mailing_city',
    'mailing_state',
    'mailing_postal_code',
    'mailing_country',
    'other_street',
    'other_city',
    'other_state',
    'other_postal_code',
    'other_country',
    'lead_source',
    'birthdate',
    'description',
    'owner_id',
])]
class Contact extends Model
{
    /** @use HasFactory<ContactFactory> */
    use HasAuditFields, HasFactory, HasRecordAccess;

    protected function casts(): array
    {
        return [
            'birthdate' => 'date',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function reportsTo(): BelongsTo
    {
        return $this->belongsTo(self::class, 'reports_to_id');
    }

    public function directReports(): HasMany
    {
        return $this->hasMany(self::class, 'reports_to_id');
    }

    public function cases(): HasMany
    {
        return $this->hasMany(CrmCase::class, 'contact_id');
    }

    public function ownershipHistories(): MorphMany
    {
        return $this->morphMany(OwnershipHistory::class, 'ownable');
    }

    public function displayName(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->last_name,
        ])));
    }
}
