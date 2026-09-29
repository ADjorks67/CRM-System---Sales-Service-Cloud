<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use Database\Factories\SavedSearchFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'name',
    'owner_id',
    'object_type',
    'definition',
])]
class SavedSearch extends Model
{
    /** @use HasFactory<SavedSearchFactory> */
    use HasAuditFields, HasFactory;

    protected function casts(): array
    {
        return [
            'definition' => 'array',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function isOwnedBy(User $user): bool
    {
        return (int) $this->owner_id === (int) $user->id;
    }
}
