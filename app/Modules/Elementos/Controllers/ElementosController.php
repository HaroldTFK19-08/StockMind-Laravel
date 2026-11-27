<?php

namespace App\Modules\Elementos\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Elementos\Facades\ElementoFacade;
use App\Modules\Elementos\Requests\ActualizarElementoRequest;
use App\Modules\Elementos\Requests\RegistroElementoRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ElementosController extends Controller
{
    use RespondeApi;

    public function __construct(
        private ElementoFacade $elementoFacade
    ) {}

    public function index(): JsonResponse
    {
        return $this->listado(
            $this->elementoFacade->listar()
        );
    }

    public function historial(string $codigo): JsonResponse
    {
        return response()->json([
            'data' => $this->elementoFacade->historial($codigo),
        ]);
    }

    public function inventarioPorSedeAlmacenCategoria(): JsonResponse
    {
        return response()->json([
            'data' => $this->elementoFacade->inventarioPorSedeAlmacenCategoria(),
        ]);
    }

    public function store(RegistroElementoRequest $request): JsonResponse
    {
        $elemento = $this->elementoFacade->crear(
            $request->validated()
        );

        return $this->creado('Elemento', $elemento);
    }

    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->elementoFacade->mostrar($id));
    }

    public function update(
        ActualizarElementoRequest $request,
        int $id
    ): JsonResponse {
        $elemento = $this->elementoFacade->actualizar(
            $id,
            $request->validated()
        );

        return $this->actualizado('Elemento', $elemento);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->elementoFacade->eliminar($id);

        return $this->eliminado('Elemento');
    }

    public function pdfVer(): Response
    {
        return $this->elementoFacade->pdfVer();
    }

    public function pdfDescargar(): Response
    {
        return $this->elementoFacade->pdfDescargar();
    }
}
