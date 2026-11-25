<?php

namespace App\Modules\Auth\Services;

use App\Modules\Shared\Enums\EstadoUsuario;
use App\Modules\Shared\Services\CloudinaryService;
use App\Modules\Usuarios\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class RegisterService
{
    public function __construct(
        private CloudinaryService $cloudinaryService,
        private TokenService $tokenService
    ) {}

    public function registrar(array $datos): User
    {
        // Si mandó foto de perfil, se sube a Cloudinary
        if (
            isset($datos['foto_perfil']) &&
            $datos['foto_perfil'] instanceof UploadedFile
        ) {
            $imagen = $this->cloudinaryService->subir(
                $datos['foto_perfil'],
                'stockmind/perfiles'
            );
            $datos['foto_perfil'] = $imagen['url'];
            $datos['foto_perfil_public_id'] = $imagen['public_id'];
        }

        $datos['password'] = Hash::make($datos['password']);
        $datos['estado'] = EstadoUsuario::Pendiente->value;
        // Usuario y token se crean juntos para no dejar registros huérfanos.
        return DB::transaction(function () use ($datos): User {
            $usuario = User::create($datos);
            // El token queda en BD, pero no se entrega hasta que un
            // administrador le asigne un rol (AuthFacade::activarCuenta).
            $this->tokenService->crearPendiente($usuario);

            return $usuario;
        });
    }
}
