<?php

namespace App\Modules\Ambientes\Services;

use App\Modules\Ambientes\Models\Environment;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use App\Modules\Shared\Services\CacheService;
use App\Modules\Usuarios\Models\Danteaccount;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

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
            fn () => Environment::with([
                'headquarter',
                'teacher.user:id,nombre_1,nombre_2,apellido_1,apellido_2,estado',
                'danteaccounts' => fn ($query) => $query
                    ->whereNull('fecha_fin')
                    ->select(['id', 'user_id', 'environment_id', 'fecha_inicio', 'fecha_fin'])
                    ->with('user:id,nombre_1,nombre_2,apellido_1,apellido_2,estado'),
            ])
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
        return Environment::with([
            'headquarter',
            'teacher.user:id,nombre_1,nombre_2,apellido_1,apellido_2,estado',
            'danteaccounts' => fn ($query) => $query
                ->whereNull('fecha_fin')
                ->select(['id', 'user_id', 'environment_id', 'fecha_inicio', 'fecha_fin'])
                ->with('user:id,nombre_1,nombre_2,apellido_1,apellido_2,estado'),
        ])
            ->withCount([
                'elements',
                'danteaccounts as danteaccounts_count' => fn ($query) => $query
                    ->whereNull('fecha_fin'),
            ])
            ->findOrFail($id);
    }

    public function crear(array $datos): Environment
    {
        $cuentadanteIds = $this->extraerCuentadanteIds($datos);

        $ambiente = DB::transaction(function () use ($datos, $cuentadanteIds) {
            $ambiente = Environment::create($datos);

            if ($cuentadanteIds !== null) {
                $this->sincronizarCuentadantes($ambiente, $cuentadanteIds);
            }

            return $ambiente;
        });

        $this->cache->invalidar('ambientes');

        return $this->buscar($ambiente->id);
    }

    public function actualizar(int $id, array $datos): Environment
    {
        $cuentadanteIds = $this->extraerCuentadanteIds($datos);
        $ambiente = $this->buscar($id);
        $capacidad = $datos['capacidad'] ?? null;

        $cuentadantesActivos = $cuentadanteIds !== null
            ? count($cuentadanteIds)
            : $ambiente->danteaccounts()->whereNull('fecha_fin')->count();

        if ($capacidad !== null && $capacidad < $cuentadantesActivos) {
            throw new ReglasNegocioExceptions(
                'La capacidad no puede ser menor que la cantidad de cuentadantes actualmente asignados.'
            );
        }

        DB::transaction(function () use ($ambiente, $datos, $cuentadanteIds): void {
            $ambiente->update($datos);

            if ($cuentadanteIds !== null) {
                $this->sincronizarCuentadantes($ambiente, $cuentadanteIds);
            }
        });

        $this->cache->invalidar('ambientes');

        return $this->buscar($id);
    }

    /**
     * @param  array<string, mixed>  $datos
     * @return array<int, int>|null
     */
    private function extraerCuentadanteIds(array &$datos): ?array
    {
        $presente = array_key_exists('cuentadante_ids', $datos) || array_key_exists('cuentadante_id', $datos);
        $ids = $datos['cuentadante_ids'] ?? $datos['cuentadante_id'] ?? null;

        unset($datos['cuentadante_ids'], $datos['cuentadante_id']);

        if (! $presente) {
            return null;
        }

        if ($ids === null || $ids === '') {
            return [];
        }

        if (! is_array($ids)) {
            $ids = [$ids];
        }

        return array_values(array_unique(array_map('intval', array_filter($ids, fn ($id) => $id !== null && $id !== ''))));
    }

    /**
     * @param  array<int, int>  $cuentadanteIds
     */
    private function sincronizarCuentadantes(Environment $ambiente, array $cuentadanteIds): void
    {
        $hoy = Carbon::today()->toDateString();
        $activos = Danteaccount::query()
            ->where('environment_id', $ambiente->id)
            ->whereNull('fecha_fin')
            ->get(['id', 'user_id']);

        $activosPorUsuario = $activos->groupBy('user_id');

        foreach ($activosPorUsuario as $userId => $cuentas) {
            if (! in_array((int) $userId, $cuentadanteIds, true)) {
                Danteaccount::query()
                    ->whereIn('id', $cuentas->pluck('id')->all())
                    ->update(['fecha_fin' => $hoy]);
            }
        }

        foreach ($cuentadanteIds as $userId) {
            if ($activosPorUsuario->has($userId)) {
                continue;
            }

            Danteaccount::create([
                'user_id' => $userId,
                'environment_id' => $ambiente->id,
                'fecha_inicio' => $hoy,
                'fecha_fin' => null,
            ]);
        }
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
