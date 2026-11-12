<?php

namespace App\Modules\Shared\Services;

use App\Modules\Shared\Enums\EstadoUsuario;
use App\Modules\Usuarios\Models\User;

class Autorizacion
{
    public function tieneRol(User $usuario, array $roles)
    {
        $retorno = $usuario->roles()->whereIn('rol', $roles)->exists();
        return $retorno;
    }
    public function estaActivo(User $usuario): bool
    {
        return $usuario->estado === EstadoUsuario::Activo->value;
    }
}
