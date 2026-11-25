<?php

namespace App\Modules\Shared\Services;

use App\Modules\Ambientes\Models\Environment;
use App\Modules\Shared\Enums\EstadoUsuario;
use App\Modules\Shared\Enums\TipoNotificacion;
use App\Modules\Shared\Models\Notification;
use App\Modules\Usuarios\Models\Danteaccount;
use App\Modules\Usuarios\Models\User;

/**
 * Subsistema compartido: crea notificaciones internas y resuelve destinatarios.
 */
class NotificacionService
{
    public function aUsuario(
        int $usuarioId,
        string $titulo,
        string $mensaje,
        TipoNotificacion|string $tipo = TipoNotificacion::General,
        array $data = []
    ): Notification {
        return Notification::create([
            'user_id' => $usuarioId,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'tipo' => $tipo instanceof TipoNotificacion ? $tipo->value : $tipo,
            'leida' => false,
            'data' => $data === [] ? null : $data,
        ]);
    }

    /**
     * Crea una notificación por cada usuario activo indicado, sin duplicados.
     *
     * @param  array<int, int|string|null>  $usuarioIds
     * @param  array<int, int|string|null>  $exceptuar
     * @return \Illuminate\Support\Collection<int, Notification>
     */
    public function aUsuarios(
        array $usuarioIds,
        string $titulo,
        string $mensaje,
        TipoNotificacion|string $tipo = TipoNotificacion::General,
        array $data = [],
        array $exceptuar = []
    ) {
        $ids = array_values(array_unique(array_filter(array_map(
            static fn ($id) => is_numeric($id) ? (int) $id : null,
            $usuarioIds
        ))));
        $excluidos = array_map('intval', array_filter($exceptuar, 'is_numeric'));
        $ids = array_diff($ids, $excluidos);

        if ($ids === []) {
            return collect();
        }

        return User::query()
            ->whereIn('id', $ids)
            ->where('estado', EstadoUsuario::Activo->value)
            ->pluck('id')
            ->map(fn (int $id) => $this->aUsuario($id, $titulo, $mensaje, $tipo, $data));
    }

    /**
     * @param  string|array<int, string>  $roles
     */
    public function aRol(
        string|array $roles,
        string $titulo,
        string $mensaje,
        TipoNotificacion|string $tipo = TipoNotificacion::General,
        array $data = []
    ) {
        return $this->aUsuarios(
            $this->idsConRol($roles),
            $titulo,
            $mensaje,
            $tipo,
            $data
        );
    }

    /**
     * @return array<int, int>
     */
    public function idsConRol(string|array $roles): array
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return User::query()
            ->whereHas('roles', fn ($query) => $query->whereIn('rol', $roles))
            ->where('estado', EstadoUsuario::Activo->value)
            ->pluck('id')
            ->all();
    }

    /**
     * Cuentadantes vigentes (y activos) a cargo de un ambiente.
     *
     * @return array<int, int>
     */
    public function idsCuentadantesDeAmbiente(int $ambienteId): array
    {
        return Danteaccount::query()
            ->where('environment_id', $ambienteId)
            ->whereNull('fecha_fin')
            ->whereHas('user', function ($query) {
                $query->where('estado', EstadoUsuario::Activo->value)
                    ->whereHas('roles', fn ($roles) => $roles->where('rol', 'Cuenta-dante'));
            })
            ->pluck('user_id')
            ->all();
    }

    /**
     * Instructor responsable de un ambiente, si lo tiene y está activo.
     *
     * @return array<int, int>
     */
    public function idsInstructoresDeAmbiente(int $ambienteId): array
    {
        $ambiente = Environment::query()
            ->with('teacher.user')
            ->find($ambienteId);

        $usuarioId = $ambiente?->teacher?->user?->id;

        if ($usuarioId === null || $ambiente->teacher->user->estado !== EstadoUsuario::Activo->value) {
            return [];
        }

        return [$usuarioId];
    }

    public function marcarLeida(Notification $notificacion): void
    {
        $notificacion->update(['leida' => true]);
    }

    public function marcarTodasLeidas(int $usuarioId): int
    {
        return Notification::query()
            ->where('user_id', $usuarioId)
            ->where('leida', false)
            ->update(['leida' => true]);
    }
}
