<?php

namespace App\Modules\Ambientes\Services;

use App\Modules\Ambientes\Models\Environment;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;

class AmbienteService
{
    public function listar()
    {
        return Environment::with(['headquarter', 'teacher'])
            ->withCount([
                'elements',
                'danteaccounts as danteaccounts_count' => fn ($query) => $query
                    ->whereNull('fecha_fin'),
            ])
            ->latest()
            ->paginate(10);
    }

    public function mostrar(int $id): Environment
    {
        return Environment::with(['headquarter', 'teacher'])
            ->withCount([
                'elements',
                'danteaccounts as danteaccounts_count' => fn ($query) => $query
                    ->whereNull('fecha_fin'),
            ])
            ->findOrFail($id);
    }

    public function crear(array $datos): Environment
    {
        $ambiente = Environment::create($datos);

        return $this->mostrar($ambiente->id);
    }

    public function actualizar(int $id, array $datos): Environment
    {
        $ambiente = $this->mostrar($id);
        $capacidad = $datos['capacidad'] ?? null;

        if (
            $capacidad !== null
            && $capacidad < $ambiente->danteaccounts()
                ->whereNull('fecha_fin')
                ->count()
        ) {
            throw new ReglasNegocioExceptions(
                'La capacidad no puede ser menor que la cantidad de aprendices actualmente asignados.'
            );
        }

        $ambiente->update($datos);

        return $this->mostrar($id);
    }

    public function eliminar(int $id): void
    {
        $ambiente = $this->mostrar($id);

        if (
            $ambiente->elements()->exists()
            || $ambiente->danteaccounts()->exists()
            || $ambiente->outgoingTransfers()->exists()
            || $ambiente->incomingTransfers()->exists()
        ) {
            throw new ReglasNegocioExceptions(
                'No se puede eliminar el ambiente porque tiene elementos, cuentas o traslados asociados.'
            );
        }

        $ambiente->delete();
    }
}
