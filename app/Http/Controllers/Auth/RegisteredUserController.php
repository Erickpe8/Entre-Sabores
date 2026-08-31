<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\ProfilePhotoService;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     */
    public function store(RegisterRequest $request, ProfilePhotoService $profilePhotos): RedirectResponse
    {
        $validated = $request->validated();

        $username = User::generateUniqueUsername(
            $validated['first_name'],
            $validated['last_name'],
            User::normalizeInstagramHandle($validated['instagram'] ?? null)
        );

        $photoPath = $profilePhotos->storeFromUploadedFile(
            $request->file('profile_photo'),
            'profiles/'.$username,
        );

        $instagramProfile = User::normalizeInstagramHandle($validated['instagram'] ?? null);

        $user = User::create([
            'first_name' => $validated['first_name'],
            'last_name' => $validated['last_name'],
            'username' => $username,
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'country' => $validated['country'],
            'profile_photo' => $photoPath,
            'description' => $validated['description'] ?? null,
            'instagram' => $instagramProfile,
        ]);

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false).'?onboarding=1');
    }
}
