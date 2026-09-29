<?php

namespace App\Services;

use App\Models\MfaBackupCode;
use App\Models\User;
use App\Notifications\MfaCodeNotification;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class MfaService
{
    public const SESSION_USER_ID = 'mfa.pending_user_id';

    public const SESSION_CODE_HASH = 'mfa.code_hash';

    public const SESSION_CODE_EXPIRES = 'mfa.code_expires_at';

    public const SESSION_REMEMBER = 'mfa.remember';

    public const CODE_TTL_MINUTES = 10;

    /**
     * @return list<string> Plaintext backup codes (shown once)
     */
    public function generateBackupCodes(User $user, int $count = 8): array
    {
        MfaBackupCode::query()->where('user_id', $user->id)->delete();

        $plain = [];
        for ($i = 0; $i < $count; $i++) {
            $code = strtoupper(Str::random(4).'-'.Str::random(4));
            $plain[] = $code;
            MfaBackupCode::query()->create([
                'user_id' => $user->id,
                'code_hash' => Hash::make($code),
            ]);
        }

        return $plain;
    }

    public function issueChallenge(User $user, bool $remember = false): void
    {
        $code = (string) random_int(100000, 999999);

        session([
            self::SESSION_USER_ID => $user->id,
            self::SESSION_CODE_HASH => Hash::make($code),
            self::SESSION_CODE_EXPIRES => now()->addMinutes(self::CODE_TTL_MINUTES)->timestamp,
            self::SESSION_REMEMBER => $remember,
        ]);

        $user->notify(new MfaCodeNotification($code));
    }

    public function clearChallenge(): void
    {
        session()->forget([
            self::SESSION_USER_ID,
            self::SESSION_CODE_HASH,
            self::SESSION_CODE_EXPIRES,
            self::SESSION_REMEMBER,
        ]);
    }

    public function pendingUserId(): ?int
    {
        $id = session(self::SESSION_USER_ID);

        return $id !== null ? (int) $id : null;
    }

    public function verifyOtp(string $code): bool
    {
        $hash = session(self::SESSION_CODE_HASH);
        $expires = (int) session(self::SESSION_CODE_EXPIRES, 0);

        if ($hash === null || $expires < now()->timestamp) {
            return false;
        }

        return Hash::check(trim($code), $hash);
    }

    public function consumeBackupCode(User $user, string $code): bool
    {
        $codes = MfaBackupCode::query()
            ->where('user_id', $user->id)
            ->whereNull('used_at')
            ->get();

        foreach ($codes as $backup) {
            if (Hash::check(strtoupper(trim($code)), $backup->code_hash) || Hash::check(trim($code), $backup->code_hash)) {
                $backup->forceFill(['used_at' => now()])->save();

                return true;
            }
        }

        return false;
    }

    public function disable(User $user): void
    {
        $user->forceFill(['mfa_enabled' => false])->save();
        MfaBackupCode::query()->where('user_id', $user->id)->delete();
    }
}
