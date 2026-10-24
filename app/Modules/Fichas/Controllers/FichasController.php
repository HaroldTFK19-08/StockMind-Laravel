<?php

namespace App\Modules\Fichas\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Fichas\Facades\FichaFacade;
use App\Modules\Fichas\Requests\ActualizarFichaRequest;
use App\Modules\Fichas\Requests\RegistroFichaRequest;
use Illuminate\Http\JsonResponse;

class FichasController extends Controller
{
    public function __construct(
        private FichaFacade $fichaFacade
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->fichaFacade->listar()
        );
    }

    public function store(RegistroFichaRequest $request): JsonResponse
    {
        $ficha = $this->fichaFacade->crear(
            $request->validated()
        );

        return response()->json([
            'message' => 'Ficha registrada correctamente.',
            'data' => $ficha,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->fichaFacade->mostrar($id),
        ]);
    }

    public function update(
        ActualizarFichaRequest $request,
        int $id
    ): JsonResponse {
        $ficha = $this->fichaFacade->actualizar(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Ficha actualizada correctamente.',
            'data' => $ficha,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->fichaFacade->eliminar($id);

        return response()->json([
            'message' => 'Ficha eliminada correctamente.',
        ]);
    }
}
