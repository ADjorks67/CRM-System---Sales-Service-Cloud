<?php

namespace App\Models;

use App\Enums\LeadStatus;
use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasRecordAccess;
use Database\Factories\LeadFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable([
    'salutation',
    'first_name',
    'last_name',
    'company',
    'title',
    'email',
    'phone',
    'mobile',
    'status',
    'lead_source',
    'rating',
    'industry',
    'annual_revenue',
    'number_of_employees',
    'website',
    'street',
    'city',
    'state',
    'postal_code',
    'country',
    'description',
    'is_converted',
    'converted_account_id',
    'converted_contact_id',
    'converted_opportunity_id',
    'converted_at',
    'owner_id',
])]
class Lead extends Model
{
    /** @use HasFactory<LeadFactory> */
    use HasAuditFields, HasFactory, HasRecordAccess;

    protected $attributes = [
        'status' => 'new',
        'is_converted' => false,
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'annual_revenue' => 'decimal:2',
            'number_of_employees' => 'integer',
            'is_converted' => 'boolean',
            'converted_at' => 'datetime',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function convertedAccount(): BelongsTo
    {
        return $this->belongsTo(Account::class, 'converted_account_id');
    }

    public function convertedContact(): BelongsTo
    {
        return $this->belongsTo(Contact::class, 'converted_contact_id');
    }

    public function convertedOpportunity(): BelongsTo
    {
        return $this->belongsTo(Opportunity::class, 'converted_opportunity_id');
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
        $name = trim(implode(' ', array_filter([
            $this->first_name,
            $this->last_name,
        ])));

        return $name !== '' ? $name : (string) $this->company;
    }

    public function isReadOnlyConverted(): bool
    {
        return $this->is_converted || $this->status === LeadStatus::Converted;
    }
}
