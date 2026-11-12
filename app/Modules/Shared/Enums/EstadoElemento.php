<?php

namespace App\Modules\Shared\Enums;

enum EstadoElemento: string
{
    case Disponible      = 'Disponible';
    case Asignado        = 'Asignado';
    case Prestado        = 'Prestado';
    case EnTraslado      = 'En traslado';
    case EnMantenimiento = 'En mantenimiento';
    case Danado          = 'Danado';
    case DadoDeBaja      = 'Dado de baja';
}
