<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;

class VercelCronController extends Controller
{
    public function schedule(Request $request): JsonResponse
    {
        $this->authorizeCron($request);

        Artisan::call('schedule:run');

        return response()->json([
            'ok' => true,
            'command' => 'schedule:run',
            'output' => trim(Artisan::output()),
        ]);
    }

    public function queue(Request $request): JsonResponse
    {
        $this->authorizeCron($request);

        Artisan::call('queue:work', [
            '--stop-when-empty' => true,
            '--max-time' => 55,
            '--tries' => 3,
            '--sleep' => 1,
        ]);

        return response()->json([
            'ok' => true,
            'command' => 'queue:work',
            'output' => trim(Artisan::output()),
        ]);
    }

    private function authorizeCron(Request $request): void
    {
        $secret = config('monitoring.cron_secret');
        if (! filled($secret)) {
            abort(503, 'CRON_SECRET not configured');
        }

        $given = $request->bearerToken();
        if (! hash_equals((string) $secret, (string) ($given ?? ''))) {
            abort(403, 'Forbidden');
        }
    }
}
