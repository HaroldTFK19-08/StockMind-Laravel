<?php

namespace App\Modules\Asignaciones\Facades;

use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Asignaciones\Services\AsignacionService;

class AsignacionFacade
{
    public function __construct(
        private AsignacionService $asignacionService
    ) {}

    public function listar()
    {
        return $this->asignacionService->listar();
    }

    public function mostrar(int $id): Assignment
    {
        return $this->asignacionService->mostrar($id);
    }

    public function crear(array $datos): Assignment
    {
        return $this->asignacionService->crear($datos);
    }

    public function actualizar(int $id, array $datos): Assignment
    {
        return $this->asignacionService->actualizar($id, $datos);
    }

    public function eliminar(int $id): void
    {
        $this->asignacionService->eliminar($id);
    }
}
