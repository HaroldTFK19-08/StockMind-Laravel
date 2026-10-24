<?php

namespace App\Modules\Asignaciones\Services;

use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Elementos\Models\Element;
use App\Modules\Prestamos\Models\Loan;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use Illuminate\Support\Facades\DB;

class AsignacionService
{
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

            return $this->mostrar($asignacion->id);
        });
    }

    public function actualizar(int $id, array $datos): Assignment
    {
        return DB::transaction(function () use ($id, $datos) {
            $asignacion = Assignment::query()
                ->lockForUpdate()
                ->findOrFail($id);
            $elementoAnteriorId = $asignacion->element_id;
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

            return $this->mostrar($id);
        });
    }

    public function eliminar(int $id): void
    {
        DB::transaction(function () use ($id) {
            $asignacion = Assignment::query()
                ->lockForUpdate()
                ->findOrFail($id);
            $elemento = Element::query()
                ->lockForUpdate()
                ->findOrFail($asignacion->element_id);
            $estabaActiva = $this->estaActiva($asignacion->estado);

            $asignacion->delete();

            if ($estabaActiva) {
                $this->actualizarEstadoElemento($elemento);
            }
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

        if (!$tieneAsignacionActiva && !$tienePrestamoActivo) {
            $elemento->update(['estado' => 'disponible']);
        }
    }

    private function estaActiva(string $estado): bool
    {
        return in_array($estado, self::ESTADOS_ACTIVOS, true);
    }
}
