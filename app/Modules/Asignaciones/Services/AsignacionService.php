<?php

namespace App\Modules\Asignaciones\Services;

use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Elementos\Models\Element;
use App\Modules\Prestamos\Models\Loan;
use App\Modules\Shared\Enums\TipoNotificacion;
use App\Modules\Shared\Services\CacheService;
use App\Modules\Shared\Services\Concerns\FiltraPdfPorAlcance;
use App\Modules\Shared\Services\Concerns\GeneraPdf;
use App\Modules\Shared\Services\Concerns\GestionaDisponibilidadElemento;
use App\Modules\Shared\Services\NotificacionService;
use App\Modules\Shared\Services\PDFService;
use App\Modules\Shared\Services\ResponsablesElementoService;
use App\Modules\Usuarios\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AsignacionService
{
    use FiltraPdfPorAlcance;
    use GeneraPdf;
    use GestionaDisponibilidadElemento;

    protected function vistaPdf(): string
    {
        return 'asignaciones.pdf';
    }

    protected function nombrePdf(): string
    {
        return 'asignaciones.pdf';
    }

    public function __construct(
        private CacheService $cache,
        private NotificacionService $notificacionService,
        private PDFService $pdfService,
        private ResponsablesElementoService $responsablesElementoService
    ) {}

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

            if ($this->estaActivo($estado)) {
                $this->verificarElementoDisponible($elemento);
            }

            $asignacion = Assignment::create($datos);

            if ($this->estaActivo($estado)) {
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
            $estadoAnteriorActivo = $this->estaActivo($asignacion->estado);
            $estadoNuevo = $datos['estado'] ?? $asignacion->estado;
            $estadoNuevoActivo = $this->estaActivo($estadoNuevo);

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
            $estabaActiva = $this->estaActivo($asignacion->estado);

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

    /**
     * @return array{asignaciones: array<int, array<string, mixed>>, total: int, generado_en: \Illuminate\Support\Carbon}
     */
    private function datosParaPdf(?User $usuario = null): array
    {
        $query = Assignment::query()
            ->with([
                'element' => static fn ($query) => $query
                    ->with(ResponsablesElementoService::relacionesParaPdf()),
                'apprentice.user',
                'teacher.user',
            ])
            ->latest('fecha_asignacion');

        $this->aplicarAlcancePdf($query, $usuario);
        $asignaciones = $query->get();

        $filas = $asignaciones->map(function (Assignment $asignacion) {
            $elemento = $asignacion->element;
            $ficha = $elemento !== null
                ? $this->responsablesElementoService->ficha($elemento)
                : null;

            return [
                'id' => $asignacion->id,
                'codigo' => $elemento?->codigo_inventario ?? 'N/D',
                'elemento' => $elemento?->nombre ?? 'Elemento no disponible',
                'aprendiz' => ResponsablesElementoService::nombreCompleto($asignacion->apprentice?->user)
                    ?? 'Sin aprendiz registrado',
                'instructor' => ResponsablesElementoService::nombreCompleto($asignacion->teacher?->user)
                    ?? 'Sin instructor registrado',
                'fecha_asignacion' => $asignacion->fecha_asignacion,
                'fecha_devolucion_esperada' => $asignacion->fecha_devolucion_esperada,
                'fecha_devolucion_real' => $asignacion->fecha_devolucion_real,
                'estado' => $asignacion->estado,
                'observaciones' => $asignacion->observaciones,
                'responsable' => $ficha['responsable'] ?? 'Sin responsable registrado',
                'tipo_responsable' => $ficha['tipo_responsable'] ?? 'Sin asignar',
                'resumen_uso' => $ficha['resumen_uso'] ?? 'Sin información de uso',
            ];
        })->all();

        return [
            'asignaciones' => $filas,
            'total' => $asignaciones->count(),
            'generado_en' => now(),
        ];
    }

    private function aplicarAlcancePdf(Builder $query, ?User $usuario): void
    {
        if ($usuario === null || $usuario->tieneRol('Administrador')) {
            return;
        }

        $ambienteIds = $this->ambientesAsignadosIds($usuario);
        if ($ambienteIds !== []) {
            $query->whereHas('element', fn (Builder $q) => $q->whereIn('environment_id', $ambienteIds));
            return;
        }

        if ($usuario->tieneRol('Instructor')) {
            $instructorId = $usuario->teacher?->id;
            $aprendizIds = $this->aprendicesDeInstructorIds($usuario);
            $query->where(function (Builder $q) use ($instructorId, $aprendizIds) {
                $q->where('teacher_id', $instructorId);
                if ($aprendizIds !== []) {
                    $q->orWhereIn('apprentice_id', $aprendizIds);
                }
            });
            return;
        }

        $this->restringirConsultaSinAlcance($query);
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

    // Configuración que el trait necesita para el dominio de asignaciones.
    protected function estadosActivos(): array
    {
        return ['activa', 'vencida'];
    }

    protected function estadosOpuestosActivos(): array
    {
        return ['activo', 'vencido'];
    }

    protected function modeloUso(): string
    {
        return Assignment::class;
    }

    protected function modeloOpuesto(): string
    {
        return Loan::class;
    }

    protected function estadoElementoOcupado(): string
    {
        return 'asignado';
    }

    protected function estadoElementoOcupadoOpuesto(): string
    {
        return 'prestado';
    }

    protected function mensajeNoDisponible(): string
    {
        return 'El elemento no está disponible para una nueva asignación.';
    }
}
