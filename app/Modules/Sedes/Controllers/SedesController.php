<?php

namespace App\Modules\Sedes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Sedes\Facades\SedeFacade;
use App\Modules\Sedes\Requests\ActualizarSedeRequest;
use App\Modules\Sedes\Requests\RegistroSedeRequest;
use Illuminate\Http\JsonResponse;

class SedesController extends Controller
{
    use RespondeApi;

    public function __construct(
        private SedeFacade $sedeFacade
    ) {}

    public function index(): JsonResponse
    {
        return $this->listado(
            $this->sedeFacade->listar()
        );
    }

    public function store(RegistroSedeRequest $request): JsonResponse
    {
        $sede = $this->sedeFacade->crear(
            $request->validated()
        );

        return $this->creado('Sede', $sede, true);
    }

    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->sedeFacade->mostrar($id));
    }

    public function update(
        ActualizarSedeRequest $request,
        int $id
    ): JsonResponse {
        $sede = $this->sedeFacade->actualizar(
            $id,
            $request->validated()
        );

        return $this->actualizado('Sede', $sede, true);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->sedeFacade->eliminar($id);

        return $this->eliminado('Sede', true);
    }
}
