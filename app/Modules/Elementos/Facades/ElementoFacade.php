<?php

namespace App\Modules\Elementos\Facades;

use App\Modules\Elementos\Models\Element;
use App\Modules\Elementos\Services\ElementoService;

class ElementoFacade
{
    public function __construct(
        private ElementoService $elementoService
    ) {}

    public function listar()
    {
        return $this->elementoService->listar();
    }

    public function mostrar(int $id): Element
    {
        return $this->elementoService->mostrar($id);
    }

    public function crear(array $datos): Element
    {
        return $this->elementoService->crear($datos);
    }

    public function actualizar(int $id, array $datos): Element
    {
        return $this->elementoService->actualizar($id, $datos);
    }

    public function eliminar(int $id): void
    {
        $this->elementoService->eliminar($id);
    }
}
