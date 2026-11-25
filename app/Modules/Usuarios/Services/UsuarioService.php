<?php

namespace App\Modules\Usuarios\Services;

use App\Modules\Auth\Facades\AuthFacade;
use App\Modules\Roles\Facades\RolFacade;
use App\Modules\Shared\Enums\EstadoUsuario;
use App\Modules\Shared\Enums\TipoNotificacion;
use App\Modules\Shared\Exceptions\ReglasNegocioExceptions;
use App\Modules\Shared\Services\CloudinaryService;
use App\Modules\Shared\Services\NotificacionService;
use App\Modules\Roles\Models\Rol;
use App\Modules\Usuarios\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

class UsuarioService
{
    public function __construct(
        private CloudinaryService $cloudinaryService,
        private AuthFacade $authFacade,
        private RolFacade $rolFacade,
        private NotificacionService $notificacionService
    ) {}

    public function listar()
    {
        return User::with([
            'roles',
            'apprentice.coursegroup',
            'teacher',
            'danteaccounts.environment',
        ])
            ->withCount('danteaccounts')
            ->latest()
            ->paginate(10);
    }

    public function mostrar(int $id): User
    {
        return User::with([
            'roles',
            'apprentice.coursegroup',
            'teacher',
            'danteaccounts.environment',
        ])
            ->withCount('danteaccounts')
            ->findOrFail($id);
    }

    public function crear(array $datos): User
    {
        $foto = $datos['foto_perfil'];
        $roleIds = $datos['role_ids'];
        unset($datos['foto_perfil'], $datos['role_ids'], $datos['password_confirmation']);

        $rolesAsignados = $this->rolFacade->filtrarParaAsignacion($roleIds);

        $imagen = $this->cloudinaryService->subir($foto, 'usuarios');

        try {
            $usuario = DB::transaction(function () use ($datos, $roleIds, $rolesAsignados, $imagen) {
                $usuario = User::create([
                    ...$datos,
                    'foto_perfil' => $imagen['url'],
                    'foto_perfil_public_id' => $imagen['public_id'],
                ]);
                $usuario->roles()->sync($roleIds);

                $this->notificarRolesAsignados($usuario, $rolesAsignados);

                return $usuario;
            });
        } catch (Throwable $e) {
            $this->eliminarImagenSinInterrumpir($imagen['public_id']);
            throw $e;
        }

        return $this->mostrar($usuario->id);
    }

    /**
     * @return array{usuario: User, token: string|null}
     */
    public function actualizar(int $id, array $datos): array
    {
        $usuario = $this->mostrar($id);
        $publicIdAnterior = $usuario->foto_perfil_public_id;
        $foto = $datos['foto_perfil'] ?? null;
        $roleIds = $datos['role_ids'] ?? null;
        unset($datos['foto_perfil'], $datos['role_ids'], $datos['password_confirmation']);
        $imagenNueva = null;
        $rolesAsignados = null;
        $rolesAnteriores = $usuario->roles()->pluck('rol')->sort()->values()->all();

        if ($roleIds !== null) {
            $rolesAsignados = $this->rolFacade->filtrarParaAsignacion($roleIds);
        }

        if ($foto instanceof UploadedFile) {
            $imagenNueva = $this->cloudinaryService->subir($foto, 'usuarios');
            $datos['foto_perfil'] = $imagenNueva['url'];
            $datos['foto_perfil_public_id'] = $imagenNueva['public_id'];
        }

        // Asignar el rol a una cuenta pendiente la activa y entrega su token.
        $activarAlAsignarRol = $roleIds !== null
            && $usuario->estado === EstadoUsuario::Pendiente->value
            && ($datos['estado'] ?? null) !== EstadoUsuario::Inactivo->value;

        if ($activarAlAsignarRol) {
            $datos['estado'] = EstadoUsuario::Activo->value;
        }

        try {
            $tokenNuevo = DB::transaction(function () use (
                $usuario,
                $datos,
                $roleIds,
                $rolesAsignados,
                $rolesAnteriores,
                $activarAlAsignarRol
            ) {
                $usuario->update($datos);

                if ($roleIds !== null) {
                    $this->validarRolesDePerfiles($usuario, $roleIds);
                    $usuario->roles()->sync($roleIds);

                    if ($rolesAsignados !== $rolesAnteriores) {
                        $this->notificarRolesAsignados($usuario, $rolesAsignados);
                    }
                }

                if (($datos['estado'] ?? null) === EstadoUsuario::Inactivo->value) {
                    $usuario->tokens()->delete();
                }

                return $activarAlAsignarRol
                    ? $this->authFacade->activarCuenta($usuario)
                    : null;
            });
        } catch (Throwable $e) {
            if ($imagenNueva !== null) {
                $this->eliminarImagenSinInterrumpir($imagenNueva['public_id']);
            }

            throw $e;
        }

        if ($imagenNueva !== null && $publicIdAnterior) {
            $this->eliminarImagenSinInterrumpir($publicIdAnterior);
        }

        return [
            'usuario' => $this->mostrar($id),
            'token' => $tokenNuevo,
        ];
    }

    public function eliminar(int $id): void
    {
        $usuario = $this->mostrar($id);

        if (
            $usuario->apprentice()->exists()
            || $usuario->teacher()->exists()
            || $usuario->danteaccounts()->exists()
        ) {
            throw new ReglasNegocioExceptions(
                'No se puede eliminar el usuario porque tiene perfiles o cuentas DANTE asociados.'
            );
        }

        $publicId = $usuario->foto_perfil_public_id;
        $usuario->delete();

        if ($publicId) {
            $this->eliminarImagenSinInterrumpir($publicId);
        }
    }

    /**
     * Avisa al usuario cuando un administrador cambia sus roles (DSMR-07).
     *
     * @param  array<int, string>  $roles
     */
    private function notificarRolesAsignados(User $usuario, array $roles): void
    {
        if ($roles === []) {
            return;
        }

        $this->notificacionService->aUsuario(
            $usuario->id,
            'Tus roles han sido actualizados',
            'Se te asignaron los roles: ' . implode(', ', $roles) . '.',
            TipoNotificacion::RolAsignado,
            [
                'usuario_id' => $usuario->id,
                'roles' => $roles,
            ]
        );
    }

    private function eliminarImagenSinInterrumpir(string $publicId): void
    {
        try {
            $this->cloudinaryService->eliminar($publicId);
        } catch (Throwable $e) {
            report($e);
        }
    }

    private function validarRolesDePerfiles(User $usuario, array $roleIds): void
    {
        $rolesNecesarios = [];

        if ($usuario->apprentice()->exists()) {
            $rolesNecesarios[] = 'Aprendiz';
        }

        if ($usuario->teacher()->exists()) {
            $rolesNecesarios[] = 'Instructor';
        }

        if ($usuario->danteaccounts()->exists()) {
            $rolesNecesarios[] = 'Cuenta-dante';
        }

        if ($rolesNecesarios === []) {
            return;
        }

        $rolesIncluidos = Rol::query()
            ->whereIn('id', $roleIds)
            ->whereIn('rol', $rolesNecesarios)
            ->pluck('rol')
            ->all();

        if (count($rolesIncluidos) !== count($rolesNecesarios)) {
            throw new ReglasNegocioExceptions(
                'No se pueden quitar roles que corresponden a perfiles o cuentas DANTE existentes.'
            );
        }
    }
}
