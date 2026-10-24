<?php

namespace App\Modules\Reportes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Reportes\Facades\ReporteFacade;
use App\Modules\Reportes\Requests\ActualizarReporteRequest;
use App\Modules\Reportes\Requests\RegistroReporteRequest;
use Illuminate\Http\JsonResponse;

class ReportesController extends Controller
{
    public function __construct(
        private ReporteFacade $reporteFacade
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->reporteFacade->listar()
        );
    }

    public function store(RegistroReporteRequest $request): JsonResponse
    {
        $reporte = $this->reporteFacade->crear(
            $request->validated()
        );

        return response()->json([
            'message' => 'Reporte registrado correctamente.',
            'data' => $reporte,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->reporteFacade->mostrar($id),
        ]);
    }

    public function update(
        ActualizarReporteRequest $request,
        int $id
    ): JsonResponse {
        $reporte = $this->reporteFacade->actualizar(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Reporte actualizado correctamente.',
            'data' => $reporte,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->reporteFacade->eliminar($id);

        return response()->json([
            'message' => 'Reporte eliminado correctamente.',
        ]);
    }
}
