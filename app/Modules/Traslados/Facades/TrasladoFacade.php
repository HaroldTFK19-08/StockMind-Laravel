<?php

namespace App\Modules\Traslados\Facades;

use App\Modules\Traslados\Models\Transfer;
use App\Modules\Traslados\Services\TrasladoService;

class TrasladoFacade
{
    public function __construct(
        private TrasladoService $trasladoService
    ) {}

    public function listar()
    {
        return $this->trasladoService->listar();
    }

    public function mostrar(int $id): Transfer
    {
        return $this->trasladoService->mostrar($id);
    }

    public function crear(array $datos): Transfer
    {
        return $this->trasladoService->crear($datos);
    }

    public function actualizar(int $id, array $datos): Transfer
    {
        return $this->trasladoService->actualizar($id, $datos);
    }

    public function eliminar(int $id): void
    {
        $this->trasladoService->eliminar($id);
    }
}
