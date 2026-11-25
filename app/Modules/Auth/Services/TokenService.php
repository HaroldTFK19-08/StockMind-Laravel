<?php

namespace App\Modules\Auth\Services;

use App\Modules\Usuarios\Models\User;

class TokenService
{
    /**
     * Crea el token en BD sin entregar el texto plano al usuario.
     * Queda a la espera de que el administrador asigne un rol.
     */
    public function crearPendiente(User $usuario, string $nombre = 'stockmind'): void
    {
        $usuario->createToken($nombre);
    }

    /**
     * Emite un token y devuelve su texto plano.
     */
    public function emitir(User $usuario, string $nombre = 'stockmind'): string
    {
        return $usuario->createToken($nombre)->plainTextToken;
    }

    /**
     * Reemplaza el token pendiente por uno nuevo y lo entrega.
     */
    public function activar(User $usuario, string $nombre = 'stockmind'): string
    {
        $usuario->tokens()->delete();

        return $this->emitir($usuario, $nombre);
    }
}
