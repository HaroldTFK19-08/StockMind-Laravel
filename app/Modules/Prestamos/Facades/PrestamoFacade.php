<?php

namespace App\Modules\Prestamos\Facades;

use App\Modules\Prestamos\Models\Loan;
use App\Modules\Prestamos\Services\PrestamoService;

class PrestamoFacade
{
    public function __construct(
        private PrestamoService $prestamoService
    ) {}

    public function listar()
    {
        return $this->prestamoService->listar();
    }

    public function mostrar(int $id): Loan
    {
        return $this->prestamoService->mostrar($id);
    }

    public function crear(array $datos): Loan
    {
        return $this->prestamoService->crear($datos);
    }

    public function actualizar(int $id, array $datos): Loan
    {
        return $this->prestamoService->actualizar($id, $datos);
    }

    public function eliminar(int $id): void
    {
        $this->prestamoService->eliminar($id);
    }
}
