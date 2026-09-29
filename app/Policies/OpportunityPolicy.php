<?php

namespace App\Policies;

use App\Models\Opportunity;
use App\Models\User;

class OpportunityPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('opportunities.view');
    }

    public function view(User $user, Opportunity $opportunity): bool
    {
        if (! $user->hasPermission('opportunities.view')) {
            return false;
        }

        return Opportunity::query()->whereKey($opportunity->id)->visibleTo($user)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('opportunities.create');
    }

    public function update(User $user, Opportunity $opportunity): bool
    {
        if (! $user->hasPermission('opportunities.update')) {
            return false;
        }

        if ($opportunity->isArchived()) {
            return false;
        }

        return $opportunity->isWritableBy($user);
    }

    public function delete(User $user, Opportunity $opportunity): bool
    {
        if (! $user->hasPermission('opportunities.delete')) {
            return false;
        }

        if ($opportunity->isArchived()) {
            return false;
        }

        return $opportunity->isWritableBy($user);
    }

    public function changeOwner(User $user, Opportunity $opportunity): bool
    {
        return $this->update($user, $opportunity);
    }

    public function updateStage(User $user, Opportunity $opportunity): bool
    {
        return $this->update($user, $opportunity);
    }

    public function archive(User $user, Opportunity $opportunity): bool
    {
        if ($opportunity->isArchived()) {
            return false;
        }

        return $this->update($user, $opportunity);
    }

    public function clone(User $user, Opportunity $opportunity): bool
    {
        if (! $user->hasPermission('opportunities.create')) {
            return false;
        }

        return $this->view($user, $opportunity);
    }
}
