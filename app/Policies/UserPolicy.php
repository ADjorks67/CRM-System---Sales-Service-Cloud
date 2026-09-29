<?php

namespace App\Policies;

use App\Enums\RoleSlug;
use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasRole(RoleSlug::SystemAdministrator);
    }

    public function view(User $user, User $model): bool
    {
        return $user->hasRole(RoleSlug::SystemAdministrator);
    }

    public function create(User $user): bool
    {
        return $user->hasRole(RoleSlug::SystemAdministrator);
    }

    public function update(User $user, User $model): bool
    {
        return $user->hasRole(RoleSlug::SystemAdministrator);
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole(RoleSlug::SystemAdministrator) && $user->id !== $model->id;
    }
}
