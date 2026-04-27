<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\View\View;

class UserController extends Controller
{
    /**
     * Perfil público por username (sin autenticación).
     */
    public function show(string $username): View
    {
        $user = User::query()->where('username', $username)->firstOrFail();

        return view('profile.public', [
            'user' => $user,
        ]);
    }
}
