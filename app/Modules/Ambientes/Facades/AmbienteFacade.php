<?php

namespace App\Modules\Ambientes\Facades;

use App\Modules\Ambientes\Models\Environment;
use App\Modules\Ambientes\Services\AmbienteService;

class AmbienteFacade
{
    public function __construct(
        private AmbienteService $ambienteService
    ) {}

    public function listar()
    {
        return $this->ambienteService->listar();
    }

    public function mostrar(int $id): Environment
    {
        return $this->ambienteService->mostrar($id);
    }

    public function crear(array $datos): Environment
    {
        return $this->ambienteService->crear($datos);
    }

    public function actualizar(int $id, array $datos): Environment
    {
        return $this->ambienteService->actualizar($id, $datos);
    }

    public function eliminar(int $id): void
    {
        $this->ambienteService->eliminar($id);
    }
}
