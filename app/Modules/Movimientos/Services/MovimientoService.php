<?php

namespace App\Modules\Movimientos\Services;

use App\Modules\Movimientos\Models\Motion;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use Illuminate\Support\Facades\DB;

class MovimientoService
{
    public function listar()
    {
        return Motion::with(['transfer', 'elements'])
            ->latest('fecha_movimiento')
            ->paginate(10);
    }

    public function mostrar(int $id): Motion
    {
        return Motion::with(['transfer', 'elements'])
            ->findOrFail($id);
    }

    public function crear(array $datos): Motion
    {
        return DB::transaction(function () use ($datos) {
            $elementIds = $datos['element_ids'];
            unset($datos['element_ids']);

            $this->validarTraslado($datos['tipo'], $datos['transfer_id'] ?? null);
            $movimiento = Motion::create($datos);
            $movimiento->elements()->sync($elementIds);

            return $this->mostrar($movimiento->id);
        });
    }

    public function actualizar(int $id, array $datos): Motion
    {
        return DB::transaction(function () use ($id, $datos) {
            $movimiento = Motion::query()
                ->lockForUpdate()
                ->findOrFail($id);

            if ($movimiento->transfer_id !== null) {
                throw new ReglasNegocioExceptions(
                    'Los movimientos asociados a traslados deben gestionarse desde el módulo de traslados.'
                );
            }

            $elementIds = $datos['element_ids'] ?? null;
            unset($datos['element_ids']);
            $tipo = $datos['tipo'] ?? $movimiento->tipo;
            $transferId = array_key_exists('transfer_id', $datos)
                ? $datos['transfer_id']
                : $movimiento->transfer_id;

            $this->validarTraslado($tipo, $transferId);
            $movimiento->update($datos);

            if ($elementIds !== null) {
                $movimiento->elements()->sync($elementIds);
            }

            return $this->mostrar($id);
        });
    }

    public function eliminar(int $id): void
    {
        $movimiento = Motion::query()
            ->lockForUpdate()
            ->findOrFail($id);

        if ($movimiento->transfer_id !== null) {
            throw new ReglasNegocioExceptions(
                'Los movimientos asociados a traslados deben gestionarse desde el módulo de traslados.'
            );
        }

        $movimiento->delete();
    }

    private function validarTraslado(string $tipo, ?int $transferId): void
    {
        if ($tipo === 'traslado' && $transferId === null) {
            throw new ReglasNegocioExceptions(
                'Un movimiento de tipo traslado debe estar asociado a un traslado.'
            );
        }

        if ($tipo !== 'traslado' && $transferId !== null) {
            throw new ReglasNegocioExceptions(
                'Solo los movimientos de tipo traslado pueden asociarse a un traslado.'
            );
        }
    }
}
