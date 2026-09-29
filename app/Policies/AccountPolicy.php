<?php

namespace App\Policies;

use App\Models\Account;
use App\Models\User;

class AccountPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('accounts.view');
    }

    public function view(User $user, Account $account): bool
    {
        if (! $user->hasPermission('accounts.view')) {
            return false;
        }

        return Account::query()->whereKey($account->id)->visibleTo($user)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('accounts.create');
    }

    public function update(User $user, Account $account): bool
    {
        if (! $user->hasPermission('accounts.update')) {
            return false;
        }

        return $account->isWritableBy($user);
    }

    public function delete(User $user, Account $account): bool
    {
        if (! $user->hasPermission('accounts.delete')) {
            return false;
        }

        return $account->isWritableBy($user);
    }

    public function changeOwner(User $user, Account $account): bool
    {
        return $this->update($user, $account);
    }
}
