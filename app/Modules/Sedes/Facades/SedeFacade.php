<?php

namespace App\Modules\Sedes\Facades;

use App\Modules\Sedes\Models\Headquarter;
use App\Modules\Sedes\Services\SedeService;

class SedeFacade
{
    public function __construct(
        private SedeService $sedeService
    ) {}

    public function listar()
    {
        return $this->sedeService->listar();
    }

    public function mostrar(int $id): Headquarter
    {
        return $this->sedeService->mostrar($id);
    }

    public function crear(array $datos): Headquarter
    {
        return $this->sedeService->crear($datos);
    }

    public function actualizar(int $id, array $datos): Headquarter
    {
        return $this->sedeService->actualizar($id, $datos);
    }

    public function eliminar(int $id): void
    {
        $this->sedeService->eliminar($id);
    }
}
