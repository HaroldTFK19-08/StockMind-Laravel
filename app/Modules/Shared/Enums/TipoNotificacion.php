<?php

namespace App\Modules\Shared\Enums;

enum TipoNotificacion: string
{
    case General = 'general';
    case RolAsignado = 'rol_asignado';
    case AmbienteAsignado = 'ambiente_asignado';
    case AsignacionCreada = 'asignacion_creada';
    case AsignacionActualizada = 'asignacion_actualizada';
    case ReporteCreado = 'reporte_creado';
    case ReporteActualizado = 'reporte_actualizado';
    case StockActualizado = 'stock_actualizado';
}
