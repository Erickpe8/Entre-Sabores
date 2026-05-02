<?php

namespace App\Http\Controllers;

use App\Support\OperationalMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Snapshot ligero de contadores (proteger en prod con token o red interna).
 */
class InternalMetricsController extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        $token = config('monitoring.metrics_token');
        if (filled($token)) {
            $given = $request->bearerToken() ?? $request->query('token');
            if (! hash_equals((string) $token, (string) ($given ?? ''))) {
                return response()->json(['error' => 'forbidden'], 403);
            }
        }

        return response()->json([
            'app' => config('app.name'),
            'env' => config('app.env'),
            'metrics' => OperationalMetrics::snapshot(),
        ]);
    }
}
