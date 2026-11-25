<?php

namespace Tests\Feature;

use App\Modules\Ambientes\Models\Environment;
use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Categorias\Models\Category;
use App\Modules\Centros\Models\Trainingcenter;
use App\Modules\Elementos\Models\Element;
use App\Modules\Reportes\Facades\ReporteFacade;
use App\Modules\Roles\Models\Rol;
use App\Modules\Fichas\Models\Coursegroup;
use App\Modules\Sedes\Models\Headquarter;
use App\Modules\Shared\Enums\EstadoUsuario;
use App\Modules\Shared\Enums\TipoNotificacion;
use App\Modules\Shared\Models\Notification;
use App\Modules\Shared\Services\CloudinaryService;
use App\Modules\Shared\Services\NotificacionService;
use App\Modules\Traslados\Facades\TrasladoFacade;
use App\Modules\Usuarios\Models\Apprentice;
use App\Modules\Usuarios\Models\Danteaccount;
use App\Modules\Usuarios\Models\Teacher;
use App\Modules\Usuarios\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificacionesTest extends TestCase
{
    use RefreshDatabase;

    private int $secuencia = 0;

    public function test_asignar_un_rol_notifica_al_usuario(): void
    {
        $rol = Rol::create([
            'rol' => 'Aprendiz',
            'descripcion' => 'Aprendiz de prueba',
        ]);

        $this->postJson('/v1/auth/register', [
            'nombre_1' => 'Ana',
            'apellido_1' => 'Pérez',
            'tipo_identidad' => 'CC',
            'numero_identidad' => '1234567890',
            'telefono' => '3001234567',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertCreated();

        $usuario = User::where('email', 'ana@example.com')->firstOrFail();

        Sanctum::actingAs($this->crearUsuario('Admin'));

        $this->putJson("/v1/usuarios/{$usuario->id}", [
            'role_ids' => [$rol->id],
        ])->assertOk();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $usuario->id,
            'tipo' => TipoNotificacion::RolAsignado->value,
            'leida' => false,
        ]);
    }

    public function test_una_asignacion_notifica_al_aprendiz(): void
    {
        $usuarioAprendiz = $this->crearUsuario('Aprendiz');
        $usuarioInstructor = $this->crearUsuario('Instructor');
        $instructor = Teacher::create([
            'user_id' => $usuarioInstructor->id,
            'tipo_instructor' => 'planta',
        ]);
        $ficha = Coursegroup::create([
            'teacher_id' => $instructor->id,
            'codigo' => 'FICHA-' . $this->secuencia++,
            'programa' => 'Programa de prueba',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
        ]);
        $aprendiz = Apprentice::create([
            'user_id' => $usuarioAprendiz->id,
            'coursegroup_id' => $ficha->id,
        ]);
        $elemento = $this->crearElemento($this->crearAmbiente('Ambiente de asignación'));

        $this->postJson('/v1/asignaciones', [
            'element_id' => $elemento->id,
            'apprentice_id' => $aprendiz->id,
            'teacher_id' => $instructor->id,
            'fecha_devolucion_esperada' => now()->addWeek()->toDateString(),
            'estado' => 'activa',
        ])->assertCreated();

        $this->assertDatabaseHas('notifications', [
            'user_id' => $usuarioAprendiz->id,
            'tipo' => TipoNotificacion::AsignacionCreada->value,
            'leida' => false,
        ]);

        $notificacion = Notification::query()
            ->where('user_id', $usuarioAprendiz->id)
            ->where('tipo', TipoNotificacion::AsignacionCreada->value)
            ->firstOrFail();

        $asignacion = Assignment::query()->firstOrFail();
        $this->assertSame($asignacion->id, $notificacion->data['asignacion_id']);
    }

    public function test_un_reporte_notifica_al_cuenta_dante_del_ambiente_del_elemento(): void
    {
        $this->mock(CloudinaryService::class)
            ->shouldReceive('subir')
            ->once()
            ->andReturn([
                'url' => 'https://example.com/reporte.png',
                'public_id' => 'reportes/reporte-prueba',
            ]);

        $ambiente = $this->crearAmbiente('Ambiente con cuenta DANTE');
        $elemento = $this->crearElemento($ambiente);

        $usuarioAprendiz = $this->crearUsuario('Aprendiz');
        $usuarioDante = $this->crearUsuario('Cuentadante');
        $rolDante = Rol::create([
            'rol' => 'Cuenta-dante',
            'descripcion' => 'Cuenta dante de prueba',
        ]);
        $usuarioDante->roles()->attach($rolDante);

        Danteaccount::create([
            'user_id' => $usuarioDante->id,
            'environment_id' => $ambiente->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => null,
        ]);

        $ficha = Coursegroup::create([
            'teacher_id' => Teacher::create([
                'user_id' => $this->crearUsuario('Instructor')->id,
                'tipo_instructor' => 'planta',
            ])->id,
            'codigo' => 'FICHA-REPORTE',
            'programa' => 'Programa de prueba',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
        ]);
        $aprendiz = Apprentice::create([
            'user_id' => $usuarioAprendiz->id,
            'coursegroup_id' => $ficha->id,
        ]);

        $reporte = $this->app->make(ReporteFacade::class)->crear([
            'apprentice_id' => $aprendiz->id,
            'tipo' => 'falla',
            'descripcion' => 'El equipo no enciende.',
            'foto_probatorio' => UploadedFile::fake()->create('reporte.png', 100, 'image/png'),
            'element_ids' => [$elemento->id],
        ]);

        $notificacion = Notification::query()
            ->where('user_id', $usuarioDante->id)
            ->where('tipo', TipoNotificacion::ReporteCreado->value)
            ->firstOrFail();

        $this->assertSame($reporte->id, $notificacion->data['reporte_id']);
        $this->assertSame($elemento->id, $notificacion->data['elementos'][0]['id']);
    }

    public function test_completar_un_traslado_notifica_al_cuenta_dante_del_nuevo_ambiente(): void
    {
        $usuarioDante = $this->crearUsuario('Cuentadante');
        $rolDante = Rol::create([
            'rol' => 'Cuenta-dante',
            'descripcion' => 'Cuenta dante de prueba',
        ]);
        $usuarioDante->roles()->attach($rolDante);

        $origen = $this->crearAmbiente('Ambiente origen');
        $destino = $this->crearAmbiente('Ambiente destino');
        $cuenta = Danteaccount::create([
            'user_id' => $usuarioDante->id,
            'environment_id' => $origen->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => null,
        ]);
        $elemento = $this->crearElemento($origen, 'ELEM-TRASLADO');

        $this->app->make(TrasladoFacade::class)->crear([
            'ambiente_origen_id' => $origen->id,
            'ambiente_destino_id' => $destino->id,
            'danteaccount_id' => $cuenta->id,
            'motivo' => 'Traslado de prueba',
            'fecha_solicitud' => now()->toDateString(),
            'estado' => 'completado',
            'element_ids' => [$elemento->id],
        ]);

        $notificacion = Notification::query()
            ->where('user_id', $usuarioDante->id)
            ->where('tipo', TipoNotificacion::AmbienteAsignado->value)
            ->firstOrFail();

        $this->assertSame($destino->id, $notificacion->data['ambiente_id']);
    }

    public function test_la_api_de_notificaciones_lista_cuenta_y_marca_como_leidas(): void
    {
        $usuario = $this->crearUsuario('Aprendiz');
        $notificacion = $this->app->make(NotificacionService::class)->aUsuario(
            $usuario->id,
            'Prueba',
            'Mensaje de prueba',
            TipoNotificacion::General
        );

        Sanctum::actingAs($usuario);

        $this->getJson('/v1/notificaciones')
            ->assertOk()
            ->assertJsonPath('data.0.id', $notificacion->id);

        $this->getJson('/v1/notificaciones/contador')
            ->assertOk()
            ->assertJsonPath('sin_leer', 1);

        $this->patchJson("/v1/notificaciones/{$notificacion->id}/leida")
            ->assertOk();

        $this->assertDatabaseHas('notifications', [
            'id' => $notificacion->id,
            'leida' => true,
        ]);
    }

    private function crearUsuario(string $prefijo): User
    {
        $this->secuencia++;

        return User::create([
            'nombre_1' => $prefijo,
            'apellido_1' => 'Prueba',
            'tipo_identidad' => 'CC',
            'numero_identidad' => (string) (9000000000 + $this->secuencia),
            'telefono' => (string) (3000000000 + $this->secuencia),
            'email' => strtolower($prefijo) . $this->secuencia . '@example.com',
            'password' => 'password123',
            'estado' => EstadoUsuario::Activo->value,
        ]);
    }

    private function crearAmbiente(string $nombre): Environment
    {
        $centro = Trainingcenter::create([
            'nombre' => 'Centro ' . $nombre,
            'regional' => 'Cauca',
            'ciudad' => 'Popayán',
        ]);
        $sede = Headquarter::create([
            'trainingcenter_id' => $centro->id,
            'nombre' => 'Sede ' . $nombre,
            'direccion' => 'Dirección de prueba',
            'zona' => 'urbana',
        ]);

        return Environment::create([
            'headquarter_id' => $sede->id,
            'nombre' => $nombre,
            'capacidad' => 10,
            'tipo' => 'sala de sistemas',
            'estado' => 'activo',
        ]);
    }

    private function crearElemento(Environment $ambiente, string $codigo = 'ELEM-PRUEBA'): Element
    {
        $categoria = Category::create([
            'nombre' => 'Categoría ' . $codigo,
            'descripcion' => 'Categoría de prueba',
        ]);

        return Element::create([
            'category_id' => $categoria->id,
            'environment_id' => $ambiente->id,
            'codigo_inventario' => $codigo . '-' . $this->secuencia++,
            'nombre' => 'Elemento de prueba',
            'img_elemento' => 'https://example.com/elemento.png',
            'estado' => 'disponible',
            'condicion' => 'bueno',
        ]);
    }
}
