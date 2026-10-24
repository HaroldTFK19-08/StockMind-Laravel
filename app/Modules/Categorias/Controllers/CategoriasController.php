<?php

namespace App\Modules\Categorias\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Categorias\Facades\CategoriaFacade;
use App\Modules\Categorias\Requests\ActualizarCategoriaRequest;
use App\Modules\Categorias\Requests\RegistroCategoriaRequest;
use Illuminate\Http\JsonResponse;

class CategoriasController extends Controller
{
    public function __construct(
        private CategoriaFacade $categoriaFacade
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->categoriaFacade->listar()
        );
    }

    public function store(RegistroCategoriaRequest $request): JsonResponse
    {
        $categoria = $this->categoriaFacade->crear(
            $request->validated()
        );

        return response()->json([
            'message' => 'Categoría registrada correctamente.',
            'data' => $categoria,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->categoriaFacade->mostrar($id),
        ]);
    }

    public function update(
        ActualizarCategoriaRequest $request,
        int $id
    ): JsonResponse {
        $categoria = $this->categoriaFacade->actualizar(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Categoría actualizada correctamente.',
            'data' => $categoria,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->categoriaFacade->eliminar($id);

        return response()->json([
            'message' => 'Categoría eliminada correctamente.',
        ]);
    }
}
