<?php

namespace App\Modules\Shared\Services\Concerns;

use App\Modules\Elementos\Models\Element;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;

/**
 * Reglas de disponibilidad del elemento compartidas por asignaciones y
 * préstamos. Cada módulo entrega sus estados, modelos y mensaje; el algoritmo
 * de validación y recálculo del estado vive aquí una sola vez.
 */
trait GestionaDisponibilidadElemento
{
    /** @return array<int, string> */
    abstract protected function estadosActivos(): array;

    /** @return array<int, string> */
    abstract protected function estadosOpuestosActivos(): array;

    /** @return class-string */
    abstract protected function modeloUso(): string;

    /** @return class-string */
    abstract protected function modeloOpuesto(): string;

    abstract protected function estadoElementoOcupado(): string;

    abstract protected function estadoElementoOcupadoOpuesto(): string;

    abstract protected function mensajeNoDisponible(): string;

    protected function estaActivo(string $estado): bool
    {
        return in_array($estado, $this->estadosActivos(), true);
    }

    protected function verificarElementoDisponible(Element $elemento, ?int $idUso = null): void
    {
        $modeloUso = $this->modeloUso();
        $modeloOpuesto = $this->modeloOpuesto();

        $tieneUsoActivo = $modeloUso::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', $this->estadosActivos())
            ->when($idUso !== null, fn ($query) => $query->where('id', '!=', $idUso))
            ->exists();
        $tieneOpuestoActivo = $modeloOpuesto::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', $this->estadosOpuestosActivos())
            ->exists();

        if ($elemento->estado !== 'disponible' || $tieneUsoActivo || $tieneOpuestoActivo) {
            throw new ReglasNegocioExceptions($this->mensajeNoDisponible());
        }
    }

    protected function actualizarEstadoElemento(Element $elemento): void
    {
        $modeloUso = $this->modeloUso();
        $modeloOpuesto = $this->modeloOpuesto();

        $tieneUsoActivo = $modeloUso::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', $this->estadosActivos())
            ->exists();
        $tieneOpuestoActivo = $modeloOpuesto::query()
            ->where('element_id', $elemento->id)
            ->whereIn('estado', $this->estadosOpuestosActivos())
            ->exists();

        if ($tieneUsoActivo) {
            $elemento->update(['estado' => $this->estadoElementoOcupado()]);
        } elseif ($tieneOpuestoActivo) {
            $elemento->update(['estado' => $this->estadoElementoOcupadoOpuesto()]);
        } elseif ($elemento->estado === $this->estadoElementoOcupado()) {
            $elemento->update(['estado' => 'disponible']);
        }
    }
}
