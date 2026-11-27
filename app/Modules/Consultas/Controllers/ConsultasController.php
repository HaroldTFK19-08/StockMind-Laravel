<?php

namespace App\Modules\Consultas\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Consultas\Facades\ConsultaFacade;
use App\Modules\Consultas\Requests\ConsultaRequest;
use Illuminate\Http\JsonResponse;

class ConsultasController extends Controller
{
    public function __construct(
        private ConsultaFacade $consultaFacade
    ) {}

    public function instructores(ConsultaRequest $request): JsonResponse
    {
        return response()->json(
            $this->consultaFacade->instructores($request)
        );
    }

    public function cuentasDante(ConsultaRequest $request): JsonResponse
    {
        return response()->json(
            $this->consultaFacade->cuentasDante($request)
        );
    }

    public function reportes(ConsultaRequest $request): JsonResponse
    {
        return response()->json(
            $this->consultaFacade->reportes($request)
        );
    }

    public function misSolicitudes(ConsultaRequest $request): JsonResponse
    {
        return response()->json(
            $this->consultaFacade->misSolicitudes($request, $request->user())
        );
    }

    public function solicitudesFicha(ConsultaRequest $request): JsonResponse
    {
        return response()->json(
            $this->consultaFacade->solicitudesFicha($request, $request->user())
        );
    }
}
