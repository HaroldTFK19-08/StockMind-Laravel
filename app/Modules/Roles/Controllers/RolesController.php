<?php
//crud Roles
namespace App\Modules\Roles\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Roles\Facades\RolFacade;
use App\Modules\Roles\Requests\ActualizarRolRequest;
use App\Modules\Roles\Requests\RegistroRolRequest;
use Illuminate\Http\JsonResponse;

class RolesController extends Controller
{
    use RespondeApi;

    public function __construct(
        private RolFacade $rolFacade
    ) {}
    public function index(): JsonResponse
    {
        return $this->listado(
            $this->rolFacade->listar()
        );
    }
    public function store(
        RegistroRolRequest $request
    ): JsonResponse {
        $rol = $this->rolFacade->crear(
            $request->validated()
        );
        return $this->creado('Rol', $rol);
    }
    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->rolFacade->mostrar($id));
    }
    public function update(
        ActualizarRolRequest $request,
        int $id
    ): JsonResponse {
        $rol = $this->rolFacade->actualizar(
            $id,
            $request->validated()
        );
        return $this->actualizado('Rol', $rol);
    }
    public function destroy(int $id): JsonResponse
    {
        $this->rolFacade->eliminar($id);
        return $this->eliminado('Rol');
    }
}
