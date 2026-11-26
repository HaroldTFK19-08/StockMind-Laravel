<?php
//crud centros
namespace App\Modules\Centros\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Centros\Facades\CentroFacade;
use App\Modules\Centros\Requests\RegistroCentroRequest;
use App\Modules\Centros\Requests\ActualizarCentroRequest;
use Illuminate\Http\JsonResponse;

class CentrosController extends Controller
{
    use RespondeApi;

    public function __construct(
        private CentroFacade $centroFacade
    ) {}
    public function index(): JsonResponse
    {
        return $this->listado(
            $this->centroFacade->listar()
        );
    }
    public function store(
        RegistroCentroRequest $request
    ): JsonResponse {
        $centro = $this->centroFacade->crear(
            $request->validated()
        );
        return $this->creado('Centro', $centro);
    }
    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->centroFacade->mostrar($id));
    }
    public function update(
        ActualizarCentroRequest $request,
        int $id
    ): JsonResponse {
        $centro = $this->centroFacade->actualizar(
            $id,
            $request->validated()
        );
        return $this->actualizado('Centro', $centro);
    }
    public function destroy(int $id): JsonResponse
    {
        $this->centroFacade->eliminar($id);
        return $this->eliminado('Centro');
    }
}
