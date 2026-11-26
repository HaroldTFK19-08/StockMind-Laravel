<?php

namespace App\Modules\Fichas\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Fichas\Facades\FichaFacade;
use App\Modules\Fichas\Requests\ActualizarFichaRequest;
use App\Modules\Fichas\Requests\RegistroFichaRequest;
use Illuminate\Http\JsonResponse;

class FichasController extends Controller
{
    use RespondeApi;

    public function __construct(
        private FichaFacade $fichaFacade
    ) {}

    public function index(): JsonResponse
    {
        return $this->listado(
            $this->fichaFacade->listar()
        );
    }

    public function store(RegistroFichaRequest $request): JsonResponse
    {
        $ficha = $this->fichaFacade->crear(
            $request->validated()
        );

        return $this->creado('Ficha', $ficha, true);
    }

    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->fichaFacade->mostrar($id));
    }

    public function update(
        ActualizarFichaRequest $request,
        int $id
    ): JsonResponse {
        $ficha = $this->fichaFacade->actualizar(
            $id,
            $request->validated()
        );

        return $this->actualizado('Ficha', $ficha, true);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->fichaFacade->eliminar($id);

        return $this->eliminado('Ficha', true);
    }
}
