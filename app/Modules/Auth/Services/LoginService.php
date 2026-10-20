<?php

namespace App\Modules\Auth\Services;

use App\Modules\Usuarios\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class LoginService
{
    public function login(array $credenciales): array
    {
        if (! Auth::attempt($credenciales)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }
        request()->session()->regenerate();
        return [
            'usuario' => Auth::user(),
            'mensaje' => 'Inicio de sesión exitoso.',
        ];
    }
    public function logout(): void
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    }
}
