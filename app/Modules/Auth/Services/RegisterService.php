<?php

namespace App\Modules\Auth\Services;

use App\Mail\UsuarioRegistradoMail;
use App\Modules\Shared\Enums\EstadoUsuario;
use App\Modules\Shared\Services\CloudinaryService;
use App\Modules\Usuarios\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class RegisterService
{
    public function __construct(
        private CloudinaryService $cloudinaryService,
        private TokenService $tokenService
    ) {}

    public function registrar(array $datos): User
    {
        $datos = $this->prepararDatos($datos);
        $publicId = $datos['foto_perfil_public_id'] ?? null;

        try {
            $usuario = DB::transaction(function () use ($datos): User {
                $usuario = User::create($datos);
                $this->tokenService->crearPendiente($usuario);
                return $usuario;
            });
        } catch (\Throwable $e) {
            $this->cloudinaryService->eliminarSinInterrumpir($publicId);
            throw $e;
        }

        $this->enviarCorreoRegistro($usuario);
        return $usuario;
    }

    /** @param  array<string, mixed>  $datos @return array<string, mixed> */
    private function prepararDatos(array $datos): array
    {
        if (($datos['foto_perfil'] ?? null) instanceof UploadedFile) {
            $imagen = $this->cloudinaryService->subir($datos['foto_perfil'], 'stockmind/perfiles');
            $datos['foto_perfil'] = $imagen['url'];
            $datos['foto_perfil_public_id'] = $imagen['public_id'];
        }

        $datos['password'] = Hash::make($datos['password']);
        $datos['estado'] = EstadoUsuario::Pendiente->value;
        unset($datos['password_confirmation']);

        return $datos;
    }

    private function enviarCorreoRegistro(User $usuario): void
    {
        try {
            Mail::to($usuario->email)->send(new UsuarioRegistradoMail($usuario));
        } catch (\Throwable $e) {
            Log::warning('No se pudo enviar correo de registro.', [
                'usuario_id' => $usuario->id,
                'email' => $usuario->email,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
