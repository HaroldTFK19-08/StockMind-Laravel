<?php

namespace App\Modules\Almacenes\Services;

use App\Modules\Almacenes\Models\Warehouse;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use App\Modules\Shared\Services\CacheService;

class AlmacenService
{
    public function __construct(
        private CacheService $cache
    ) {}

    // Listado paginado: las primeras páginas salen de Redis
    public function listar()
    {
        return $this->cache->recordarPagina(
            'almacenes',
            CacheService::TTL_CATALOGO,
            10,
            fn () => Warehouse::with('headquarter')
                ->withCount('elements')
                ->latest()
        );
    }

    // Detalle para lectura (GET): sale de Redis como array
    public function mostrar(int $id): array
    {
        return $this->cache->recordar(
            'almacenes',
            "detalle:{$id}",
            CacheService::TTL_CATALOGO,
            fn () => $this->buscar($id)->toArray()
        );
    }

    // Consulta directa a MySQL: la usan crear, actualizar y eliminar
    // para trabajar siempre con datos frescos
    private function buscar(int $id): Warehouse
    {
        return Warehouse::with('headquarter')
            ->withCount('elements')
            ->findOrFail($id);
    }

    public function crear(array $datos): Warehouse
    {
        $almacen = Warehouse::create($datos);

        $this->cache->invalidar('almacenes');

        return $this->buscar($almacen->id);
    }

    public function actualizar(int $id, array $datos): Warehouse
    {
        $almacen = $this->buscar($id);
        $capacidad = $datos['capacidad_elementos'] ?? null;

        if ($capacidad !== null && $capacidad < $almacen->elements()->count()) {
            throw new ReglasNegocioExceptions(
                'La capacidad no puede ser menor que la cantidad de elementos almacenados.'
            );
        }

        $almacen->update($datos);

        $this->cache->invalidar('almacenes');

        return $this->buscar($id);
    }

    public function eliminar(int $id): void
    {
        $almacen = $this->buscar($id);

        if ($almacen->elements()->exists()) {
            throw new ReglasNegocioExceptions(
                'No se puede eliminar el almacén porque tiene elementos asociados.'
            );
        }

        $almacen->delete();

        $this->cache->invalidar('almacenes');
    }
}
