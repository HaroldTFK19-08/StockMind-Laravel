<?php

namespace App\Modules\Categorias\Facades;

use App\Modules\Categorias\Services\CategoriaService;
use App\Modules\Categorias\Models\Category;

class CategoriaFacade
{
    public function __construct(
        private CategoriaService $centroService
    ) {}
    public function listar()
    {
        return $this->centroService->listar();
    }
    public function mostrar(int $id): Category
    {
        return $this->centroService->mostrar($id);
    }
    public function crear(array $datos): Category
    {
        return $this->centroService->registrar($datos);
    }
    public function actualizar(int $id, array $datos): Category
    {
        return $this->centroService->actualizar($id, $datos);
    }
    public function eliminar(int $id): void
    {
        $this->centroService->eliminar($id);
    }
}
