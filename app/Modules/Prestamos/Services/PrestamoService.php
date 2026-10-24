<?php

namespace App\Modules\Prestamos\Services;

use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Elementos\Models\Element;
use App\Modules\Prestamos\Models\Loan;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use Illuminate\Support\Facades\DB;

class PrestamoService
{
    private const ESTADOS_ACTIVOS = ['activo', 'vencido'];

    public function listar()
    {
        return Loan::with(['element', 'teacher.user'])
            ->latest('fecha_prestamo')
            ->paginate(10);
    }

    public function mostrar(int $id): Loan
    {
        return Loan::with(['element', 'teacher.user'])
            ->findOrFail($id);
    }

    public function crear(array $datos): Loan
    {
        return DB::transaction(function () use ($datos) {
            $elemento = Element::query()
                ->lockForUpdate()
                ->findOrFail($datos['element_id']);
            $estado = $datos['estado'] ?? 'activo';

            if ($this->estaActivo($estado)) {
                $this->verificarElementoDisponible($elemento);
            }

            if ($estado === 'devuelto' && empty($datos['fecha_devolucion_real'])) {
                $datos['fecha_devolucion_real'] = now();
            }

            $prestamo = Loan::create($datos);

            if ($this->estaActivo($estado)) {
                $elemento->update(['estado' => 'prestado']);
            }

            return $this->mostrar($prestamo->id);
        });
    }

    public function actualizar(int $id, array $datos): Loan
    {
        return DB::transaction(function () use ($id, $datos) {
            $prestamo = Loan::query()
                ->lockForUpdate()
                ->findOrFail($id);
            $elementoAnteriorId = $prestamo->element_id;
            $elementoNuevoId = $datos['element_id'] ?? $elementoAnteriorId;
            $elementos = Element::query()
                ->whereIn('id', array_unique([$elementoAnteriorId, $elementoNuevoId]))
                ->orderBy('id')
                ->lockForUpdate()
                ->get()
                ->keyBy('id');
            $elementoAnterior = $elementos->get($elementoAnteriorId);
            $elementoNuevo = $elementos->get($elementoNuevoId);
            $estadoAnteriorActivo = $this->estaActivo($prestamo->estado);
            $estadoNuevo = $datos['estado'] ?? $prestamo->estado;
            $estadoNuevoActivo = $this->estaActivo($estadoNuevo);

            if ($estadoNuevoActivo) {
                $mismoElementoActivo = $elementoAnteriorId === $elementoNuevoId
                    && $estadoAnteriorActivo;

                if (! $mismoElementoActivo) {
                    $this->verificarElementoDisponible($elementoNuevo, $id);
                }
            }

            if (
                $estadoNuevo === 'devuelto'
                && empty($datos['fecha_devolucion_real'])
                && ($estadoAnteriorActivo || $prestamo->fecha_devolucion_real === null)
            ) {
                $datos['fecha_devolucion_real'] = now();
            } elseif (
                $estadoNuevoActivo
                && ! array_key_exists('fecha_devolucion_real', $datos)
            ) {
                $datos['fecha_devolucion_real'] = null;
            }

            $prestamo->update($datos);

            if (
                $elementoAnteriorId !== $elementoNuevoId
                && $estadoAnteriorActivo
            ) {
                $this->actualizarEstadoElemento($elementoAnterior);
            }

            if ($estadoNuevoActivo) {
                $elementoNuevo->update(['estado' => 'prestado']);
            } else {
                $this->actualizarEstadoElemento($elementoNuevo);
            }

            return $this->mostrar($id);
        });
    }

    public function eliminar(int $id): void
    {
        DB::transaction(function () use ($id) {
            $prestamo = Loan::query()
                ->lockForUpdate()
                ->findOrFail($id);
            $elemento = Element::query()
                ->lockForUpdate()
                ->findOrFail($prestamo->element_id);
            $estabaActivo = $this->estaActivo($prestamo->estado);

            $prestamo->delete();

            if ($estabaActivo) {
                $this->actualizarEstadoElemento($elemento);
            }
        });
    }

    private function verificarElementoDisponible(Element $elemento, ?int $prestamoId = null): void
    {
        $tieneOtroPrestamoActivo = Loan::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', self::ESTADOS_ACTIVOS)
            ->when(
                $prestamoId !== null,
                fn ($query) => $query->where('id', '!=', $prestamoId)
            )
            ->exists();
        $tieneAsignacionActiva = Assignment::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', ['activa', 'vencida'])
            ->exists();

        if (
            $elemento->estado !== 'disponible'
            || $tieneOtroPrestamoActivo
            || $tieneAsignacionActiva
        ) {
            throw new ReglasNegocioExceptions(
                'El elemento no está disponible para un nuevo préstamo.'
            );
        }
    }

    private function actualizarEstadoElemento(Element $elemento): void
    {
        $tienePrestamoActivo = Loan::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', self::ESTADOS_ACTIVOS)
            ->exists();
        $tieneAsignacionActiva = Assignment::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', ['activa', 'vencida'])
            ->exists();

        if ($tienePrestamoActivo) {
            $elemento->update(['estado' => 'prestado']);
        } elseif ($tieneAsignacionActiva) {
            $elemento->update(['estado' => 'asignado']);
        } elseif ($elemento->estado === 'prestado') {
            $elemento->update(['estado' => 'disponible']);
        }
    }

    private function estaActivo(string $estado): bool
    {
        return in_array($estado, self::ESTADOS_ACTIVOS, true);
    }
}
