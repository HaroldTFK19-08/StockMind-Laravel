<?php

namespace App\Modules\Auth\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Facades\UserFacade;
use App\Modules\Auth\Requests\RegisterRequest;
use App\Modules\Auth\Facades\LoginFacade;
use App\Modules\Auth\Facades\RegisterFacade;
use App\Modules\Auth\Requests\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Js;

class AuthController extends Controller
{
    public function register(RegisterRequest $request): JsonResponse
    {
        $usuario = RegisterFacade::registrar(
            $request->validated()
        );
        return response()->json([
            "mensaje" => "Usuario creado correctamente",
            "usuario" => $usuario
        ]);
    }
    public function login(LoginRequest $request): JsonResponse
    {
        $login = LoginFacade::login(
            $request->validated()
        );
        return response()->json([
            'mensaje' => $login['mensaje'],
            'usuario' => $login['usuario'],
        ], 200);
    }
    public function logout(Request $request): JsonResponse
    {
        LoginFacade::logout();

        return response()->json([
            'mensaje' => 'Sesión cerrada correctamente.',
        ], 200);
    }
}
