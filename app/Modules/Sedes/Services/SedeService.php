<?php

namespace App\Modules\Sedes\Services;

use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use App\Modules\Sedes\Models\Headquarter;
use App\Modules\Shared\Services\CacheService;

class SedeService
{
    public function __construct(
        private CacheService $cache
    ) {}

    // Listado paginado: las primeras páginas salen de Redis
    public function listar()
    {
        return $this->cache->recordarPagina(
            'sedes',
            CacheService::TTL_CATALOGO,
            10,
            fn () => Headquarter::with('trainingcenter')
                ->withCount(['warehouses', 'environments'])
                ->latest()
        );
    }

    // Detalle para lectura (GET): sale de Redis como array
    public function mostrar(int $id): array
    {
        return $this->cache->recordar(
            'sedes',
            "detalle:{$id}",
            CacheService::TTL_CATALOGO,
            fn () => $this->buscar($id)->toArray()
        );
    }

    // Consulta directa a MySQL: la usan crear, actualizar y eliminar
    // para trabajar siempre con datos frescos
    private function buscar(int $id): Headquarter
    {
        return Headquarter::with('trainingcenter')
            ->withCount(['warehouses', 'environments'])
            ->findOrFail($id);
    }

    public function crear(array $datos): Headquarter
    {
        $sede = Headquarter::create($datos);

        $this->cache->invalidar('sedes');

        return $this->buscar($sede->id);
    }

    public function actualizar(int $id, array $datos): Headquarter
    {
        $sede = $this->buscar($id);
        $sede->update($datos);

        $this->cache->invalidar('sedes');

        return $this->buscar($id);
    }

    public function eliminar(int $id): void
    {
        $sede = $this->buscar($id);

        if ($sede->warehouses()->exists() || $sede->environments()->exists()) {
            throw new ReglasNegocioExceptions(
                'No se puede eliminar la sede porque tiene almacenes o ambientes asociados.'
            );
        }

        $sede->delete();

        $this->cache->invalidar('sedes');
    }
}
