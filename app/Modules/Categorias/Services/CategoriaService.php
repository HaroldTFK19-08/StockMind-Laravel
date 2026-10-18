<?php

namespace App\Modules\Categorias\Services;

use App\Modules\Categorias\Models\Category;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;

class CategoriaService
{
    public function listar(): Category
    {
        return Category::latest()->paginate(5);
    }
    public function mostrar(int $id): Category
    {
        $categoria = Category::with(
            [
                'elements' => function ($query) {
                    $query->select('id', 'nombre', 'category_id');
                }
            ]
        )->findOrFail($id);
        return $categoria;
    }
    public function registrar(array $datos): Category
    {
        return Category::create($datos);
    }
    public function actualizar(int $id, array $datos): Category
    {
        $categoria = Category::findOrFail($id);
        $categoria->update($datos);
        return $categoria->refresh();
    }
    public function eliminar(int $id): void
    {
        $categoria = Category::findOrFail($id);
        $categoria->delete();
    }
}
