<?php

namespace App\Policies;

use App\Models\Dashboard;
use App\Models\User;

class DashboardPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermission('dashboards.view');
    }

    public function view(User $user, Dashboard $dashboard): bool
    {
        if (! $user->hasPermission('dashboards.view')) {
            return false;
        }

        return Dashboard::query()->whereKey($dashboard->id)->visibleTo($user)->exists();
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('dashboards.create');
    }

    public function update(User $user, Dashboard $dashboard): bool
    {
        if (! $user->hasPermission('dashboards.update')) {
            return false;
        }

        return $dashboard->isWritableBy($user);
    }

    public function delete(User $user, Dashboard $dashboard): bool
    {
        if (! $user->hasPermission('dashboards.delete')) {
            return false;
        }

        return $dashboard->isWritableBy($user);
    }

    public function clone(User $user, Dashboard $dashboard): bool
    {
        return $this->create($user) && $this->view($user, $dashboard);
    }
}
