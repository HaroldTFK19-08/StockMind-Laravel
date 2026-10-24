<?php

namespace App\Modules\Elementos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Elementos\Facades\ElementoFacade;
use App\Modules\Elementos\Requests\ActualizarElementoRequest;
use App\Modules\Elementos\Requests\RegistroElementoRequest;
use Illuminate\Http\JsonResponse;

class ElementosController extends Controller
{
    public function __construct(
        private ElementoFacade $elementoFacade
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->elementoFacade->listar()
        );
    }

    public function store(RegistroElementoRequest $request): JsonResponse
    {
        $elemento = $this->elementoFacade->crear(
            $request->validated()
        );

        return response()->json([
            'message' => 'Elemento registrado correctamente.',
            'data' => $elemento,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->elementoFacade->mostrar($id),
        ]);
    }

    public function update(
        ActualizarElementoRequest $request,
        int $id
    ): JsonResponse {
        $elemento = $this->elementoFacade->actualizar(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Elemento actualizado correctamente.',
            'data' => $elemento,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->elementoFacade->eliminar($id);

        return response()->json([
            'message' => 'Elemento eliminado correctamente.',
        ]);
    }
}
