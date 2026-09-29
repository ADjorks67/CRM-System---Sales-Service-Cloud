<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;

class DashboardFilterService
{
    public const SESSION_KEY = 'dashboard.global_filters';

    /**
     * @return array{date_from: string|null, date_to: string|null, owner_id: int|null}
     */
    public function get(Request $request): array
    {
        $stored = Session::get(self::SESSION_KEY, []);

        return [
            'date_from' => $stored['date_from'] ?? now()->startOfYear()->toDateString(),
            'date_to' => $stored['date_to'] ?? now()->endOfYear()->toDateString(),
            'owner_id' => isset($stored['owner_id']) ? (int) $stored['owner_id'] : null,
        ];
    }

    /**
     * @param  array<string, mixed>  $input
     */
    public function store(array $input): void
    {
        Session::put(self::SESSION_KEY, [
            'date_from' => $input['date_from'] ?? null,
            'date_to' => $input['date_to'] ?? null,
            'owner_id' => filled($input['owner_id'] ?? null) ? (int) $input['owner_id'] : null,
        ]);
    }
}
