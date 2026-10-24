<?php

namespace App\Modules\Movimientos\Facades;

use App\Modules\Movimientos\Models\Motion;
use App\Modules\Movimientos\Services\MovimientoService;

class MovimientoFacade
{
    public function __construct(
        private MovimientoService $movimientoService
    ) {}

    public function listar()
    {
        return $this->movimientoService->listar();
    }

    public function mostrar(int $id): Motion
    {
        return $this->movimientoService->mostrar($id);
    }

    public function crear(array $datos): Motion
    {
        return $this->movimientoService->crear($datos);
    }

    public function actualizar(int $id, array $datos): Motion
    {
        return $this->movimientoService->actualizar($id, $datos);
    }

    public function eliminar(int $id): void
    {
        $this->movimientoService->eliminar($id);
    }
}
