<?php

namespace App\Modules\Ambientes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Ambientes\Facades\AmbienteFacade;
use App\Modules\Ambientes\Requests\ActualizarAmbienteRequest;
use App\Modules\Ambientes\Requests\RegistroAmbienteRequest;
use Illuminate\Http\JsonResponse;

class AmbientesController extends Controller
{
    public function __construct(
        private AmbienteFacade $ambienteFacade
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->ambienteFacade->listar()
        );
    }

    public function store(RegistroAmbienteRequest $request): JsonResponse
    {
        $ambiente = $this->ambienteFacade->crear(
            $request->validated()
        );

        return response()->json([
            'message' => 'Ambiente registrado correctamente.',
            'data' => $ambiente,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->ambienteFacade->mostrar($id),
        ]);
    }

    public function update(
        ActualizarAmbienteRequest $request,
        int $id
    ): JsonResponse {
        $ambiente = $this->ambienteFacade->actualizar(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Ambiente actualizado correctamente.',
            'data' => $ambiente,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->ambienteFacade->eliminar($id);

        return response()->json([
            'message' => 'Ambiente eliminado correctamente.',
        ]);
    }
}
