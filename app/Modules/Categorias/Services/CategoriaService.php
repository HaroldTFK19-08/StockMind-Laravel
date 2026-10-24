<?php

namespace App\Modules\Categorias\Services;

use App\Modules\Categorias\Models\Category;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;

class CategoriaService
{
    public function listar()
    {
        return Category::withCount('elements')
            ->latest()
            ->paginate(10);
    }

    public function mostrar(int $id): Category
    {
        return Category::withCount('elements')
            ->findOrFail($id);
    }

    public function crear(array $datos): Category
    {
        $categoria = Category::create($datos);

        return $this->mostrar($categoria->id);
    }

    public function actualizar(int $id, array $datos): Category
    {
        $categoria = $this->mostrar($id);
        $categoria->update($datos);

        return $this->mostrar($id);
    }

    public function eliminar(int $id): void
    {
        $categoria = $this->mostrar($id);

        if ($categoria->elements()->exists()) {
            throw new ReglasNegocioExceptions(
                'No se puede eliminar la categoría porque tiene elementos asociados.'
            );
        }

        $categoria->delete();
    }
}
