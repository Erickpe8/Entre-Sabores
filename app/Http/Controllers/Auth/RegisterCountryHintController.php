<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Support\RegisterCountryDetector;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RegisterCountryHintController extends Controller
{
    public function hint(Request $request, RegisterCountryDetector $detector): JsonResponse
    {
        $result = $detector->hintFromRequest($request);

        if ($result === null) {
            return response()->json([
                'detected' => false,
            ]);
        }

        return response()->json([
            'detected' => true,
            ...$result,
        ]);
    }

    public function resolve(Request $request, RegisterCountryDetector $detector): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $result = $detector->resolveCoordinates(
            (float) $validated['latitude'],
            (float) $validated['longitude'],
        );

        if ($result === null) {
            return response()->json([
                'detected' => false,
                'message' => 'No pudimos determinar tu país desde la ubicación.',
            ], 422);
        }

        return response()->json([
            'detected' => true,
            ...$result,
        ]);
    }
}
