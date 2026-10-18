<?php

namespace App\Modules\Centros\Facades;

use App\Modules\Centros\Models\Trainingcenter;
use App\Modules\Centros\Services\CentroService;

class CentroFacade
{
    public function __construct(
        private CentroService $centroService
    ) {}

    public function listar()
    {
        return $this->centroService->listar();
    }
    public function mostrar(int $id): Trainingcenter
    {
        return $this->centroService->mostrar($id);
    }
    public function crear(array $datos): Trainingcenter
    {
        return $this->centroService->crear($datos);
    }
    public function actualizar(int $id, array $datos): Trainingcenter
    {
        return $this->centroService->actualizar($id, $datos);
    }
    public function eliminar(int $id): void
    {
        $this->centroService->eliminar($id);
    }
}
