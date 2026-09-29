<?php

namespace App\Policies;

use App\Models\Lead;
use App\Models\User;

class LeadPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('leads.view');
    }

    public function view(User $user, Lead $lead): bool
    {
        if (! $user->hasPermission('leads.view')) {
            return false;
        }

        return Lead::query()->whereKey($lead->id)->visibleTo($user)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('leads.create');
    }

    public function update(User $user, Lead $lead): bool
    {
        if (! $user->hasPermission('leads.update')) {
            return false;
        }

        if ($lead->isReadOnlyConverted()) {
            return false;
        }

        return $lead->isWritableBy($user);
    }

    public function delete(User $user, Lead $lead): bool
    {
        if (! $user->hasPermission('leads.delete')) {
            return false;
        }

        if ($lead->isReadOnlyConverted()) {
            return false;
        }

        return $lead->isWritableBy($user);
    }

    public function changeOwner(User $user, Lead $lead): bool
    {
        return $this->update($user, $lead);
    }

    public function changeStatus(User $user, Lead $lead): bool
    {
        return $this->update($user, $lead);
    }
}
