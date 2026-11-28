<?php

namespace App\Modules\Auth\Services;

use App\Modules\Usuarios\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AccessCodeService
{
    public const EXPIRATION_MINUTES = 30;
    public const MAX_ATTEMPTS = 5;

    public function emitir(User $usuario): string
    {
        $codigo = (string) random_int(100000, 999999);

        $usuario->forceFill([
            'access_code_hash' => $codigo,
            'access_code_expires_at' => now()->addMinutes(self::EXPIRATION_MINUTES),
            'access_code_used_at' => null,
            'access_code_attempts' => 0,
        ])->save();

        return $codigo;
    }

    public function verificar(User $usuario, string $codigo): void
    {
        $this->asegurarCodigoValido($usuario);

        if (! Hash::check($codigo, $usuario->access_code_hash)) {
            $usuario->forceFill(['access_code_attempts' => $usuario->access_code_attempts + 1])->save();
            throw ValidationException::withMessages(['codigo' => ['El código de acceso es inválido.']]);
        }

        $usuario->forceFill([
            'access_code_hash' => null,
            'access_code_expires_at' => null,
            'access_code_used_at' => now(),
            'access_code_attempts' => 0,
        ])->save();
    }

    private function asegurarCodigoValido(User $usuario): void
    {
        $error = match (true) {
            ! $usuario->access_code_hash || ! $usuario->access_code_expires_at => 'No hay un código de acceso activo. Solicita uno nuevo al administrador.',
            $usuario->access_code_used_at !== null => 'Este código ya fue utilizado.',
            now()->greaterThan($usuario->access_code_expires_at) => 'El código de acceso expiró. Solicita uno nuevo al administrador.',
            $usuario->access_code_attempts >= self::MAX_ATTEMPTS => 'Has excedido los intentos permitidos. Solicita un nuevo código.',
            default => null,
        };

        if ($error !== null) {
            throw ValidationException::withMessages(['codigo' => [$error]]);
        }
    }

    public function limpiar(User $usuario): void
    {
        $usuario->forceFill([
            'access_code_hash' => null,
            'access_code_expires_at' => null,
            'access_code_used_at' => null,
            'access_code_attempts' => 0,
        ])->save();
    }
}
