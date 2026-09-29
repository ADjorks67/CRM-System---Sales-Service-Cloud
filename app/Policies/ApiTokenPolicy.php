<?php

namespace App\Policies;

use App\Models\ApiToken;
use App\Models\User;

class ApiTokenPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->id !== null;
    }

    public function create(User $user): bool
    {
        return $user->id !== null;
    }

    public function delete(User $user, ApiToken $apiToken): bool
    {
        return (int) $apiToken->user_id === (int) $user->id;
    }
}
