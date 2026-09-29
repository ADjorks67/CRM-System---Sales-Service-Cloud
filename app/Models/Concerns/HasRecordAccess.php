<?php

namespace App\Models\Concerns;

use App\Enums\RoleSlug;
use App\Enums\SharingAccessLevel;
use App\Models\RecordShare;
use App\Models\SharingDefault;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * Record-level visibility (FR-AUTH-003). Requires owner_id on the model table.
 *
 * @mixin Model
 */
trait HasRecordAccess
{
    public function shares(): MorphMany
    {
        return $this->morphMany(RecordShare::class, 'shareable');
    }

    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole(RoleSlug::SystemAdministrator)) {
            return $query;
        }

        $objectType = $this->getMorphClass();
        $default = SharingDefault::accessLevelFor($objectType);

        if ($default->allowsReadForEveryone()) {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($user): void {
            $inner->where($this->qualifyColumn('owner_id'), $user->id)
                ->orWhereHas('shares', function (Builder $shares) use ($user): void {
                    $shares->where('user_id', $user->id);
                });
        });
    }

    public function isVisibleBy(User $user): bool
    {
        if ($user->hasRole(RoleSlug::SystemAdministrator)) {
            return true;
        }

        $default = SharingDefault::accessLevelFor($this->getMorphClass());

        if ($default->allowsReadForEveryone()) {
            return true;
        }

        if ((int) $this->getAttribute('owner_id') === (int) $user->id) {
            return true;
        }

        return $this->shares()
            ->where('user_id', $user->id)
            ->exists();
    }

    public function isWritableBy(User $user): bool
    {
        if ($user->hasRole(RoleSlug::SystemAdministrator)) {
            return true;
        }

        $default = SharingDefault::accessLevelFor($this->getMorphClass());

        if ($default->allowsWriteForEveryone()) {
            return true;
        }

        if ((int) $this->getAttribute('owner_id') === (int) $user->id) {
            return true;
        }

        return $this->shares()
            ->where('user_id', $user->id)
            ->where('access_level', SharingAccessLevel::PublicReadWrite->value)
            ->exists();
    }
}
