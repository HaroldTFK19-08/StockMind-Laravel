<?php

namespace App\Modules\Movimientos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Movimientos\Facades\MovimientoFacade;
use App\Modules\Movimientos\Requests\ActualizarMovimientoRequest;
use App\Modules\Movimientos\Requests\RegistroMovimientoRequest;
use Illuminate\Http\JsonResponse;

class MovimientosController extends Controller
{
    use RespondeApi;

    public function __construct(
        private MovimientoFacade $movimientoFacade
    ) {}

    public function index(): JsonResponse
    {
        return $this->listado(
            $this->movimientoFacade->listar()
        );
    }

    public function store(RegistroMovimientoRequest $request): JsonResponse
    {
        $movimiento = $this->movimientoFacade->crear(
            $request->validated()
        );

        return $this->creado('Movimiento', $movimiento);
    }

    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->movimientoFacade->mostrar($id));
    }

    public function update(
        ActualizarMovimientoRequest $request,
        int $id
    ): JsonResponse {
        $movimiento = $this->movimientoFacade->actualizar(
            $id,
            $request->validated()
        );

        return $this->actualizado('Movimiento', $movimiento);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->movimientoFacade->eliminar($id);

        return $this->eliminado('Movimiento');
    }
}
