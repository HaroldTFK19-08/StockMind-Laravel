<?php

namespace App\Modules\Shared\Enums;

enum EstadoUsuario: string
{
    case Pendiente = 'Pendiente';
    case Activo    = 'Activo';
    case Inactivo  = 'Inactivo';
}
