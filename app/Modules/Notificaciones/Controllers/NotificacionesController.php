<?php

namespace App\Modules\Notificaciones\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Notificaciones\Facades\NotificacionFacade;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class NotificacionesController extends Controller
{
    public function __construct(
        private NotificacionFacade $notificacionFacade
    ) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->notificacionFacade->listar($request->user()->id)
        );
    }

    public function contador(Request $request): JsonResponse
    {
        return response()->json(
            $this->notificacionFacade->contador($request->user()->id)
        );
    }

    public function marcarLeida(Request $request, int $notificacion): JsonResponse
    {
        return response()->json([
            'message' => 'Notificación marcada como leída.',
            'data' => $this->notificacionFacade->marcarLeida(
                $request->user()->id,
                $notificacion
            ),
        ]);
    }

    public function marcarTodasLeidas(Request $request): JsonResponse
    {
        $cantidad = $this->notificacionFacade->marcarTodasLeidas($request->user()->id);

        return response()->json([
            'message' => "Se marcaron {$cantidad} notificaciones como leídas.",
            'cantidad' => $cantidad,
        ]);
    }
}
