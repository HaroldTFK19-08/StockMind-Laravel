<?php

namespace App\Modules\Ambientes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Ambientes\Facades\AmbienteFacade;
use App\Modules\Ambientes\Requests\ActualizarAmbienteRequest;
use App\Modules\Ambientes\Requests\RegistroAmbienteRequest;
use Illuminate\Http\JsonResponse;

class AmbientesController extends Controller
{
    use RespondeApi;

    public function __construct(
        private AmbienteFacade $ambienteFacade
    ) {}

    public function index(): JsonResponse
    {
        return $this->listado(
            $this->ambienteFacade->listar()
        );
    }

    public function store(RegistroAmbienteRequest $request): JsonResponse
    {
        $ambiente = $this->ambienteFacade->crear(
            $request->validated()
        );

        return $this->creado('Ambiente', $ambiente);
    }

    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->ambienteFacade->mostrar($id));
    }

    public function update(
        ActualizarAmbienteRequest $request,
        int $id
    ): JsonResponse {
        $ambiente = $this->ambienteFacade->actualizar(
            $id,
            $request->validated()
        );

        return $this->actualizado('Ambiente', $ambiente);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->ambienteFacade->eliminar($id);

        return $this->eliminado('Ambiente');
    }
}
