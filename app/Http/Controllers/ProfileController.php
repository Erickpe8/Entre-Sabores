<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProfileUpdateRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class ProfileController extends Controller
{
    /**
     * Display the user's profile form.
     */
    public function edit(Request $request): View
    {
        return view('profile.edit', [
            'user' => $request->user(),
        ]);
    }

    /**
     * Update the user's profile information.
     */
    public function update(ProfileUpdateRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $user = $request->user();

        $previousUsername = $user->username;
        $targetUsername = $validated['username'] ?? $previousUsername;

        if ($targetUsername !== $previousUsername) {
            $disk = Storage::disk('public');
            $oldDir = 'profiles/'.$previousUsername;
            $newDir = 'profiles/'.$targetUsername;

            if ($disk->exists($oldDir)) {
                $from = $disk->path($oldDir);
                $to = $disk->path($newDir);

                if (! File::isDirectory($to)) {
                    File::moveDirectory($from, $to);
                }

                $photo = $user->profile_photo;
                if (is_string($photo) && $photo !== '' && str_contains($photo, $oldDir.'/')) {
                    $validated['profile_photo'] = str_replace($oldDir.'/', $newDir.'/', $photo);
                }
            }
        }

        $base64 = $validated['profile_photo_base64'] ?? null;
        unset($validated['profile_photo_base64']);

        if (is_string($base64) && $base64 !== '') {
            $image = $base64;
            $image = (string) preg_replace('/^data:image\/\w+;base64,/', '', $image);
            $image = str_replace(' ', '+', $image);

            $imageDecoded = base64_decode($image, true);

            if ($imageDecoded === false) {
                throw ValidationException::withMessages([
                    'profile_photo_base64' => 'No se pudo procesar la imagen del avatar. Intenta con otra foto.',
                ]);
            }

            if (strlen($imageDecoded) > 2048 * 1024) {
                throw ValidationException::withMessages([
                    'profile_photo_base64' => 'La foto de perfil no puede superar los 2 MB.',
                ]);
            }

            $directory = 'profiles/'.$targetUsername;
            $fileName = 'avatar_'.time().'.jpg';
            $path = $directory.'/'.$fileName;

            Storage::disk('public')->put($path, $imageDecoded);

            $previousPath = $validated['profile_photo'] ?? $user->profile_photo;
            if ($previousPath && $previousPath !== $path && Storage::disk('public')->exists($previousPath)) {
                Storage::disk('public')->delete($previousPath);
            }

            $validated['profile_photo'] = $path;
        }

        $user->fill($validated);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return Redirect::route('profile.edit')
            ->with('status', 'profile-updated')
            ->with('success', 'Perfil actualizado correctamente');
    }

    /**
     * Delete the user's account.
     */
    public function destroy(Request $request): RedirectResponse
    {
        $request->validateWithBag('userDeletion', [
            'password' => ['required', 'current_password'],
        ]);

        $user = $request->user();

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return Redirect::to('/');
    }
}
