<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Redis;

class HealthController extends Controller
{
    /**
     * Comprobaciones para balanceadores y orquestadores (DB, cache, cola).
     * Laravel ya expone GET /up (liviano); este endpoint ofrece más detalle opcionalmente protegido por token.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $token = config('monitoring.health_token');
        if (filled($token)) {
            $given = $request->query('token') ?? $request->header('X-Health-Token');
            if (! hash_equals((string) $token, (string) ($given ?? ''))) {
                return response()->json(['status' => 'forbidden'], 403);
            }
        }

        $checks = [
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'queue_connection' => $this->checkQueue(),
        ];

        $ok = ! in_array(false, $checks, true);

        return response()->json([
            'status' => $ok ? 'ok' : 'degraded',
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ], $ok ? 200 : 503);
    }

    private function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();
            DB::select('select 1');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkCache(): bool
    {
        try {
            $key = 'health_probe_'.bin2hex(random_bytes(8));
            Cache::put($key, '1', 5);

            return Cache::pull($key) === '1';
        } catch (\Throwable) {
            return false;
        }
    }

    private function checkQueue(): bool
    {
        $driver = config('queue.default');

        if ($driver === 'sync') {
            return true;
        }

        if ($driver === 'redis') {
            try {
                Redis::connection(config('queue.connections.redis.connection', 'default'))->ping();

                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        if ($driver === 'database') {
            try {
                DB::table(config('queue.connections.database.table', 'jobs'))->limit(1)->count();

                return true;
            } catch (\Throwable) {
                return false;
            }
        }

        try {
            return Queue::getConnectionName() !== '';
        } catch (\Throwable) {
            return false;
        }
    }
}
