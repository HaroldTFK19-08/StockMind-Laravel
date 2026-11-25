<?php

namespace App\Modules\Notificaciones\Services;

use App\Modules\Shared\Models\Notification;
use App\Modules\Shared\Services\NotificacionService;

class NotificacionConsultaService
{
    public function __construct(
        private NotificacionService $notificacionService
    ) {}

    public function listar(int $usuarioId)
    {
        return Notification::query()
            ->where('user_id', $usuarioId)
            ->latest()
            ->paginate(15);
    }

    public function contador(int $usuarioId): array
    {
        return [
            'sin_leer' => Notification::query()
                ->where('user_id', $usuarioId)
                ->where('leida', false)
                ->count(),
        ];
    }

    public function marcarLeida(int $usuarioId, int $notificacionId): Notification
    {
        $notificacion = Notification::query()
            ->where('user_id', $usuarioId)
            ->findOrFail($notificacionId);

        $this->notificacionService->marcarLeida($notificacion);

        return $notificacion->refresh();
    }

    public function marcarTodasLeidas(int $usuarioId): int
    {
        return $this->notificacionService->marcarTodasLeidas($usuarioId);
    }
}
