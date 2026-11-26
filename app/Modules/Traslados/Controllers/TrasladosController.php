<?php

namespace App\Modules\Traslados\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Traslados\Facades\TrasladoFacade;
use App\Modules\Traslados\Requests\ActualizarTrasladoRequest;
use App\Modules\Traslados\Requests\RegistroTrasladoRequest;
use Illuminate\Http\JsonResponse;

class TrasladosController extends Controller
{
    use RespondeApi;

    public function __construct(
        private TrasladoFacade $trasladoFacade
    ) {}

    public function index(): JsonResponse
    {
        return $this->listado(
            $this->trasladoFacade->listar()
        );
    }

    public function store(RegistroTrasladoRequest $request): JsonResponse
    {
        $traslado = $this->trasladoFacade->crear(
            $request->validated()
        );

        return $this->creado('Traslado', $traslado);
    }

    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->trasladoFacade->mostrar($id));
    }

    public function update(
        ActualizarTrasladoRequest $request,
        int $id
    ): JsonResponse {
        $traslado = $this->trasladoFacade->actualizar(
            $id,
            $request->validated()
        );

        return $this->actualizado('Traslado', $traslado);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->trasladoFacade->eliminar($id);

        return $this->eliminado('Traslado');
    }
}
