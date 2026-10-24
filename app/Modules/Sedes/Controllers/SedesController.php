<?php

namespace App\Modules\Sedes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Sedes\Facades\SedeFacade;
use App\Modules\Sedes\Requests\ActualizarSedeRequest;
use App\Modules\Sedes\Requests\RegistroSedeRequest;
use Illuminate\Http\JsonResponse;

class SedesController extends Controller
{
    public function __construct(
        private SedeFacade $sedeFacade
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->sedeFacade->listar()
        );
    }

    public function store(RegistroSedeRequest $request): JsonResponse
    {
        $sede = $this->sedeFacade->crear(
            $request->validated()
        );

        return response()->json([
            'message' => 'Sede registrada correctamente.',
            'data' => $sede,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->sedeFacade->mostrar($id),
        ]);
    }

    public function update(
        ActualizarSedeRequest $request,
        int $id
    ): JsonResponse {
        $sede = $this->sedeFacade->actualizar(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Sede actualizada correctamente.',
            'data' => $sede,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->sedeFacade->eliminar($id);

        return response()->json([
            'message' => 'Sede eliminada correctamente.',
        ]);
    }
}
