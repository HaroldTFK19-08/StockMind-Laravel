<?php

namespace App\Modules\Centros\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Centros\Facades\CentroFacade;
use App\Modules\Centros\Requests\RegistroCentroRequest;
use App\Modules\Centros\Requests\ActualizarCentroRequest;
use Illuminate\Http\JsonResponse;

class CentrosController extends Controller
{
    public function __construct(
        private CentroFacade $centroFacade
    ) {}
    public function index(): JsonResponse
    {
        return response()->json(
            $this->centroFacade->listar()
        );
    }
    public function store(
        RegistroCentroRequest $request
    ): JsonResponse {
        $centro = $this->centroFacade->crear(
            $request->validated()
        );
        return response()->json([
            'message' => 'Centro registrado correctamente.',
            'data' => $centro,
        ], 201);
    }
    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->centroFacade->mostrar($id),
        ]);
    }
    public function update(
        ActualizarCentroRequest $request,
        int $id
    ): JsonResponse {
        $centro = $this->centroFacade->actualizar(
            $id,
            $request->validated()
        );
        return response()->json([
            'message' => 'Centro actualizado correctamente.',
            'data' => $centro,
        ]);
    }
    public function destroy(int $id): JsonResponse
    {
        $this->centroFacade->eliminar($id);
        return response()->json([
            'message' => 'Centro eliminado correctamente.',
        ]);
    }
}
