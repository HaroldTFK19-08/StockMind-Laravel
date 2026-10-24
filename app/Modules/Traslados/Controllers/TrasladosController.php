<?php

namespace App\Modules\Traslados\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Traslados\Facades\TrasladoFacade;
use App\Modules\Traslados\Requests\ActualizarTrasladoRequest;
use App\Modules\Traslados\Requests\RegistroTrasladoRequest;
use Illuminate\Http\JsonResponse;

class TrasladosController extends Controller
{
    public function __construct(
        private TrasladoFacade $trasladoFacade
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->trasladoFacade->listar()
        );
    }

    public function store(RegistroTrasladoRequest $request): JsonResponse
    {
        $traslado = $this->trasladoFacade->crear(
            $request->validated()
        );

        return response()->json([
            'message' => 'Traslado registrado correctamente.',
            'data' => $traslado,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->trasladoFacade->mostrar($id),
        ]);
    }

    public function update(
        ActualizarTrasladoRequest $request,
        int $id
    ): JsonResponse {
        $traslado = $this->trasladoFacade->actualizar(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Traslado actualizado correctamente.',
            'data' => $traslado,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->trasladoFacade->eliminar($id);

        return response()->json([
            'message' => 'Traslado eliminado correctamente.',
        ]);
    }
}
