<?php

namespace App\Modules\Fichas\Facades;

use App\Modules\Fichas\Models\Coursegroup;
use App\Modules\Fichas\Services\FichaService;

class FichaFacade
{
    public function __construct(
        private FichaService $fichaService
    ) {}

    public function listar()
    {
        return $this->fichaService->listar();
    }

    public function mostrar(int $id): Coursegroup
    {
        return $this->fichaService->mostrar($id);
    }

    public function crear(array $datos): Coursegroup
    {
        return $this->fichaService->crear($datos);
    }

    public function actualizar(int $id, array $datos): Coursegroup
    {
        return $this->fichaService->actualizar($id, $datos);
    }

    public function eliminar(int $id): void
    {
        $this->fichaService->eliminar($id);
    }
}
