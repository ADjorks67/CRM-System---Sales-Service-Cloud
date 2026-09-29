<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Throwable;

class HealthController extends Controller
{
    /**
     * NFR-REL-005: health check for infrastructure monitoring (app + database).
     */
    public function __invoke(): JsonResponse
    {
        $checks = [
            'app' => 'ok',
            'database' => 'ok',
        ];

        $status = 200;

        try {
            DB::connection()->getPdo();
            DB::select('select 1');
        } catch (Throwable) {
            $checks['database'] = 'fail';
            $status = 503;
        }

        return response()->json([
            'status' => $status === 200 ? 'ok' : 'degraded',
            'checks' => $checks,
            'timestamp' => now()->toIso8601String(),
        ], $status);
    }
}
