<?php

namespace App\Modules\Almacenes\Facades;

use App\Modules\Almacenes\Models\Warehouse;
use App\Modules\Almacenes\Services\AlmacenService;

class AlmacenFacade
{
    public function __construct(
        private AlmacenService $almacenService
    ) {}

    public function listar()
    {
        return $this->almacenService->listar();
    }

    public function mostrar(int $id): Warehouse
    {
        return $this->almacenService->mostrar($id);
    }

    public function crear(array $datos): Warehouse
    {
        return $this->almacenService->crear($datos);
    }

    public function actualizar(int $id, array $datos): Warehouse
    {
        return $this->almacenService->actualizar($id, $datos);
    }

    public function eliminar(int $id): void
    {
        $this->almacenService->eliminar($id);
    }
}
