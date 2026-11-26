<?php

namespace App\Modules\Prestamos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Prestamos\Facades\PrestamoFacade;
use App\Modules\Prestamos\Requests\ActualizarPrestamoRequest;
use App\Modules\Prestamos\Requests\RegistroPrestamoRequest;
use Illuminate\Http\JsonResponse;

class PrestamosController extends Controller
{
    use RespondeApi;

    public function __construct(
        private PrestamoFacade $prestamoFacade
    ) {}

    public function index(): JsonResponse
    {
        return $this->listado(
            $this->prestamoFacade->listar()
        );
    }

    public function store(RegistroPrestamoRequest $request): JsonResponse
    {
        $prestamo = $this->prestamoFacade->crear(
            $request->validated()
        );

        return $this->creado('Préstamo', $prestamo);
    }

    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->prestamoFacade->mostrar($id));
    }

    public function update(
        ActualizarPrestamoRequest $request,
        int $id
    ): JsonResponse {
        $prestamo = $this->prestamoFacade->actualizar(
            $id,
            $request->validated()
        );

        return $this->actualizado('Préstamo', $prestamo);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->prestamoFacade->eliminar($id);

        return $this->eliminado('Préstamo');
    }
}
