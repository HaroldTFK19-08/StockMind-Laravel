<?php

namespace App\Modules\Shared\Enums;

enum EstadoElemento: string
{
    case Disponible      = 'disponible';
    case Asignado        = 'asignado';
    case Prestado        = 'prestado';
    case EnTraslado      = 'en traslado';
    case EnMantenimiento = 'en mantenimiento';
    case Danado          = 'danado';
    case DadoDeBaja      = 'dado_de_baja';
}
