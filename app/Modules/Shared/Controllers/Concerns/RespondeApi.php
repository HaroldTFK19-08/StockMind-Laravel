<?php

namespace App\Modules\Shared\Controllers\Concerns;

use Illuminate\Http\JsonResponse;

/**
 * Respuestas JSON estándar del CRUD.
 *
 * Conserva las claves actuales: index sin envolver, show con "data",
 * creación con 201 y mensajes con género configurable.
 */
trait RespondeApi
{
    /** Listados paginados se entregan tal cual. */
    protected function listado(mixed $datos): JsonResponse
    {
        return response()->json($datos);
    }

    /** Un único recurso siempre viaja dentro de "data". */
    protected function detalle(mixed $datos): JsonResponse
    {
        return response()->json(['data' => $datos]);
    }

    protected function creado(string $entidad, mixed $datos, bool $femenino = false): JsonResponse
    {
        return $this->accion('registrad', $entidad, $datos, $femenino, 201);
    }

    protected function actualizado(string $entidad, mixed $datos, bool $femenino = false): JsonResponse
    {
        return $this->accion('actualizad', $entidad, $datos, $femenino, 200);
    }

    protected function eliminado(string $entidad, bool $femenino = false): JsonResponse
    {
        return $this->accion('eliminad', $entidad, null, $femenino, 200);
    }

    /** Construye el mensaje y evita repetir response()->json en cada controlador. */
    private function accion(
        string $verbo,
        string $entidad,
        mixed $datos,
        bool $femenino,
        int $codigo
    ): JsonResponse {
        $terminacion = $femenino ? 'a' : 'o';
        $cuerpo = ['message' => "{$entidad} {$verbo}{$terminacion} correctamente."];

        if ($datos !== null) {
            $cuerpo['data'] = $datos;
        }

        return response()->json($cuerpo, $codigo);
    }
}
