<?php

namespace App\Modules\Movimientos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Movimientos\Facades\MovimientoFacade;
use App\Modules\Movimientos\Requests\ActualizarMovimientoRequest;
use App\Modules\Movimientos\Requests\RegistroMovimientoRequest;
use Illuminate\Http\JsonResponse;

class MovimientosController extends Controller
{
    public function __construct(
        private MovimientoFacade $movimientoFacade
    ) {}

    public function index(): JsonResponse
    {
        return response()->json(
            $this->movimientoFacade->listar()
        );
    }

    public function store(RegistroMovimientoRequest $request): JsonResponse
    {
        $movimiento = $this->movimientoFacade->crear(
            $request->validated()
        );

        return response()->json([
            'message' => 'Movimiento registrado correctamente.',
            'data' => $movimiento,
        ], 201);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => $this->movimientoFacade->mostrar($id),
        ]);
    }

    public function update(
        ActualizarMovimientoRequest $request,
        int $id
    ): JsonResponse {
        $movimiento = $this->movimientoFacade->actualizar(
            $id,
            $request->validated()
        );

        return response()->json([
            'message' => 'Movimiento actualizado correctamente.',
            'data' => $movimiento,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->movimientoFacade->eliminar($id);

        return response()->json([
            'message' => 'Movimiento eliminado correctamente.',
        ]);
    }
}
