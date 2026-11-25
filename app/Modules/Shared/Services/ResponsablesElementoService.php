<?php

namespace App\Modules\Shared\Services;

use App\Modules\Elementos\Models\Element;
use App\Modules\Usuarios\Models\User;
use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

/**
 * Resuelve, para los documentos PDF, quién responde por cada elemento y
 * cómo ha sido usado (asignaciones, préstamos, movimientos y reportes).
 */
class ResponsablesElementoService
{
    private const ESTADOS_ASIGNACION_ACTIVA = ['activa', 'vencida'];

    private const ESTADOS_PRESTAMO_ACTIVO = ['activo', 'vencido'];

    /**
     * Relaciones que deben cargarse antes de generar una ficha de elemento.
     */
    public static function relacionesParaPdf(): array
    {
        return [
            'category',
            'environment',
            'environment.teacher.user',
            'environment.danteaccounts' => static fn ($query) => $query
                ->whereNull('fecha_fin')
                ->with('user'),
            'warehouse',
            'assignments' => static fn ($query) => $query
                ->with('apprentice.user')
                ->latest('fecha_asignacion'),
            'loans' => static fn ($query) => $query
                ->with('teacher.user')
                ->latest('fecha_prestamo'),
            'motions',
            'reports',
        ];
    }

    /**
     * @param  iterable<int, Element>  $elementos
     * @return array<int, array<string, mixed>>
     */
    public function preparar(iterable $elementos): array
    {
        return collect($elementos)
            ->map(fn (Element $elemento) => $this->ficha($elemento))
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public function ficha(Element $elemento): array
    {
        $asignaciones = $this->relacion($elemento, 'assignments');
        $prestamos = $this->relacion($elemento, 'loans');
        $movimientos = $this->relacion($elemento, 'motions');
        $reportes = $this->relacion($elemento, 'reports');
        $cuentas = $elemento->environment !== null
            ? $this->relacion($elemento->environment, 'danteaccounts')
            : collect();

        $responsable = $this->responsableActual(
            $elemento,
            $asignaciones,
            $prestamos,
            $cuentas
        );

        $uso = $this->uso(
            $asignaciones,
            $prestamos,
            $movimientos,
            $reportes
        );

        return [
            'id' => $elemento->id,
            'codigo' => $elemento->codigo_inventario,
            'nombre' => $elemento->nombre,
            'descripcion' => $elemento->descripcion,
            'serial' => $elemento->serial,
            'marca' => $elemento->marca,
            'modelo' => $elemento->modelo,
            'categoria' => $elemento->category?->nombre ?? 'Sin categoría',
            'ubicacion' => $elemento->environment?->nombre
                ?? $elemento->warehouse?->nombre
                ?? 'Sin ubicación registrada',
            'estado' => $elemento->estado,
            'condicion' => $elemento->condicion,
            'responsable' => $responsable['nombre'],
            'tipo_responsable' => $responsable['tipo'],
            'uso' => $uso,
            'resumen_uso' => $this->resumenUso($uso),
        ];
    }

    public static function nombreCompleto(?User $usuario): ?string
    {
        if ($usuario === null) {
            return null;
        }

        $nombre = trim(collect([
            $usuario->nombre_1,
            $usuario->nombre_2,
            $usuario->apellido_1,
            $usuario->apellido_2,
        ])->filter()->implode(' '));

        return $nombre === '' ? $usuario->email : $nombre;
    }

    /**
     * @param  array<string, int|string|null>  $uso
     */
    public function resumenUso(array $uso): string
    {
        return sprintf(
            'Asignaciones: %d (%d activas) · Préstamos: %d (%d activos) · Movimientos: %d · Reportes: %d',
            $uso['asignaciones'],
            $uso['asignaciones_activas'],
            $uso['prestamos'],
            $uso['prestamos_activos'],
            $uso['movimientos'],
            $uso['reportes']
        );
    }

    private function relacion(Model $modelo, string $relacion): Collection
    {
        if (! $modelo->relationLoaded($relacion)) {
            $modelo->loadMissing($relacion);
        }

        return $modelo->getRelation($relacion);
    }

    /**
     * @return array{nombre: string, tipo: string}
     */
    private function responsableActual(
        Element $elemento,
        Collection $asignaciones,
        Collection $prestamos,
        Collection $cuentas
    ): array {
        $asignacionActiva = $asignaciones->first(
            fn ($asignacion) => in_array($asignacion->estado, self::ESTADOS_ASIGNACION_ACTIVA, true)
        );
        $aprendiz = $asignacionActiva?->apprentice?->user;

        if ($aprendiz !== null) {
            return [
                'nombre' => self::nombreCompleto($aprendiz) ?? 'Aprendiz',
                'tipo' => 'Aprendiz con asignación activa',
            ];
        }

        $prestamoActivo = $prestamos->first(
            fn ($prestamo) => in_array($prestamo->estado, self::ESTADOS_PRESTAMO_ACTIVO, true)
        );
        $instructorPrestamo = $prestamoActivo?->teacher?->user;

        if ($instructorPrestamo !== null) {
            return [
                'nombre' => self::nombreCompleto($instructorPrestamo) ?? 'Instructor',
                'tipo' => 'Instructor con préstamo activo',
            ];
        }

        $cuentaVigente = $cuentas->first(fn ($cuenta) => $cuenta->fecha_fin === null);
        $cuentadante = $cuentaVigente?->user;

        if ($cuentadante !== null) {
            return [
                'nombre' => self::nombreCompleto($cuentadante) ?? 'Cuenta-dante',
                'tipo' => 'Cuenta-dante del ambiente',
            ];
        }

        $instructorAmbiente = $elemento->environment?->teacher?->user;

        if ($instructorAmbiente !== null) {
            return [
                'nombre' => self::nombreCompleto($instructorAmbiente) ?? 'Instructor',
                'tipo' => 'Instructor responsable del ambiente',
            ];
        }

        if ($elemento->warehouse !== null) {
            return [
                'nombre' => $elemento->warehouse->nombre,
                'tipo' => 'Almacén',
            ];
        }

        return [
            'nombre' => 'Sin responsable registrado',
            'tipo' => 'Sin asignar',
        ];
    }

    /**
     * @return array<string, int|string|null>
     */
    private function uso(
        Collection $asignaciones,
        Collection $prestamos,
        Collection $movimientos,
        Collection $reportes
    ): array {
        $asignacionesActivas = $asignaciones->filter(
            fn ($asignacion) => in_array($asignacion->estado, self::ESTADOS_ASIGNACION_ACTIVA, true)
        );
        $prestamosActivos = $prestamos->filter(
            fn ($prestamo) => in_array($prestamo->estado, self::ESTADOS_PRESTAMO_ACTIVO, true)
        );

        return [
            'asignaciones' => $asignaciones->count(),
            'asignaciones_activas' => $asignacionesActivas->count(),
            'prestamos' => $prestamos->count(),
            'prestamos_activos' => $prestamosActivos->count(),
            'movimientos' => $movimientos->count(),
            'reportes' => $reportes->count(),
            'ultima_asignacion' => $this->ultimaFecha($asignaciones, 'fecha_asignacion'),
            'ultimo_prestamo' => $this->ultimaFecha($prestamos, 'fecha_prestamo'),
            'ultimo_movimiento' => $this->ultimaFecha($movimientos, 'fecha_movimiento'),
            'ultimo_reporte' => $this->ultimaFecha($reportes, 'fecha_reporte'),
        ];
    }

    private function ultimaFecha(Collection $items, string $campo): ?string
    {
        $fecha = $items->pluck($campo)
            ->filter()
            ->sortDesc()
            ->first();

        return $fecha instanceof DateTimeInterface
            ? $fecha->format('d/m/Y')
            : null;
    }
}
