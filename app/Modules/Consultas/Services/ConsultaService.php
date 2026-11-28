<?php

namespace App\Modules\Consultas\Services;

use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Consultas\Requests\ConsultaRequest;
use App\Modules\Prestamos\Models\Loan;
use App\Modules\Reportes\Models\Report;
use App\Modules\Shared\Services\CacheService;
use App\Modules\Usuarios\Models\Danteaccount;
use App\Modules\Usuarios\Models\Teacher;
use App\Modules\Usuarios\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;

/**
 * Consultas de solo lectura del SRS/backlog (fichas, perfiles, solicitudes y reportes).
 *
 * Todas las consultas seleccionan columnas explícitas, limitan la página y
 * evitan N+1 con eager loading; ninguna consulta calcula stock.
 */
class ConsultaService
{
    public function __construct(
        private CacheService $cache
    ) {}

    /** Campos mínimos del usuario: se excluyen correo, teléfono y documento. */
    private const USUARIO_CAMPOS = 'id,nombre_1,nombre_2,apellido_1,apellido_2,estado';

    /** DSMR-05 / ASMR-25: instructores activos y su carga de fichas. */
    public function instructores(ConsultaRequest $request)
    {
        $porPagina = $this->porPagina($request);
        $filtros = $request->only(['estado', 'especialidad', 'tipo_instructor']);

        return $this->cache->recordarPagina(
            'consultas',
            CacheService::TTL_CONSULTA,
            $porPagina,
            fn () => $this->queryInstructores($filtros),
            $this->claveConsulta('instructores', $filtros)
        );
    }

    private function queryInstructores(array $filtros)
    {
        $estado = $filtros['estado'] ?? 'Activo';

        return Teacher::query()
            ->select(['id', 'user_id', 'tipo_instructor', 'especialidad'])
            ->with(['user:' . self::USUARIO_CAMPOS])
            ->withCount('coursegroups')
            ->whereHas('user', fn ($query) => $query->where('estado', $estado))
            ->when(
                ! empty($filtros['especialidad']),
                fn ($query) => $query->where('especialidad', 'like', '%' . $filtros['especialidad'] . '%')
            )
            ->when(
                ! empty($filtros['tipo_instructor']),
                fn ($query) => $query->where('tipo_instructor', $filtros['tipo_instructor'])
            )
            ->orderBy('id');
    }

    /** ASMR-26 / ISMR-22: cuentas DANTE con vigencia resuelta en SQL. */
    public function cuentasDante(ConsultaRequest $request)
    {
        $porPagina = $this->porPagina($request);
        $filtros = $request->only(['solo_vigentes', 'ambiente_id', 'usuario_id']);
        $filtros['solo_vigentes'] = $request->boolean('solo_vigentes', true);

        return $this->cache->recordarPagina(
            'consultas',
            CacheService::TTL_CONSULTA,
            $porPagina,
            fn () => $this->queryCuentasDante($filtros),
            $this->claveConsulta('cuentas_dante', $filtros)
        );
    }

    private function queryCuentasDante(array $filtros)
    {
        return Danteaccount::query()
            ->select(['id', 'user_id', 'environment_id', 'fecha_inicio', 'fecha_fin'])
            ->with([
                'user:' . self::USUARIO_CAMPOS,
                'environment:id,headquarter_id,nombre',
            ])
            ->when(
                $filtros['solo_vigentes'] ?? true,
                fn ($query) => $query->whereNull('fecha_fin')
            )
            ->when(
                ! empty($filtros['ambiente_id']),
                fn ($query) => $query->where('environment_id', $filtros['ambiente_id'])
            )
            ->when(
                ! empty($filtros['usuario_id']),
                fn ($query) => $query->where('user_id', $filtros['usuario_id'])
            )
            ->latest('fecha_inicio');
    }

    /** ISMR-14/16/21: reportes de fallas, pérdidas y daños con filtros. */
    public function reportes(ConsultaRequest $request)
    {
        $porPagina = $this->porPagina($request);
        $filtros = $request->only(['tipo', 'estado', 'aprendiz_id', 'desde', 'hasta']);

        return $this->cache->recordarPagina(
            'consultas',
            CacheService::TTL_CONSULTA,
            $porPagina,
            fn () => $this->queryReportes($filtros),
            $this->claveConsulta('reportes', $filtros)
        );
    }

    private function queryReportes(array $filtros)
    {
        return Report::query()
            ->select(['id', 'apprentice_id', 'tipo', 'descripcion', 'fecha_reporte', 'estado'])
            ->with([
                'apprentice:id,user_id,coursegroup_id',
                'apprentice.user:' . self::USUARIO_CAMPOS,
            ])
            ->when(
                ! empty($filtros['tipo']),
                fn ($query) => $query->where('tipo', $filtros['tipo'])
            )
            ->when(
                ! empty($filtros['estado']),
                fn ($query) => $query->where('estado', $filtros['estado'])
            )
            ->when(
                ! empty($filtros['aprendiz_id']),
                fn ($query) => $query->where('apprentice_id', $filtros['aprendiz_id'])
            )
            ->when(
                ! empty($filtros['desde']),
                fn ($query) => $query->whereDate('fecha_reporte', '>=', $filtros['desde'])
            )
            ->when(
                ! empty($filtros['hasta']),
                fn ($query) => $query->whereDate('fecha_reporte', '<=', $filtros['hasta'])
            )
            ->latest('fecha_reporte');
    }

