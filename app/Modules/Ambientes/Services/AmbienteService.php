<?php

namespace App\Modules\Ambientes\Services;

use App\Modules\Ambientes\Models\Environment;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use App\Modules\Shared\Services\CacheService;

class AmbienteService
{
    public function __construct(
        private CacheService $cache
    ) {}

    // Listado paginado: las primeras páginas salen de Redis
    public function listar()
    {
        return $this->cache->recordarPagina(
            'ambientes',
            CacheService::TTL_CATALOGO,
            10,
            fn () => Environment::with(['headquarter', 'teacher'])
                ->withCount([
                    'elements',
                    'danteaccounts as danteaccounts_count' => fn ($query) => $query
                        ->whereNull('fecha_fin'),
                ])
                ->latest()
        );
    }

    // Detalle para lectura (GET): sale de Redis como array
    public function mostrar(int $id): array
    {
        return $this->cache->recordar(
            'ambientes',
            "detalle:{$id}",
            CacheService::TTL_CATALOGO,
            fn () => $this->buscar($id)->toArray()
        );
    }

    // Consulta directa a MySQL: la usan crear, actualizar y eliminar
    // para trabajar siempre con datos frescos
    private function buscar(int $id): Environment
    {
        return Environment::with(['headquarter', 'teacher'])
            ->withCount([
                'elements',
                'danteaccounts as danteaccounts_count' => fn ($query) => $query
                    ->whereNull('fecha_fin'),
            ])
            ->findOrFail($id);
    }

    public function crear(array $datos): Environment
    {
        $ambiente = Environment::create($datos);

        $this->cache->invalidar('ambientes');

        return $this->buscar($ambiente->id);
    }

    public function actualizar(int $id, array $datos): Environment
    {
        $ambiente = $this->buscar($id);
        $capacidad = $datos['capacidad'] ?? null;

        if (
            $capacidad !== null
            && $capacidad < $ambiente->danteaccounts()
                ->whereNull('fecha_fin')
                ->count()
        ) {
            throw new ReglasNegocioExceptions(
                'La capacidad no puede ser menor que la cantidad de aprendices actualmente asignados.'
            );
        }

        $ambiente->update($datos);

        $this->cache->invalidar('ambientes');

        return $this->buscar($id);
    }

    public function eliminar(int $id): void
    {
        $ambiente = $this->buscar($id);

        if (
            $ambiente->elements()->exists()
            || $ambiente->danteaccounts()->exists()
            || $ambiente->outgoingTransfers()->exists()
            || $ambiente->incomingTransfers()->exists()
        ) {
            throw new ReglasNegocioExceptions(
                'No se puede eliminar el ambiente porque tiene elementos, cuentas o traslados asociados.'
            );
        }

        $ambiente->delete();

        $this->cache->invalidar('ambientes');
    }
}
