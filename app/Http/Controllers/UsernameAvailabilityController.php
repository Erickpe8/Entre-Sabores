<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UsernameAvailabilityController extends Controller
{
    /**
     * Comprueba si un nombre de usuario está libre (JSON). Usuarios autenticados ignoran su propio id.
     */
    public function __invoke(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'username' => ['required', 'string', 'min:3', 'max:30', 'alpha_dash:ascii'],
        ]);

        $normalized = Str::lower(trim($validated['username']));

        $exceptId = $request->user()?->id;

        $taken = User::query()
            ->where('username', $normalized)
            ->when($exceptId !== null, fn ($q) => $q->where('id', '!=', $exceptId))
            ->exists();

        return response()->json([
            'available' => ! $taken,
            'username' => $normalized,
        ]);
    }
}
