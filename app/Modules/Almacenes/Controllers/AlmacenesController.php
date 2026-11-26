<?php

namespace App\Modules\Almacenes\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Almacenes\Facades\AlmacenFacade;
use App\Modules\Almacenes\Requests\ActualizarAlmacenRequest;
use App\Modules\Almacenes\Requests\RegistroAlmacenRequest;
use Illuminate\Http\JsonResponse;

class AlmacenesController extends Controller
{
    use RespondeApi;

    public function __construct(
        private AlmacenFacade $almacenFacade
    ) {}

    public function index(): JsonResponse
    {
        return $this->listado(
            $this->almacenFacade->listar()
        );
    }

    public function store(RegistroAlmacenRequest $request): JsonResponse
    {
        $almacen = $this->almacenFacade->crear(
            $request->validated()
        );

        return $this->creado('Almacén', $almacen);
    }

    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->almacenFacade->mostrar($id));
    }

    public function update(
        ActualizarAlmacenRequest $request,
        int $id
    ): JsonResponse {
        $almacen = $this->almacenFacade->actualizar(
            $id,
            $request->validated()
        );

        return $this->actualizado('Almacén', $almacen);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->almacenFacade->eliminar($id);

        return $this->eliminado('Almacén');
    }
}
