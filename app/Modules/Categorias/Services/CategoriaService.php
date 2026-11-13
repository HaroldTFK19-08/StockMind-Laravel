<?php

namespace App\Modules\Categorias\Services;

use App\Modules\Categorias\Models\Category;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use App\Modules\Shared\Services\CacheService;

class CategoriaService
{
    public function __construct(
        private CacheService $cache
    ) {}

    // Listado paginado: las primeras páginas salen de Redis
    public function listar()
    {
        return $this->cache->recordarPagina(
            'categorias',
            CacheService::TTL_CATALOGO,
            10,
            fn () => Category::withCount('elements')
                ->latest()
        );
    }

    // Detalle para lectura (GET): sale de Redis como array
    public function mostrar(int $id): array
    {
        return $this->cache->recordar(
            'categorias',
            "detalle:{$id}",
            CacheService::TTL_CATALOGO,
            fn () => $this->buscar($id)->toArray()
        );
    }

    // Consulta directa a MySQL: la usan crear, actualizar y eliminar
    // para trabajar siempre con datos frescos
    private function buscar(int $id): Category
    {
        return Category::with([
            'elements' => function ($query) {
                $query->select('id', 'nombre', 'category_id');
            }
        ])
            ->withCount('elements')
            ->findOrFail($id);
    }

    public function crear(array $datos): Category
    {
        $categoria = Category::create($datos);

        $this->cache->invalidar('categorias');

        return $this->buscar($categoria->id);
    }

    public function actualizar(int $id, array $datos): Category
    {
        $categoria = $this->buscar($id);
        $categoria->update($datos);

        $this->cache->invalidar('categorias');

        return $this->buscar($id);
    }

    public function eliminar(int $id): void
    {
        $categoria = $this->buscar($id);

        if ($categoria->elements()->exists()) {
            throw new ReglasNegocioExceptions(
                'No se puede eliminar la categoría porque tiene elementos asociados.'
            );
        }
        $categoria->delete();

        $this->cache->invalidar('categorias');
    }
}
