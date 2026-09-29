<?php

namespace App\Models\Concerns;

use App\Enums\RoleSlug;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * Owner / private visibility for SavedReport and Dashboard (not CRM sharing OWD).
 *
 * @mixin Model
 */
trait HasOwnerPrivacy
{
    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    public function scopeVisibleTo(Builder $query, User $user): Builder
    {
        if ($user->hasRole(RoleSlug::SystemAdministrator)) {
            return $query;
        }

        return $query->where(function (Builder $inner) use ($user): void {
            $inner->where($this->qualifyColumn('owner_id'), $user->id)
                ->orWhere('is_private', false);
        });
    }

    public function isVisibleBy(User $user): bool
    {
        if ($user->hasRole(RoleSlug::SystemAdministrator)) {
            return true;
        }

        return (int) $this->getAttribute('owner_id') === (int) $user->id
            || ! (bool) $this->getAttribute('is_private');
    }

    public function isWritableBy(User $user): bool
    {
        if ($user->hasRole(RoleSlug::SystemAdministrator)) {
            return true;
        }

        return (int) $this->getAttribute('owner_id') === (int) $user->id;
    }
}
