<?php

namespace App\Modules\Traslados\Services;

use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Ambientes\Models\Environment;
use App\Modules\Elementos\Models\Element;
use App\Modules\Movimientos\Models\Motion;
use App\Modules\Prestamos\Models\Loan;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use App\Modules\Traslados\Models\Transfer;
use App\Modules\Usuarios\Models\Danteaccount;
use Illuminate\Support\Facades\DB;

class TrasladoService
{
    public function listar()
    {
        return Transfer::with([
            'originEnvironment',
            'destinationEnvironment',
            'danteaccount.user',
            'motion.elements',
        ])
            ->latest('fecha_solicitud')
            ->paginate(10);
    }

    public function mostrar(int $id): Transfer
    {
        return Transfer::with([
            'originEnvironment',
            'destinationEnvironment',
            'danteaccount.user',
            'motion.elements',
        ])->findOrFail($id);
    }

    public function crear(array $datos): Transfer
    {
        return DB::transaction(function () use ($datos) {
            $elementIds = $datos['element_ids'];
            unset($datos['element_ids']);
            $estado = $datos['estado'] ?? 'pendiente';
            $fechaTraslado = $datos['fecha_traslado'] ?? null;

            if (in_array($estado, ['aprobado', 'completado'], true)) {
                $this->validarCuentaDante(
                    $datos['danteaccount_id'],
                    $datos['ambiente_origen_id']
                );
            }

            if ($estado === 'completado' && $fechaTraslado === null) {
                $datos['fecha_traslado'] = now();
            }

            $traslado = Transfer::create($datos);
            $movimiento = Motion::create([
                'transfer_id' => $traslado->id,
                'tipo' => 'traslado',
                'motivo' => $traslado->motivo,
                'fecha_movimiento' => $traslado->fecha_traslado ?? $traslado->fecha_solicitud,
            ]);
            $movimiento->elements()->sync($elementIds);

            if ($estado === 'completado') {
                $this->completarTraslado($traslado, $elementIds);
            }

            return $this->mostrar($traslado->id);
        });
    }

    public function actualizar(int $id, array $datos): Transfer
    {
        return DB::transaction(function () use ($id, $datos) {
            $traslado = Transfer::query()
                ->lockForUpdate()
                ->findOrFail($id);
            $elementIds = $datos['element_ids'] ?? null;
            unset($datos['element_ids']);
            $estadoAnterior = $traslado->estado;
            $estadoNuevo = $datos['estado'] ?? $estadoAnterior;
            $elementIds ??= $traslado->motion()
                ->with('elements:id')
                ->firstOrFail()
                ->elements
                ->modelKeys();

            if ($estadoAnterior === 'completado') {
                throw new ReglasNegocioExceptions(
                    'Un traslado completado no se puede modificar.'
                );
            }

            if ($estadoAnterior === 'rechazado') {
                throw new ReglasNegocioExceptions(
                    'Un traslado rechazado no se puede modificar.'
                );
            }

            if ($estadoAnterior === 'aprobado' && $estadoNuevo !== 'completado') {
                throw new ReglasNegocioExceptions(
                    'Un traslado aprobado solo puede completarse.'
                );
            }

            if (
                in_array($estadoNuevo, ['aprobado', 'completado'], true)
                && $estadoAnterior === 'pendiente'
            ) {
                $this->validarCuentaDante(
                    $datos['danteaccount_id'] ?? $traslado->danteaccount_id,
                    $datos['ambiente_origen_id'] ?? $traslado->ambiente_origen_id
                );
            }

            if ($estadoNuevo === 'completado') {
                if (empty($datos['fecha_traslado'])) {
                    $datos['fecha_traslado'] = now();
                }
                $this->completarTraslado(
                    $traslado,
                    $elementIds,
                    $datos['ambiente_origen_id'] ?? $traslado->ambiente_origen_id,
                    $datos['ambiente_destino_id'] ?? $traslado->ambiente_destino_id,
                    $datos['danteaccount_id'] ?? $traslado->danteaccount_id
                );
            }

            $traslado->update($datos);
            $traslado->refresh();
            $movimiento = $traslado->motion()->firstOrFail();
            $movimiento->update([
                'motivo' => $traslado->motivo,
                'fecha_movimiento' => $traslado->fecha_traslado ?? $traslado->fecha_solicitud,
            ]);
            $movimiento->elements()->sync($elementIds);

            return $this->mostrar($id);
        });
    }

