<?php

namespace App\Modules\Prestamos\Services;

use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Elementos\Models\Element;
use App\Modules\Prestamos\Models\Loan;
use Illuminate\Support\Facades\DB;
use App\Modules\Shared\Services\CacheService;
use App\Modules\Shared\Services\Concerns\GestionaDisponibilidadElemento;

class PrestamoService
{
    // Disponibilidad del elemento y recálculo de estado compartidos con asignaciones.
    use GestionaDisponibilidadElemento;

    public function __construct(
        private CacheService $cache
    ) {}

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

            $this->cache->invalidar('prestamos');

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

            $this->cache->invalidar('prestamos');

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

            $this->cache->invalidar('prestamos');

            if ($estabaActivo) {
                $this->actualizarEstadoElemento($elemento);
            }
        });
    }

    // Configuración que el trait necesita para el dominio de préstamos.
    protected function estadosActivos(): array
    {
        return ['activo', 'vencido'];
    }

    protected function estadosOpuestosActivos(): array
    {
        return ['activa', 'vencida'];
    }

    protected function modeloUso(): string
    {
        return Loan::class;
    }

    protected function modeloOpuesto(): string
    {
        return Assignment::class;
    }

    protected function estadoElementoOcupado(): string
    {
        return 'prestado';
    }

    protected function estadoElementoOcupadoOpuesto(): string
    {
        return 'asignado';
    }

    protected function mensajeNoDisponible(): string
    {
        return 'El elemento no está disponible para un nuevo préstamo.';
    }
}
