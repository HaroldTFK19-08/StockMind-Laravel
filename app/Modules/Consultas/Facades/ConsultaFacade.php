<?php

namespace App\Modules\Consultas\Facades;

use App\Modules\Consultas\Requests\ConsultaRequest;
use App\Modules\Consultas\Services\ConsultaService;
use App\Modules\Usuarios\Models\User;

/**
 * Fachada delgada: sólo reenvía al servicio de consultas.
 *
 * No extiende CrudFacade porque este módulo es de solo lectura.
 */
class ConsultaFacade
{
    public function __construct(
        private ConsultaService $consultaService
    ) {}

    public function instructores(ConsultaRequest $request)
    {
        return $this->consultaService->instructores($request);
    }

    public function cuentasDante(ConsultaRequest $request)
    {
        return $this->consultaService->cuentasDante($request);
    }

    public function reportes(ConsultaRequest $request)
    {
        return $this->consultaService->reportes($request);
    }

    public function misSolicitudes(ConsultaRequest $request, User $usuario): array
    {
        return $this->consultaService->misSolicitudes($request, $usuario);
    }

    public function solicitudesFicha(ConsultaRequest $request, User $usuario)
    {
        return $this->consultaService->solicitudesFicha($request, $usuario);
    }
}
