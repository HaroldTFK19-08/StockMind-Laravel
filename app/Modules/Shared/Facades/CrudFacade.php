<?php

namespace App\Modules\Shared\Facades;

/**
 * Fachada base para los módulos con CRUD estándar.
 *
 * Sólo reenvía al servicio inyectado: la lógica de negocio sigue viviendo en
 * cada Service y el patrón controlador -> fachada -> servicio se mantiene.
 */
abstract class CrudFacade
{
    public function __construct(protected object $servicio) {}

    public function listar(): mixed
    {
        return $this->servicio->listar();
    }

    public function mostrar(int $id): mixed
    {
        return $this->servicio->mostrar($id);
    }

    public function crear(array $datos): mixed
    {
        return $this->servicio->crear($datos);
    }

    public function actualizar(int $id, array $datos): mixed
    {
        return $this->servicio->actualizar($id, $datos);
    }

    public function eliminar(int $id): void
    {
        $this->servicio->eliminar($id);
    }
}
