<?php

namespace App\Modules\Consultas\Services;

use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Consultas\Requests\ConsultaRequest;
use App\Modules\Prestamos\Models\Loan;
use App\Modules\Reportes\Models\Report;
use App\Modules\Usuarios\Models\Danteaccount;
use App\Modules\Usuarios\Models\Teacher;
use App\Modules\Usuarios\Models\User;

/**
 * Consultas de solo lectura del SRS/backlog (fichas, perfiles, solicitudes y reportes).
 *
 * Todas las consultas seleccionan columnas explícitas, limitan la página y
 * evitan N+1 con eager loading; ninguna consulta calcula stock.
 */
class ConsultaService
{
    /** Campos mínimos del usuario: se excluyen correo, teléfono y documento. */
    private const USUARIO_CAMPOS = 'id,nombre_1,nombre_2,apellido_1,apellido_2,estado';

    /** DSMR-05 / ASMR-25: instructores activos y su carga de fichas. */
    public function instructores(ConsultaRequest $request)
    {
        return Teacher::query()
            ->select(['id', 'user_id', 'tipo_instructor', 'especialidad'])
            ->with(['user:' . self::USUARIO_CAMPOS])
            ->withCount('coursegroups')
            ->whereHas(
                'user',
                fn ($query) => $query->where('estado', $request->input('estado', 'Activo'))
            )
            ->when(
                $request->filled('especialidad'),
                fn ($query) => $query->where('especialidad', 'like', '%' . $request->input('especialidad') . '%')
            )
            ->when(
                $request->filled('tipo_instructor'),
                fn ($query) => $query->where('tipo_instructor', $request->input('tipo_instructor'))
            )
            ->orderBy('id')
            ->paginate($this->porPagina($request));
    }

    /** ASMR-26 / ISMR-22: cuentas DANTE con vigencia resuelta en SQL. */
    public function cuentasDante(ConsultaRequest $request)
    {
        return Danteaccount::query()
            ->select(['id', 'user_id', 'environment_id', 'fecha_inicio', 'fecha_fin'])
            ->with([
                'user:' . self::USUARIO_CAMPOS,
                'environment:id,headquarter_id,nombre',
            ])
            ->when(
                $request->boolean('solo_vigentes', true),
                fn ($query) => $query->whereNull('fecha_fin')
            )
            ->when(
                $request->filled('ambiente_id'),
                fn ($query) => $query->where('environment_id', $request->input('ambiente_id'))
            )
            ->when(
                $request->filled('usuario_id'),
                fn ($query) => $query->where('user_id', $request->input('usuario_id'))
            )
            ->latest('fecha_inicio')
            ->paginate($this->porPagina($request));
    }

    /** ISMR-14/16/21: reportes de fallas, pérdidas y daños con filtros. */
    public function reportes(ConsultaRequest $request)
    {
        return Report::query()
            ->select(['id', 'apprentice_id', 'tipo', 'descripcion', 'fecha_reporte', 'estado'])
            ->with([
                'apprentice:id,user_id,coursegroup_id',
                'apprentice.user:' . self::USUARIO_CAMPOS,
            ])
            ->when(
                $request->filled('tipo'),
                fn ($query) => $query->where('tipo', $request->input('tipo'))
            )
            ->when(
                $request->filled('estado'),
                fn ($query) => $query->where('estado', $request->input('estado'))
            )
            ->when(
                $request->filled('aprendiz_id'),
                fn ($query) => $query->where('apprentice_id', $request->input('aprendiz_id'))
            )
            ->when(
                $request->filled('desde'),
                fn ($query) => $query->whereDate('fecha_reporte', '>=', $request->input('desde'))
            )
            ->when(
                $request->filled('hasta'),
                fn ($query) => $query->whereDate('fecha_reporte', '<=', $request->input('hasta'))
            )
            ->latest('fecha_reporte')
            ->paginate($this->porPagina($request));
    }

    /** ASMR-36/38: estado de las solicitudes propias del aprendiz o instructor. */
    public function misSolicitudes(ConsultaRequest $request, User $usuario): array
    {
        $porPagina = $this->porPagina($request);

        $asignaciones = Assignment::query()
            ->select([
                'id', 'element_id', 'apprentice_id', 'teacher_id',
                'fecha_asignacion', 'fecha_devolucion_esperada',
                'fecha_devolucion_real', 'estado',
            ])
            ->with([
                'element:id,codigo_inventario,nombre',
                'teacher.user:id,nombre_1,apellido_1',
            ])
            ->whereHas('apprentice', fn ($query) => $query->where('user_id', $usuario->id))
            ->when(
                $request->filled('estado'),
                fn ($query) => $query->where('estado', $request->input('estado'))
            )
            ->latest('fecha_asignacion')
            ->paginate($porPagina, ['*'], 'asignaciones_page');

        $prestamos = Loan::query()
            ->select([
                'id', 'element_id', 'teacher_id',
                'fecha_prestamo', 'fecha_devolucion_esperada',
                'fecha_devolucion_real', 'estado',
            ])
            ->with([
                'element:id,codigo_inventario,nombre',
                'teacher.user:id,nombre_1,apellido_1',
            ])
            ->whereHas('teacher', fn ($query) => $query->where('user_id', $usuario->id))
            ->when(
                $request->filled('estado'),
                fn ($query) => $query->where('estado', $request->input('estado'))
            )
            ->latest('fecha_prestamo')
            ->paginate($porPagina, ['*'], 'prestamos_page');

        return [
            'asignaciones' => $asignaciones,
            'prestamos' => $prestamos,
        ];
    }

    /** ISMR-16: solicitudes de los aprendices de una ficha (o de las fichas del instructor). */
    public function solicitudesFicha(ConsultaRequest $request, User $usuario)
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
                $request->filled('ficha_id'),
                // La ficha llega como filtro explícito: una sola subconsulta por id.
                fn ($query) => $query->whereHas(
                    'apprentice',
                    fn ($apprentice) => $apprentice->where('coursegroup_id', $request->input('ficha_id'))
                ),
                // Sin ficha, se usan todas las fichas del instructor en SQL (sin pluck).
                fn ($query) => $query->whereHas(
                    'apprentice.coursegroup.teacher',
                    fn ($teacher) => $teacher->where('user_id', $usuario->id)
                )
            )
            ->when(
                $request->filled('estado'),
                fn ($query) => $query->where('estado', $request->input('estado'))
            )
            ->latest('fecha_asignacion')
            ->paginate($this->porPagina($request));
    }

    /** Request validado en la capa HTTP: aquí sólo se aplica el límite seguro. */
    private function porPagina(ConsultaRequest $request): int
    {
        return (int) $request->input('per_page', 10);
    }
}
