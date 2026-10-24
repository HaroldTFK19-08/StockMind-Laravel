<?php

namespace App\Modules\Roles\Facades;

use App\Modules\Roles\Models\Rol;
use App\Modules\Roles\Services\RolService;

class RolFacade
{
    public function __construct(
        private RolService $rolService
    ) {}

    public function listar()
    {
        return $this->rolService->listar();
    }
    public function mostrar(int $id): Rol
    {
        return $this->rolService->mostrar($id);
    }
    public function crear(array $datos): Rol
    {
        return $this->rolService->crear($datos);
    }
    public function actualizar(int $id, array $datos): Rol
    {
        return $this->rolService->actualizar($id, $datos);
    }
    public function eliminar(int $id): void
    {
        $this->rolService->eliminar($id);
    }
}
