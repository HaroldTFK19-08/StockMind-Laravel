<?php

namespace App\Modules\Almacenes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Almacenes\Facades\AlmacenFacade;
use App\Modules\Almacenes\Requests\ActualizarAlmacenRequest;
use App\Modules\Almacenes\Requests\RegistroAlmacenRequest;
use Illuminate\Http\JsonResponse;

class AlmacenesController extends Controller
{
    public function __construct(
        private AlmacenFacade $almacenFacade
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->almacenFacade->listar()
        );
    }

    public function store(RegistroAlmacenRequest $request): JsonResponse
    {
        $almacen = $this->almacenFacade->crear(
            $request->validated()
        );

        return response()->json([
            'message' => 'Almacén registrado correctamente.',
            'data' => $almacen,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->almacenFacade->mostrar($id),
        ]);
    }

    public function update(
        ActualizarAlmacenRequest $request,
        int $id
    ): JsonResponse {
        $almacen = $this->almacenFacade->actualizar(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Almacén actualizado correctamente.',
            'data' => $almacen,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->almacenFacade->eliminar($id);

        return response()->json([
            'message' => 'Almacén eliminado correctamente.',
        ]);
    }
}
