<?php

namespace App\Modules\Almacenes\Services;

use App\Modules\Almacenes\Models\Warehouse;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;

class AlmacenService
{
    public function listar()
    {
        return Warehouse::with('headquarter')
            ->withCount('elements')
            ->latest()
            ->paginate(10);
    }

    public function mostrar(int $id): Warehouse
    {
        return Warehouse::with('headquarter')
            ->withCount('elements')
            ->findOrFail($id);
    }

    public function crear(array $datos): Warehouse
    {
        $almacen = Warehouse::create($datos);

        return $this->mostrar($almacen->id);
    }

    public function actualizar(int $id, array $datos): Warehouse
    {
        $almacen = $this->mostrar($id);
        $capacidad = $datos['capacidad_elementos'] ?? null;

        if ($capacidad !== null && $capacidad < $almacen->elements()->count()) {
            throw new ReglasNegocioExceptions(
                'La capacidad no puede ser menor que la cantidad de elementos almacenados.'
            );
        }

        $almacen->update($datos);

        return $this->mostrar($id);
    }

    public function eliminar(int $id): void
    {
        $almacen = $this->mostrar($id);

        if ($almacen->elements()->exists()) {
            throw new ReglasNegocioExceptions(
                'No se puede eliminar el almacén porque tiene elementos asociados.'
            );
        }

        $almacen->delete();
    }
}
