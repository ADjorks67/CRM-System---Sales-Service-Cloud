<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasOwnerPrivacy;
use Database\Factories\SavedReportFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'description',
    'folder',
    'report_type',
    'definition',
    'is_private',
    'owner_id',
])]
class SavedReport extends Model
{
    /** @use HasFactory<SavedReportFactory> */
    use HasAuditFields, HasFactory, HasOwnerPrivacy;

    protected $attributes = [
        'folder' => 'private',
        'is_private' => true,
    ];

    protected function casts(): array
    {
        return [
            'definition' => 'array',
            'is_private' => 'boolean',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(DashboardWidget::class);
    }
}
