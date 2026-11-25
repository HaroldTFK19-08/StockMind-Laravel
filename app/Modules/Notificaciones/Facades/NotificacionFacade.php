<?php

namespace App\Modules\Notificaciones\Facades;

use App\Modules\Notificaciones\Services\NotificacionConsultaService;
use App\Modules\Shared\Models\Notification;

class NotificacionFacade
{
    public function __construct(
        private NotificacionConsultaService $notificacionConsultaService
    ) {}

    public function listar(int $usuarioId)
    {
        return $this->notificacionConsultaService->listar($usuarioId);
    }

    public function contador(int $usuarioId): array
    {
        return $this->notificacionConsultaService->contador($usuarioId);
    }

    public function marcarLeida(int $usuarioId, int $notificacionId): Notification
    {
        return $this->notificacionConsultaService->marcarLeida($usuarioId, $notificacionId);
    }

    public function marcarTodasLeidas(int $usuarioId): int
    {
        return $this->notificacionConsultaService->marcarTodasLeidas($usuarioId);
    }
}
