<?php

namespace App\Modules\Asignaciones\Services;

use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Elementos\Models\Element;
use App\Modules\Prestamos\Models\Loan;
use App\Modules\Shared\Enums\TipoNotificacion;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use Illuminate\Support\Facades\DB;
use App\Modules\Shared\Services\CacheService;
use App\Modules\Shared\Services\NotificacionService;

class AsignacionService
{
    public function __construct(
        private CacheService $cache,
        private NotificacionService $notificacionService
    ) {}

    private const ESTADOS_ACTIVOS = ['activa', 'vencida'];

    public function listar()
    {
        return Assignment::with([
            'element',
            'apprentice.user',
            'teacher.user',
        ])
            ->latest()
            ->paginate(10);
    }

    public function mostrar(int $id): Assignment
    {
        return Assignment::with([
            'element',
            'apprentice.user',
            'teacher.user',
        ])->findOrFail($id);
    }

    public function crear(array $datos): Assignment
    {
        return DB::transaction(function () use ($datos) {
            $elemento = Element::query()
                ->lockForUpdate()
                ->findOrFail($datos['element_id']);
            $estado = $datos['estado'] ?? 'activa';

            if ($this->estaActiva($estado)) {
                $this->verificarElementoDisponible($elemento);
            }

            $asignacion = Assignment::create($datos);

            if ($this->estaActiva($estado)) {
                $elemento->update(['estado' => 'asignado']);
            }

            $this->cache->invalidar('asignaciones');

            $asignacion = $this->mostrar($asignacion->id);
            $this->notificarAlAprendiz(
                $asignacion,
                'Tienes una nueva asignación',
                "Se te asignó el elemento {$asignacion->element?->nombre} con estado {$asignacion->estado}.",
                TipoNotificacion::AsignacionCreada
            );

            return $asignacion;
        });
    }

    public function actualizar(int $id, array $datos): Assignment
    {
        return DB::transaction(function () use ($id, $datos) {
            $asignacion = Assignment::query()
                ->lockForUpdate()
                ->findOrFail($id);
            $elementoAnteriorId = $asignacion->element_id;
            $estadoAnterior = $asignacion->estado;
            $elementoNuevoId = $datos['element_id'] ?? $elementoAnteriorId;
            $elementos = Element::query()
                ->whereIn('id', array_unique([$elementoAnteriorId, $elementoNuevoId]))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $elementoAnterior = $elementos->get($elementoAnteriorId);
            $elementoNuevo = $elementos->get($elementoNuevoId);
            $estadoAnteriorActivo = $this->estaActiva($asignacion->estado);
            $estadoNuevo = $datos['estado'] ?? $asignacion->estado;
            $estadoNuevoActivo = $this->estaActiva($estadoNuevo);

            if ($estadoNuevoActivo) {
                $mismoElementoActivo = $elementoAnteriorId === $elementoNuevoId
                    && $estadoAnteriorActivo;

                if (!$mismoElementoActivo) {
                    $this->verificarElementoDisponible($elementoNuevo, $id);
                }
            }

            $asignacion->update($datos);

            if ($elementoAnteriorId !== $elementoNuevoId && $estadoAnteriorActivo) {
                $this->actualizarEstadoElemento($elementoAnterior);
            }

            if ($estadoNuevoActivo) {
                $elementoNuevo->update(['estado' => 'asignado']);
            } else {
                $this->actualizarEstadoElemento($elementoNuevo);
            }

            $this->cache->invalidar('asignaciones');

            $asignacionActualizada = $this->mostrar($id);

            if (
                $estadoAnterior !== $asignacionActualizada->estado
                || $elementoAnteriorId !== $elementoNuevoId
            ) {
                $this->notificarAlAprendiz(
                    $asignacionActualizada,
                    'Tu asignación fue actualizada',
                    "Tu asignación del elemento {$asignacionActualizada->element?->nombre} ahora está {$asignacionActualizada->estado}.",
                    TipoNotificacion::AsignacionActualizada
                );
            }

            return $asignacionActualizada;
        });
    }

    public function eliminar(int $id): void
    {
        DB::transaction(function () use ($id) {
            $asignacion = Assignment::query()
                ->lockForUpdate()
                ->findOrFail($id);
            $datosAsignacion = $this->mostrar($asignacion->id);
            $elemento = Element::query()
                ->lockForUpdate()
                ->findOrFail($asignacion->element_id);
            $estabaActiva = $this->estaActiva($asignacion->estado);

            $asignacion->delete();

            $this->cache->invalidar('asignaciones');

            if ($estabaActiva) {
                $this->actualizarEstadoElemento($elemento);
            }

            $this->notificarAlAprendiz(
                $datosAsignacion,
                'Tu asignación fue cancelada',
                "La asignación del elemento {$datosAsignacion->element?->nombre} fue eliminada.",
                TipoNotificacion::AsignacionActualizada
            );
        });
    }

    private function verificarElementoDisponible(Element $elemento, ?int $asignacionId = null): void
    {
        $tieneAsignacionActiva = Assignment::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', self::ESTADOS_ACTIVOS)
            ->when(
                $asignacionId !== null,
                fn ($query) => $query->where('id', '!=', $asignacionId)
            )
            ->exists();
        $tienePrestamoActivo = Loan::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', ['activo', 'vencido'])
            ->exists();

        if (
            $elemento->estado !== 'disponible'
            || $tieneAsignacionActiva
            || $tienePrestamoActivo
        ) {
            throw new ReglasNegocioExceptions(
                'El elemento no está disponible para una nueva asignación.'
            );
        }
    }

    private function actualizarEstadoElemento(Element $elemento): void
    {
        $tieneAsignacionActiva = Assignment::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', self::ESTADOS_ACTIVOS)
            ->exists();
        $tienePrestamoActivo = Loan::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', ['activo', 'vencido'])
            ->exists();

        if ($tieneAsignacionActiva) {
            $elemento->update(['estado' => 'asignado']);
        } elseif ($tienePrestamoActivo) {
            $elemento->update(['estado' => 'prestado']);
        } elseif ($elemento->estado === 'asignado') {
            $elemento->update(['estado' => 'disponible']);
        }
    }

    /**
     * Notifica al aprendiz dueño de la asignación (ASMR-34 y ASMR-36).
     */
    private function notificarAlAprendiz(
        Assignment $asignacion,
        string $titulo,
        string $mensaje,
        TipoNotificacion $tipo
    ): void {
        $usuarioId = $asignacion->apprentice?->user_id;

        if ($usuarioId === null) {
            return;
        }

        $this->notificacionService->aUsuario(
            $usuarioId,
            $titulo,
            $mensaje,
            $tipo,
            [
                'asignacion_id' => $asignacion->id,
                'elemento_id' => $asignacion->element_id,
                'estado' => $asignacion->estado,
            ]
        );
    }

    private function estaActiva(string $estado): bool
    {
        return in_array($estado, self::ESTADOS_ACTIVOS, true);
    }
}