    /** ASMR-36/38: estado de las solicitudes propias del aprendiz o instructor. */
    public function misSolicitudes(ConsultaRequest $request, User $usuario): array
    {
        $porPagina = $this->porPagina($request);
        $filtros = $request->only(['estado']);
        $filtros['usuario_id'] = $usuario->id;

        $asignaciones = $this->cache->recordarPagina(
            'consultas',
            CacheService::TTL_CONSULTA,
            $porPagina,
            fn () => $this->queryMisAsignaciones($usuario->id, $filtros),
            $this->claveConsulta('mis_asignaciones', $filtros),
            'asignaciones_page'
        );

        $prestamos = $this->cache->recordarPagina(
            'consultas',
            CacheService::TTL_CONSULTA,
            $porPagina,
            fn () => $this->queryMisPrestamos($usuario->id, $filtros),
            $this->claveConsulta('mis_prestamos', $filtros),
            'prestamos_page'
        );

        return [
            'asignaciones' => $asignaciones,
            'prestamos' => $prestamos,
        ];
    }

    private function queryMisAsignaciones(int $usuarioId, array $filtros)
    {
        return Assignment::query()
            ->select([
                'id', 'element_id', 'apprentice_id', 'teacher_id',
                'fecha_asignacion', 'fecha_devolucion_esperada',
                'fecha_devolucion_real', 'estado',
            ])
            ->with([
                'element:id,codigo_inventario,nombre',
                'teacher.user:id,nombre_1,apellido_1',
            ])
            ->whereHas('apprentice', fn ($query) => $query->where('user_id', $usuarioId))
            ->when(
                ! empty($filtros['estado']),
                fn ($query) => $query->where('estado', $filtros['estado'])
            )
            ->latest('fecha_asignacion');
    }

    private function queryMisPrestamos(int $usuarioId, array $filtros)
    {
        return Loan::query()
            ->select([
                'id', 'element_id', 'teacher_id',
                'fecha_prestamo', 'fecha_devolucion_esperada',
                'fecha_devolucion_real', 'estado',
            ])
            ->with([
                'element:id,codigo_inventario,nombre',
                'teacher.user:id,nombre_1,apellido_1',
            ])
            ->whereHas('teacher', fn ($query) => $query->where('user_id', $usuarioId))
            ->when(
                ! empty($filtros['estado']),
                fn ($query) => $query->where('estado', $filtros['estado'])
            )
            ->latest('fecha_prestamo');
    }

    /** ISMR-16: solicitudes de los aprendices de una ficha (o de las fichas del instructor). */
    public function solicitudesFicha(ConsultaRequest $request, User $usuario)
    {
        $porPagina = $this->porPagina($request);
        $filtros = $request->only(['ficha_id', 'estado']);
        $filtros['usuario_id'] = $usuario->id;

        return $this->cache->recordarPagina(
            'consultas',
            CacheService::TTL_CONSULTA,
            $porPagina,
            fn () => $this->querySolicitudesFicha($usuario->id, $filtros),
            $this->claveConsulta('solicitudes_ficha', $filtros)
        );
    }

    private function querySolicitudesFicha(int $usuarioId, array $filtros)
    {
        return Assignment::query()
            ->select([
                'id', 'element_id', 'apprentice_id', 'teacher_id',
                'fecha_asignacion', 'fecha_devolucion_esperada',
                'fecha_devolucion_real', 'estado',
            ])
            ->with([
                'apprentice:id,user_id,coursegroup_id',
                'apprentice.user:' . self::USUARIO_CAMPOS,
                'teacher.user:id,nombre_1,apellido_1',
            ])
            ->when(
                ! empty($filtros['ficha_id']),
                // La ficha llega como filtro explícito: una sola subconsulta por id.
                fn ($query) => $query->whereHas(
                    'apprentice',
                    fn ($apprentice) => $apprentice->where('coursegroup_id', $filtros['ficha_id'])
                ),
                // Sin ficha, se usan todas las fichas del instructor en SQL (sin pluck).
                fn ($query) => $query->whereHas(
                    'apprentice.coursegroup.teacher',
                    fn ($teacher) => $teacher->where('user_id', $usuarioId)
                )
            )
            ->when(
                ! empty($filtros['estado']),
                fn ($query) => $query->where('estado', $filtros['estado'])
            )
            ->latest('fecha_asignacion');
    }

    /** Request validado en la capa HTTP: aquí sólo se aplica el límite seguro. */
    private function porPagina(ConsultaRequest $request): int
    {
        return (int) $request->input('per_page', 10);
    }

    /**
     * Clave corta y determinista para separar cachés por filtros.
     * Incluye usuario y nombre de página cuando aplica, para evitar colisiones.
     */
    private function claveConsulta(string $prefijo, array $filtros, string $pageName = 'page'): string
    {
        $normalizados = [];
        ksort($filtros);

        foreach ($filtros as $key => $value) {
            if ($value === null || $value === '') {
                continue;
            }
            $normalizados[$key] = is_bool($value) ? ($value ? '1' : '0') : $value;
        }

        $hash = substr(sha1(json_encode($normalizados)), 0, 16);

        return "{$prefijo}:{$pageName}:{$hash}";
    }
}
