<?php

namespace App\Modules\Usuarios\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Shared\Controllers\Concerns\RespondeApi;
use App\Modules\Usuarios\Facades\UsuarioFacade;
use App\Modules\Usuarios\Requests\ActualizarUsuarioRequest;
use App\Modules\Usuarios\Requests\RegistroUsuarioRequest;
use Illuminate\Http\JsonResponse;

class UserController extends Controller
{
    use RespondeApi;

    public function __construct(
        private UsuarioFacade $usuarioFacade
    ) {}

    public function index(): JsonResponse
    {
        return $this->listado(
            $this->usuarioFacade->listar()
        );
    }

    public function indexConPerfiles(): JsonResponse
    {
        // Endpoint de consultas de kevin: mantiene la clave "data" de su contrato.
        return response()->json([
            'data' => $this->usuarioFacade->listarConPerfiles(),
        ]);
    }
    //parte de registro
    public function store(RegistroUsuarioRequest $request): JsonResponse
    {
        $usuario = $this->usuarioFacade->crear(
            $request->validated()
        );

        return $this->creado('Usuario', $usuario);
    }

    public function show(int $id): JsonResponse
    {
        return $this->detalle($this->usuarioFacade->mostrar($id));
    }

    public function update(
        ActualizarUsuarioRequest $request,
        int $id
    ): JsonResponse {
        $resultado = $this->usuarioFacade->actualizar(
            $id,
            $request->validated()
        );

        $respuesta = [
            'message' => 'Usuario actualizado correctamente.',
            'data' => $resultado['usuario'],
        ];

        if ($resultado['token'] !== null) {
            $respuesta['message'] = 'Rol asignado y cuenta activada. Entrega este token al usuario.';
            $respuesta['token'] = $resultado['token'];
        }

        return response()->json($respuesta);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->usuarioFacade->eliminar($id);

        return $this->eliminado('Usuario');
    }
}
