<?php

namespace App\Policies;

use App\Models\SavedSearch;
use App\Models\User;

class SavedSearchPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->id !== null;
    }

    public function view(User $user, SavedSearch $savedSearch): bool
    {
        return $savedSearch->isOwnedBy($user);
    }

    public function create(User $user): bool
    {
        return $user->id !== null;
    }

    public function update(User $user, SavedSearch $savedSearch): bool
    {
        return $savedSearch->isOwnedBy($user);
    }

    public function delete(User $user, SavedSearch $savedSearch): bool
    {
        return $savedSearch->isOwnedBy($user);
    }
}
