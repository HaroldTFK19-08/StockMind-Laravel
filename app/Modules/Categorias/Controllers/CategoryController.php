<?php

namespace App\Modules\Categorias\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Categorias\Facades\CategoriaFacade;
use App\Modules\Categorias\Requests\RegistroRequest;
use App\Modules\Categorias\Requests\ActualizarRequest;
use Illuminate\Http\JsonResponse;

class CategoryController extends Controller
{
    public function __construct(
        private CategoriaFacade $categoriaFacade
    ) {}

    /**
     * Listar categorías.
     */
    public function index(): JsonResponse
    {
        $categorias = $this->categoriaFacade->listar();

        return response()->json([
            'message' => 'Categorías obtenidas correctamente.',
            'data' => $categorias,
        ], 200);
    }

    /**
     * Registrar una categoría.
     */
    public function store(RegistroRequest $request): JsonResponse
    {
        $categoria = $this->categoriaFacade->crear(
            $request->validated()
        );
        return response()->json([
            'message' => 'Categoría registrada correctamente.',
            'data' => $categoria,
        ], 201);
    }
    /**
     * Mostrar una categoría.
     */
    public function show(string $id): JsonResponse
    {
        $categoria = $this->categoriaFacade->mostrar((int) $id);
        return response()->json([
            'message' => 'Categoría obtenida correctamente.',
            'data' => $categoria,
        ], 200);
    }
    /**
     * Actualizar una categoría.
     */
    public function update(
        ActualizarRequest $request,
        string $id
    ): JsonResponse {
        $categoria = $this->categoriaFacade->actualizar(
            (int) $id,
            $request->validated()
        );
        return response()->json([
            'message' => 'Categoría actualizada correctamente.',
            'data' => $categoria,
        ], 200);
    }
    /**
     * Eliminar una categoría.
     */
    public function destroy(string $id): JsonResponse
    {
        $this->categoriaFacade->eliminar((int) $id);
        return response()->json([
            'message' => 'Categoría eliminada correctamente.',
        ], 200);
    }
}
