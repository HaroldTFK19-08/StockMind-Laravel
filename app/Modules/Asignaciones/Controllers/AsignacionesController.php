<?php

namespace App\Modules\Asignaciones\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Asignaciones\Facades\AsignacionFacade;
use App\Modules\Asignaciones\Requests\ActualizarAsignacionRequest;
use App\Modules\Asignaciones\Requests\RegistroAsignacionRequest;
use Illuminate\Http\JsonResponse;

class AsignacionesController extends Controller
{
    public function __construct(
        private AsignacionFacade $asignacionFacade
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->asignacionFacade->listar()
        );
    }

    public function store(RegistroAsignacionRequest $request): JsonResponse
    {
        $asignacion = $this->asignacionFacade->crear(
            $request->validated()
        );

        return response()->json([
            'message' => 'Asignación registrada correctamente.',
            'data' => $asignacion,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->asignacionFacade->mostrar($id),
        ]);
    }

    public function update(
        ActualizarAsignacionRequest $request,
        int $id
    ): JsonResponse {
        $asignacion = $this->asignacionFacade->actualizar(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Asignación actualizada correctamente.',
            'data' => $asignacion,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->asignacionFacade->eliminar($id);

        return response()->json([
            'message' => 'Asignación eliminada correctamente.',
        ]);
    }
}
