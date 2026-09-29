<?php

namespace App\Rules;

use App\Models\User;
use App\Services\PasswordHistoryService;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class NotRecentPassword implements ValidationRule
{
    public function __construct(private readonly User $user) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail('The :attribute must be a string.');

            return;
        }

        $service = app(PasswordHistoryService::class);

        if ($service->wasUsedRecently($this->user, $value)) {
            $fail('The :attribute must not match any of your last '.PasswordHistoryService::HISTORY_LIMIT.' passwords.');
        }
    }
}
