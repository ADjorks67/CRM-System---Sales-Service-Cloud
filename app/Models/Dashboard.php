<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use App\Models\Concerns\HasOwnerPrivacy;
use Database\Factories\DashboardFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'description',
    'folder',
    'is_private',
    'layout',
    'refresh_interval_minutes',
    'owner_id',
])]
class Dashboard extends Model
{
    /** @use HasFactory<DashboardFactory> */
    use HasAuditFields, HasFactory, HasOwnerPrivacy;

    public const REFRESH_INTERVALS = [5, 10, 30, 60];

    protected $attributes = [
        'folder' => 'private',
        'is_private' => true,
    ];

    protected function casts(): array
    {
        return [
            'layout' => 'array',
            'is_private' => 'boolean',
            'refresh_interval_minutes' => 'integer',
        ];
    }

    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function widgets(): HasMany
    {
        return $this->hasMany(DashboardWidget::class)->orderBy('grid_y')->orderBy('grid_x');
    }
}
