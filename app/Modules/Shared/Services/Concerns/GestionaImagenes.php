<?php

namespace App\Modules\Shared\Services\Concerns;

use Closure;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Patrón común de los servicios que guardan imágenes en Cloudinary:
 * subir la imagen, ejecutar la transacción y limpiar si algo falla.
 *
 * Cada servicio entrega la lógica de negocio en un closure, así que la
 * responsabilidad de cada módulo no se mezcla con el manejo de archivos.
 */
trait GestionaImagenes
{
    /**
     * @param  Closure(array<string, mixed>|null): mixed  $operacion
     */
    protected function transaccionConImagen(
        ?UploadedFile $archivo,
        string $carpeta,
        Closure $operacion,
        ?string $publicIdAnterior = null
    ): mixed {
        $imagen = $archivo instanceof UploadedFile
            ? $this->cloudinaryService->subir($archivo, $carpeta)
            : null;

        try {
            $resultado = DB::transaction(fn () => $operacion($imagen));
        } catch (Throwable $e) {
            // Rollback de la imagen recién subida; nunca interrumpe el error original.
            $this->cloudinaryService->eliminarSinInterrumpir($imagen['public_id'] ?? null);
            throw $e;
        }

        // La imagen anterior se borra sólo si la nueva quedó persistida.
        if ($imagen !== null && $publicIdAnterior) {
            $this->cloudinaryService->eliminarSinInterrumpir($publicIdAnterior);
        }

        return $resultado;
    }
}
