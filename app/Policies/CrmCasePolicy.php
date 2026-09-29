<?php

namespace App\Policies;

use App\Models\CrmCase;
use App\Models\User;

class CrmCasePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('cases.view');
    }

    public function view(User $user, CrmCase $crmCase): bool
    {
        if (! $user->hasPermission('cases.view')) {
            return false;
        }

        return CrmCase::query()->whereKey($crmCase->id)->visibleTo($user)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('cases.create');
    }

    public function update(User $user, CrmCase $crmCase): bool
    {
        if (! $user->hasPermission('cases.update')) {
            return false;
        }

        if ($crmCase->isReadOnlyClosed()) {
            return false;
        }

        return $crmCase->isWritableBy($user);
    }

    public function delete(User $user, CrmCase $crmCase): bool
    {
        if (! $user->hasPermission('cases.delete')) {
            return false;
        }

        if ($crmCase->isReadOnlyClosed()) {
            return false;
        }

        return $crmCase->isWritableBy($user);
    }

    public function changeOwner(User $user, CrmCase $crmCase): bool
    {
        if (! $user->hasPermission('cases.update')) {
            return false;
        }

        if ($crmCase->isReadOnlyClosed()) {
            return false;
        }

        return $crmCase->isWritableBy($user);
    }

    public function changeStatus(User $user, CrmCase $crmCase): bool
    {
        return $this->update($user, $crmCase);
    }

    public function reopen(User $user, CrmCase $crmCase): bool
    {
        if (! $user->hasPermission('cases.update')) {
            return false;
        }

        if (! $crmCase->isClosed()) {
            return false;
        }

        return $crmCase->isWritableBy($user);
    }
}
