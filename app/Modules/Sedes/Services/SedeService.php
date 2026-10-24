<?php

namespace App\Modules\Sedes\Services;

use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use App\Modules\Sedes\Models\Headquarter;

class SedeService
{
    public function listar()
    {
        return Headquarter::with('trainingcenter')
            ->withCount(['warehouses', 'environments'])
            ->latest()
            ->paginate(10);
    }

    public function mostrar(int $id): Headquarter
    {
        return Headquarter::with('trainingcenter')
            ->withCount(['warehouses', 'environments'])
            ->findOrFail($id);
    }

    public function crear(array $datos): Headquarter
    {
        $sede = Headquarter::create($datos);

        return $this->mostrar($sede->id);
    }

    public function actualizar(int $id, array $datos): Headquarter
    {
        $sede = $this->mostrar($id);
        $sede->update($datos);

        return $this->mostrar($id);
    }

    public function eliminar(int $id): void
    {
        $sede = $this->mostrar($id);

        if ($sede->warehouses()->exists() || $sede->environments()->exists()) {
            throw new ReglasNegocioExceptions(
                'No se puede eliminar la sede porque tiene almacenes o ambientes asociados.'
            );
        }

        $sede->delete();
    }
}
