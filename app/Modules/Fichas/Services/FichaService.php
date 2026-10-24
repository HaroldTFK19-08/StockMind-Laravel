<?php

namespace App\Modules\Fichas\Services;

use App\Modules\Fichas\Models\Coursegroup;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use Illuminate\Support\Carbon;

class FichaService
{
    public function listar()
    {
        return Coursegroup::with('teacher.user')
            ->withCount('apprentices')
            ->latest()
            ->paginate(10);
    }

    public function mostrar(int $id): Coursegroup
    {
        return Coursegroup::with('teacher.user')
            ->withCount('apprentices')
            ->findOrFail($id);
    }

    public function crear(array $datos): Coursegroup
    {
        $ficha = Coursegroup::create($datos);

        return $this->mostrar($ficha->id);
    }

    public function actualizar(int $id, array $datos): Coursegroup
    {
        $ficha = $this->mostrar($id);
        $fechaInicio = $datos['fecha_inicio'] ?? $ficha->fecha_inicio;
        $fechaFin = $datos['fecha_fin'] ?? $ficha->fecha_fin;

        if (Carbon::parse($fechaFin)->lt(Carbon::parse($fechaInicio))) {
            throw new ReglasNegocioExceptions(
                'La fecha de fin debe ser igual o posterior a la fecha de inicio.'
            );
        }

        $ficha->update($datos);

        return $this->mostrar($id);
    }

    public function eliminar(int $id): void
    {
        $ficha = $this->mostrar($id);

        if ($ficha->apprentices()->exists()) {
            throw new ReglasNegocioExceptions(
                'No se puede eliminar la ficha porque tiene aprendices asociados.'
            );
        }

        $ficha->delete();
    }
}
