<?php

namespace App\Modules\Categorias\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Categorias\Facades\CategoriaFacade;
use App\Modules\Categorias\Requests\ActualizarCategoriaRequest;
use App\Modules\Categorias\Requests\RegistroCategoriaRequest;
use Illuminate\Http\JsonResponse;

class CategoriasController extends Controller
{
    use RespondeApi;

    public function __construct(
        private CategoriaFacade $categoriaFacade
    ) {}

    public function index(): JsonResponse
    {
        return $this->listado(
            $this->categoriaFacade->listar()
        );
    }

    public function store(RegistroCategoriaRequest $request): JsonResponse
    {
        $categoria = $this->categoriaFacade->crear(
            $request->validated()
        );

        return $this->creado('Categoría', $categoria, true);
    }

    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->categoriaFacade->mostrar($id));
    }

    public function update(
        ActualizarCategoriaRequest $request,
        int $id
    ): JsonResponse {
        $categoria = $this->categoriaFacade->actualizar(
            $id,
            $request->validated()
        );

        return $this->actualizado('Categoría', $categoria, true);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->categoriaFacade->eliminar($id);

        return $this->eliminado('Categoría', true);
    }
}
