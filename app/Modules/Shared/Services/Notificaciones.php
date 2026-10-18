<?php

namespace App\Modules\Shared\Services;

use App\Modules\Shared\Models\Notification;
use App\Modules\Usuarios\Models\User;

/**
 * Subsistema compartido: crea notificaciones internas.
 */
class NotificacionService
{
    public function aUsuario(int $usuarioId, string $titulo, string $mensaje): Notification
    {
        return Notification::create([
            'usuario_id' => $usuarioId,
            'titulo'     => $titulo,
            'mensaje'    => $mensaje,
            'leida'      => false,
        ]);
    }

    public function aRol(string $rol, string $titulo, string $mensaje): void
    {
        User::whereHas('roles', fn ($q) => $q->where('nombre', $rol))
            ->where('estado', 'activo')
            ->pluck('id')
            ->each(fn ($id) => $this->aUsuario($id, $titulo, $mensaje));
    }

    public function marcarLeida(Notification $notificacion): void
    {
        $notificacion->update(['leida' => true]);
    }
}