<?php

namespace App\Modules\Auth\Services;

use App\Modules\Shared\Enums\EstadoUsuario;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use App\Modules\Usuarios\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Laravel\Sanctum\PersonalAccessToken;

class LoginService
{
    public function login(array $credenciales): array
    {
        // 1. Buscar al usuario y verificar la contraseña
        $usuario = User::where('email', $credenciales['email'])->first();

        if (!$usuario || !Hash::check($credenciales['password'], $usuario->password)) {
            throw ValidationException::withMessages([
                'email' => ['Las credenciales son incorrectas.'],
            ]);
        }

        // 2. Verificar que esté aprobado y activo
        $this->validarEstado($usuario);

        // 3. Crear el token (reemplaza la sesión)
        $token = $usuario->createToken('stockmind')->plainTextToken;

        return [
            'mensaje' => 'Inicio de sesión exitoso.',
            'token'   => $token,
            'usuario' => $this->perfil($usuario),
        ];
    }

    public function logout(User $usuario): void
    {
        $token = $usuario->currentAccessToken();
        // Solo los tokens guardados en la BD (los que crea el login) se pueden borrar
        if ($token instanceof PersonalAccessToken) {
            $token->delete();
        }
    }

    public function perfil(User $usuario): User
    {
        // Devuelve el usuario con sus roles, para que el front sepa qué mostrar
        return $usuario->load('roles');
    }

    private function validarEstado(User $usuario): void
    {
        if ($usuario->estado === EstadoUsuario::Pendiente->value) {
            throw new ReglasNegocioExceptions(
                'Tu cuenta está pendiente de aprobación por un administrador.',
                403
            );
        }

        if ($usuario->estado === EstadoUsuario::Inactivo->value) {
            throw new ReglasNegocioExceptions('Tu cuenta está inactiva.', 403);
        }
    }
}
