<?php

namespace App\Modules\Prestamos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Prestamos\Facades\PrestamoFacade;
use App\Modules\Prestamos\Requests\ActualizarPrestamoRequest;
use App\Modules\Prestamos\Requests\RegistroPrestamoRequest;
use Illuminate\Http\JsonResponse;

class PrestamosController extends Controller
{
    public function __construct(
        private PrestamoFacade $prestamoFacade
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->prestamoFacade->listar()
        );
    }

    public function store(RegistroPrestamoRequest $request): JsonResponse
    {
        $prestamo = $this->prestamoFacade->crear(
            $request->validated()
        );

        return response()->json([
            'message' => 'Préstamo registrado correctamente.',
            'data' => $prestamo,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->prestamoFacade->mostrar($id),
        ]);
    }

    public function update(
        ActualizarPrestamoRequest $request,
        int $id
    ): JsonResponse {
        $prestamo = $this->prestamoFacade->actualizar(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Préstamo actualizado correctamente.',
            'data' => $prestamo,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->prestamoFacade->eliminar($id);

        return response()->json([
            'message' => 'Préstamo eliminado correctamente.',
        ]);
    }
}
