<?php

namespace App\Modules\Shared\Services;

use Closure;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Throwable;

/**
 * Subsistema compartido: caché de consultas en Redis.
 *
 * Solo se cachean los módulos con mucho tráfico de lectura:
 *   - elementos (el corazón del inventario)
 *   - catálogos que casi no cambian y se piden en cada pantalla y formulario:
 *     categorias, centros, sedes, ambientes y almacenes
 *
 * NO se cachean asignaciones, préstamos, traslados, movimientos, reportes,
 * usuarios, roles ni fichas: cambian seguido o se consultan poco, así que
 * guardarlos gastaría RAM sin ahorrar trabajo real a MySQL.
 *
 * Cómo se invalida: cada módulo tiene un número de versión guardado en Redis
 * y ese número forma parte de la clave. Cuando alguien escribe, se sube la
 * versión y las claves viejas dejan de usarse (Redis las borra solas al vencer
 * su TTL). Así no hace falta recorrer ni borrar claves una por una.
 */
class CacheService
{
    // Tiempo de vida en segundos
    public const TTL_CATALOGO = 3600; // 1 hora: categorías, centros, sedes, ambientes, almacenes
    public const TTL_ELEMENTOS = 600; // 10 minutos: elementos cambian de estado con frecuencia
    public const TTL_CONSULTA = 120;  // 2 minutos: consultas filtradas, bajo volumen en RAM
    // Solo se guardan las primeras páginas de cada listado (las más visitadas).
    // De la página 6 en adelante se consulta directo a MySQL para no llenar la RAM.
    private const PAGINAS_CACHEADAS = 5;
    /**
     * Módulo que escribe => módulos cacheados que quedan desactualizados.
     *
     * Ejemplo: al crear una asignación cambia el estado del elemento y su
     * contador de asignaciones, así que hay que invalidar "elementos".
     */
    private const DEPENDENCIAS = [
        'elementos'    => ['elementos', 'categorias', 'ambientes', 'almacenes', 'consultas'],
        'categorias'   => ['categorias', 'elementos', 'consultas'],
        'centros'      => ['centros', 'sedes', 'consultas'],
        'sedes'        => ['sedes', 'ambientes', 'almacenes', 'consultas'],
        'ambientes'    => ['ambientes', 'sedes', 'elementos', 'consultas'],
        'almacenes'    => ['almacenes', 'sedes', 'elementos', 'consultas'],
        'asignaciones' => ['elementos', 'consultas'],
        'prestamos'    => ['elementos', 'consultas'],
        'traslados'    => ['elementos', 'ambientes', 'almacenes', 'consultas'],
        'movimientos'  => ['elementos', 'consultas'],
        'reportes'     => ['elementos', 'consultas'],
        'usuarios'     => ['consultas'],
        'roles'        => ['consultas'],
        'fichas'       => ['consultas'],
    ];
    /**
     * Devuelve el valor guardado en Redis; si no existe, ejecuta la consulta
     * y guarda el resultado. La consulta debe devolver un array (no modelos),
     * porque los arrays ocupan mucha menos memoria en Redis.
     */
    public function recordar(string $modulo, string $clave, int $ttl, Closure $consulta): mixed
    {
        try {
            $claveCompleta = $this->clave($modulo, $clave);
            $valor = Cache::get($claveCompleta);
        } catch (Throwable $e) {
            // Si Redis falla, la API sigue funcionando contra MySQL.
            report($e);
            return $consulta();
        }
        if ($valor !== null) {
            return $valor;
        }
        // Si la consulta lanza una excepción (por ejemplo un 404), no se guarda nada.
        $valor = $consulta();
        try {
            Cache::put($claveCompleta, $valor, $ttl);
        } catch (Throwable $e) {
            report($e);
        }
        return $valor;
    }
    /**
     * Igual que recordar(), pero para listados paginados.
     * La consulta debe devolver el query de Eloquent SIN paginar.
     *
     * En Redis solo se guardan los registros y el total; los enlaces de
     * paginación se arman en cada petición, así sirven igual para la web
     * y para la app móvil aunque entren por direcciones distintas.
     *
     * $claveBase permite separar cachés cuando el mismo módulo tiene filtros
     * distintos (ej. consultas). Debe ser un string corto y determinista.
     */
    public function recordarPagina(
        string $modulo,
        int $ttl,
        int $porPagina,
        Closure $consulta,
        string $claveBase = 'lista',
        string $pageName = 'page'
    ): LengthAwarePaginator {
        $pagina = Paginator::resolveCurrentPage($pageName);
        if ($pagina > self::PAGINAS_CACHEADAS) {
            return $consulta()->paginate($porPagina, ['*'], $pageName);
        }
        $datos = $this->recordar(
            $modulo,
            "{$claveBase}:{$pageName}:p{$pagina}:n{$porPagina}",
            $ttl,
            function () use ($consulta, $porPagina, $pageName) {
                $paginador = $consulta()->paginate($porPagina, ['*'], $pageName);
                return [
                    'items' => $paginador->getCollection()->toArray(),
                    'total' => $paginador->total(),
                ];
            }
        );
        return new LengthAwarePaginator(
            $datos['items'],
            $datos['total'],
            $porPagina,
            $pagina,
            ['path' => Paginator::resolveCurrentPath(), 'pageName' => $pageName]
        );
    }
    /**
     * Se llama después de crear, actualizar o eliminar.
     * Recibe el módulo que escribió e invalida todo lo que depende de él.
     *
     * Usa afterCommit: si hay una transacción abierta espera a que termine
     * (y si hace rollback no invalida nada); si no hay, se ejecuta de inmediato.
     */
    public function invalidar(string $moduloQueEscribe): void
    {
        $modulos = self::DEPENDENCIAS[$moduloQueEscribe] ?? [$moduloQueEscribe];
        DB::afterCommit(function () use ($modulos) {
            foreach ($modulos as $modulo) {
                try {
                    Cache::increment($this->claveVersion($modulo));
                } catch (Throwable $e) {
                    report($e);
                }
            }
        });
    }
    private function clave(string $modulo, string $clave): string
    {
        $version = (int) Cache::get($this->claveVersion($modulo), 0);
        return "sm:{$modulo}:v{$version}:{$clave}";
    }
    private function claveVersion(string $modulo): string
    {
        return "sm:{$modulo}:version";
    }
}
