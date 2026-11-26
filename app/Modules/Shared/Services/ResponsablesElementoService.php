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

    /**
     * Delegamos el formato al modelo para no repetir la concatenación
     * en servicios, reportes ni vistas PDF.
     */
    public static function nombreCompleto(?User $usuario): ?string
    {
        return $usuario?->nombre_completo;
    }

    /**
     * Suma el uso de varias fichas. El listado de reportes dejó de repetir
     * seis array_sum() y delega aquí el mismo cálculo.
     *
     * @param  array<int, array<string, mixed>>  $fichas
     * @return array<string, int>
     */
    public function sumarUso(array $fichas): array
    {
        $claves = [
            'asignaciones',
            'asignaciones_activas',
            'prestamos',
            'prestamos_activos',
            'movimientos',
            'reportes',
        ];
        $usos = array_column($fichas, 'uso');

        return array_combine(
            $claves,
            array_map(
                static fn (string $clave): int => array_sum(array_column($usos, $clave)),
                $claves
            )
        );
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
        // loadMissing() no recarga una relación ya cargada: evita el if previo.
        return $modelo->loadMissing($relacion)->getRelation($relacion);
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
        $prestamoActivo = $prestamos->first(
            fn ($prestamo) => in_array($prestamo->estado, self::ESTADOS_PRESTAMO_ACTIVO, true)
        );
        $cuentaVigente = $cuentas->first(fn ($cuenta) => $cuenta->fecha_fin === null);

        // Cadena de prioridad: asignación > préstamo > cuenta DANTE > instructor > almacén.
        return $this->responsable(
            $asignacionActiva?->apprentice?->user,
            'Aprendiz',
            'Aprendiz con asignación activa'
        ) ?? $this->responsable(
            $prestamoActivo?->teacher?->user,
            'Instructor',
            'Instructor con préstamo activo'
        ) ?? $this->responsable(
            $cuentaVigente?->user,
            'Cuenta-dante',
            'Cuenta-dante del ambiente'
        ) ?? $this->responsable(
            $elemento->environment?->teacher?->user,
            'Instructor',
            'Instructor responsable del ambiente'
        ) ?? [
            'nombre' => $elemento->warehouse?->nombre ?? 'Sin responsable registrado',
            'tipo' => $elemento->warehouse !== null ? 'Almacén' : 'Sin asignar',
        ];
    }

    /** @return array{nombre: string, tipo: string}|null */
    private function responsable(?User $usuario, string $fallback, string $tipo): ?array
    {
        if ($usuario === null) {
            return null;
        }

        return ['nombre' => self::nombreCompleto($usuario) ?? $fallback, 'tipo' => $tipo];
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
        // Un único contador paramétrico reemplaza los dos filtros duplicados.
        $activos = static fn (Collection $items, array $estados): int => $items
            ->filter(fn ($item) => in_array($item->estado, $estados, true))
            ->count();

        return [
            'asignaciones' => $asignaciones->count(),
            'asignaciones_activas' => $activos($asignaciones, self::ESTADOS_ASIGNACION_ACTIVA),
            'prestamos' => $prestamos->count(),
            'prestamos_activos' => $activos($prestamos, self::ESTADOS_PRESTAMO_ACTIVO),
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
