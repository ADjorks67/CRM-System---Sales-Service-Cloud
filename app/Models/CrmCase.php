<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasRecordAccess;
use Database\Factories\CrmCaseFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'case_number',
    'subject',
    'status',
    'priority',
    'origin',
    'type',
    'reason',
    'description',
    'internal_comments',
    'contact_id',
    'account_id',
    'web_email',
    'web_company',
    'web_name',
    'web_phone',
    'owner_id',
    'closed_at',
])]
class CrmCase extends Model
{
    /** @use HasFactory<CrmCaseFactory> */
    use HasAuditFields, HasFactory, HasRecordAccess;

    protected $table = 'cases';

    protected $attributes = [
        'status' => 'new',
        'priority' => 'medium',
        'origin' => 'web',
    ];

    protected function casts(): array
    {
        return [
            'closed_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CrmCase $crmCase): void {
            if (filled($crmCase->case_number)) {
                return;
            }

            /** @var object{seq: int|string} $row */
            $row = DB::selectOne("SELECT nextval('case_number_seq') AS seq");
            $crmCase->case_number = sprintf(
                'CASE-%s-%05d',
                now()->format('Y'),
                (int) $row->seq,
            );
        });
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function account(): BelongsTo
    {
        return $this->belongsTo(Account::class);
    }

    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    public function ownershipHistories(): MorphMany
    {
        return $this->morphMany(OwnershipHistory::class, 'ownable');
    }

    public function displayName(): string
    {
        $subject = trim((string) $this->subject);

        return $subject !== '' ? $subject : (string) $this->case_number;
    }

    public function isClosed(): bool
    {
        return $this->status === 'closed';
    }

    public function isReadOnlyClosed(): bool
    {
        return $this->isClosed();
    }
}
