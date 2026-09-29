<?php

namespace App\Services;

use App\Models\PasswordHistory;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class PasswordHistoryService
{
    public const HISTORY_LIMIT = 5;

    public function store(User $user, string $plainPassword): void
    {
        PasswordHistory::query()->create([
            'user_id' => $user->id,
            'password' => Hash::make($plainPassword),
        ]);

        $this->prune($user);
    }

    public function wasUsedRecently(User $user, string $plainPassword): bool
    {
        $histories = PasswordHistory::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->limit(self::HISTORY_LIMIT)
            ->get();

        foreach ($histories as $history) {
            if (Hash::check($plainPassword, $history->password)) {
                return true;
            }
        }

        if (Hash::check($plainPassword, $user->password)) {
            return true;
        }

        return false;
    }

    public function prune(User $user): void
    {
        $keepIds = PasswordHistory::query()
            ->where('user_id', $user->id)
            ->latest('created_at')
            ->limit(self::HISTORY_LIMIT)
            ->pluck('id');

        PasswordHistory::query()
            ->where('user_id', $user->id)
            ->whereNotIn('id', $keepIds)
            ->delete();
    }
}