    public function eliminar(int $id): void
    {
        DB::transaction(function () use ($id) {
            $traslado = Transfer::query()
                ->lockForUpdate()
                ->findOrFail($id);

            if (!in_array($traslado->estado, ['pendiente', 'rechazado'], true)) {
                throw new ReglasNegocioExceptions(
                    'Solo se pueden eliminar traslados pendientes o rechazados.'
                );
            }

            $traslado->motion()->first()?->delete();
            $traslado->delete();
        });
    }

    private function completarTraslado(
        Transfer $traslado,
        array $elementIds,
        ?int $origenId = null,
        ?int $destinoId = null,
        ?int $cuentaId = null
    ): void {
        $origenId ??= $traslado->ambiente_origen_id;
        $destinoId ??= $traslado->ambiente_destino_id;
        $cuentaId ??= $traslado->danteaccount_id;

        $this->validarCuentaDante($cuentaId, $origenId);

        $ambienteDestino = Environment::query()
            ->lockForUpdate()
            ->findOrFail($destinoId);

        if ($ambienteDestino->estado !== 'activo') {
            throw new ReglasNegocioExceptions(
                'El ambiente de destino debe estar activo para completar el traslado.'
            );
        }

        $cuenta = Danteaccount::query()
            ->lockForUpdate()
            ->findOrFail($cuentaId);
        $cuentaActivaExistente = Danteaccount::query()
            ->where('user_id', $cuenta->user_id)
            ->whereNull('fecha_fin')
            ->where('id', '!=', $cuenta->id)
            ->exists();

        if ($cuentaActivaExistente) {
            throw new ReglasNegocioExceptions(
                'El aprendiz ya tiene otra cuenta DANTE vigente.'
            );
        }

        $aprendicesEnDestino = $ambienteDestino->danteaccounts()
            ->whereNull('fecha_fin')
            ->count();

        if ($aprendicesEnDestino >= $ambienteDestino->capacidad) {
            throw new ReglasNegocioExceptions(
                'El ambiente de destino no tiene capacidad disponible.'
            );
        }

        $elementos = Element::query()
            ->whereIn('id', $elementIds)
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        if ($elementos->count() !== count(array_unique($elementIds))) {
            throw new ReglasNegocioExceptions(
                'No se encontraron todos los elementos del traslado.'
            );
        }

        foreach ($elementos as $elemento) {
            $tieneAsignacionActiva = Assignment::query()
                ->where('element_id', $elemento->id)
                ->whereIn('estado', ['activa', 'vencida'])
                ->exists();
            $tienePrestamoActivo = Loan::query()
                ->where('element_id', $elemento->id)
                ->whereIn('estado', ['activo', 'vencido'])
                ->exists();

            if (
                $elemento->environment_id !== $origenId
                || $elemento->estado !== 'disponible'
                || $tieneAsignacionActiva
                || $tienePrestamoActivo
            ) {
                throw new ReglasNegocioExceptions(
                    'Todos los elementos deben estar disponibles y ubicados en el ambiente de origen para completar el traslado.'
                );
            }
        }

        foreach ($elementos as $elemento) {
            $elemento->update([
                'environment_id' => $destinoId,
                'warehouse_id' => null,
            ]);
        }

        $fechaFin = now()->toDateString();
        $cuenta->update(['fecha_fin' => $fechaFin]);
        Danteaccount::create([
            'user_id' => $cuenta->user_id,
            'environment_id' => $destinoId,
            'fecha_inicio' => $fechaFin,
            'fecha_fin' => null,
        ]);
    }

    private function validarCuentaDante(int $cuentaId, int $origenId): void
    {
        $cuenta = Danteaccount::query()
            ->lockForUpdate()
            ->findOrFail($cuentaId);

        if ($cuenta->fecha_fin !== null || $cuenta->environment_id !== $origenId) {
            throw new ReglasNegocioExceptions(
                'La cuenta DANTE debe estar vigente y pertenecer al ambiente de origen.'
            );
        }
    }
}
