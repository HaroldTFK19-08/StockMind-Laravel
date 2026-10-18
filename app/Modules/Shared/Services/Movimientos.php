<?php

namespace App\Modules\Shared\Services;

use App\Modules\Elementos\Models\Element;
use App\Modules\Movimientos\Models\Motion;
/**
 * Subsistema compartido: registra el historial de cada elemento.
 * Lo usan todas las fachadas.
 */
class Movimientos
{
    public const INGRESO       = 'ingreso';
    public const ASIGNACION    = 'asignacion';
    public const DEVOLUCION    = 'devolucion';
    public const TRASLADO      = 'traslado';
    public const CAMBIO_ESTADO = 'cambio_estado';
    public const BAJA          = 'baja';
    /**
     * @param array $referencias Ej: ['traslado_id' => 5] o ['asignacion_id' => 3]
     */
    public function registrar(Element $elemento, string $tipo, string $descripcion, array $referencias = []): Motion
    {
        return Motion::create(array_merge([
            'elemento_id' => $elemento->id,
            'tipo'        => $tipo,
            'descripcion' => $descripcion,
            'fecha'       => now(),
        ], $referencias));
    }
    public function historial(Element $elemento)
    {
        return Motion::where('elemento_id', $elemento->id)
            ->orderByDesc('fecha')
            ->get();
    }
}
