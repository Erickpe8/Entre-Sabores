<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Support\RegisterCountryCatalog;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (! $this->has('email')) {
            return;
        }

        $email = $this->input('email');
        if (! is_string($email)) {
            return;
        }

        $this->merge([
            'email' => mb_strtolower(trim($email)),
        ]);
    }

    public function rules(): array
    {
        return [
            'first_name' => ['required', 'string', 'max:50'],
            'last_name' => ['required', 'string', 'max:50'],
            'instagram' => ['nullable', 'string', 'max:100', 'regex:/^[a-zA-Z0-9._]{0,100}$/'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class, 'email')],
            'password' => ['required', 'confirmed', 'min:8', Password::defaults()],
            'country' => ['required', 'string', Rule::in(RegisterCountryCatalog::NAMES)],
            'profile_photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Escribe tu nombre.',
            'last_name.required' => 'Escribe tu apellido.',
            'email.required' => 'Necesitamos tu correo electrónico.',
            'email.lowercase' => 'El correo electrónico debe estar en minúsculas.',
            'email.email' => 'Ese correo no es válido.',
            'email.unique' => 'Este correo electrónico ya está registrado.',
            'password.required' => 'Crea una contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'Tu contraseña debe tener al menos 8 caracteres.',
            'country.required' => 'Debes seleccionar un país.',
            'country.in' => 'Selecciona un país válido de la lista.',
            'profile_photo.required' => 'La foto de perfil es obligatoria.',
            'profile_photo.image' => 'La foto de perfil debe ser una imagen válida.',
            'profile_photo.mimes' => 'La foto de perfil debe estar en formato JPG, PNG o WebP.',
            'profile_photo.max' => 'La foto de perfil no puede superar los 2 MB.',
            'description.max' => 'La descripción no puede superar los 500 caracteres.',
            'instagram.regex' => 'El usuario de Instagram solo puede incluir letras, números, puntos y guiones bajos.',
        ];
    }
}
