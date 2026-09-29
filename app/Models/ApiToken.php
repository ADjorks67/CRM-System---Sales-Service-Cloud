<?php

namespace App\Models;

use App\Models\Concerns\HasAuditFields;
use Database\Factories\ApiTokenFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

#[Fillable([
    'user_id',
    'name',
    'token',
    'last_used_at',
    'expires_at',
])]
class ApiToken extends Model
{
    /** @use HasFactory<ApiTokenFactory> */
    use HasAuditFields, HasFactory;

    protected function casts(): array
    {
        return [
            'last_used_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return array{token: self, plain: string}
     */
    public static function issue(User $user, string $name, ?\DateTimeInterface $expiresAt = null): array
    {
        $plain = Str::random(40);

        $token = self::query()->create([
            'user_id' => $user->id,
            'name' => $name,
            'token' => hash('sha256', $plain),
            'expires_at' => $expiresAt,
        ]);

        return ['token' => $token, 'plain' => $plain];
    }

    public function matches(string $plain): bool
    {
        return hash_equals($this->token, hash('sha256', $plain));
    }

    public function isExpired(): bool
    {
        return $this->expires_at !== null && $this->expires_at->isPast();
    }
}
