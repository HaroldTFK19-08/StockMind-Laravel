<?php

namespace App\Modules\Categorias\Facades;

use App\Modules\Categorias\Models\Category;
use App\Modules\Categorias\Services\CategoriaService;

class CategoriaFacade
{
    public function __construct(
        private CategoriaService $categoriaService
    ) {}

    public function listar()
    {
        return $this->categoriaService->listar();
    }

    public function mostrar(int $id): Category
    {
        return $this->categoriaService->mostrar($id);
    }

    public function crear(array $datos): Category
    {
        return $this->categoriaService->crear($datos);
    }

    public function actualizar(int $id, array $datos): Category
    {
        return $this->categoriaService->actualizar($id, $datos);
    }

    public function eliminar(int $id): void
    {
        $this->categoriaService->eliminar($id);
    }
}
