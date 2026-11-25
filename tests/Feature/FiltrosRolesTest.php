<?php

namespace Tests\Feature;

use App\Http\Middleware\RolCuentadante;
use App\Http\Middleware\RolInstructor;
use App\Modules\Roles\Models\Rol;
use App\Modules\Shared\Enums\EstadoUsuario;
use App\Modules\Usuarios\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class FiltrosRolesTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_puede_asignar_instructor_y_cuenta_dante_a_la_vez(): void
    {
        $instructor = Rol::create(['rol' => 'Instructor', 'descripcion' => 'Instructor']);
        $cuentadante = Rol::create(['rol' => 'Cuenta-dante', 'descripcion' => 'Cuenta dante']);
        $usuario = $this->crearUsuario('Pendiente');

        Sanctum::actingAs($this->crearUsuario(EstadoUsuario::Activo->value));

        $respuesta = $this->putJson("/v1/usuarios/{$usuario->id}", [
            'role_ids' => [$instructor->id, $cuentadante->id],
        ]);

        $respuesta->assertOk()->assertJsonStructure(['token']);

        $usuario->refresh();

        $this->assertSame(EstadoUsuario::Activo->value, $usuario->estado);
        $this->assertEqualsCanonicalizing(
            [$instructor->id, $cuentadante->id],
            $usuario->roles()->pluck('roles.id')->all()
        );
    }

    public function test_no_se_permite_otra_combinacion_de_roles(): void
    {
        $aprendiz = Rol::create(['rol' => 'Aprendiz', 'descripcion' => 'Aprendiz']);
        $instructor = Rol::create(['rol' => 'Instructor', 'descripcion' => 'Instructor']);
        $usuario = $this->crearUsuario('Pendiente');

        Sanctum::actingAs($this->crearUsuario(EstadoUsuario::Activo->value));

        $this->putJson("/v1/usuarios/{$usuario->id}", [
            'role_ids' => [$aprendiz->id, $instructor->id],
        ])->assertStatus(422);

        $this->assertCount(0, $usuario->roles);
    }

    public function test_los_filtros_de_rol_no_estan_en_las_rutas_pero_si_funcionan(): void
    {
        $instructor = Rol::create(['rol' => 'Instructor', 'descripcion' => 'Instructor']);
        $cuentadante = Rol::create(['rol' => 'Cuenta-dante', 'descripcion' => 'Cuenta dante']);
        $usuario = $this->crearUsuario(EstadoUsuario::Activo->value);
        $usuario->roles()->sync([$instructor->id]);

        $request = Request::create('/', 'GET');
        $request->setUserResolver(fn () => $usuario);
        $next = fn () => new Response('ok', 200);

        $this->assertSame(
            200,
            $this->app->make(RolInstructor::class)->handle($request, $next)->getStatusCode()
        );
        $this->assertSame(
            403,
            $this->app->make(RolCuentadante::class)->handle($request, $next)->getStatusCode()
        );

        // El administrador le otorga el permiso asignando el rol Cuenta-dante.
        $usuario->roles()->sync([$instructor->id, $cuentadante->id]);

        $this->assertSame(
            200,
            $this->app->make(RolCuentadante::class)->handle($request, $next)->getStatusCode()
        );
    }

    private function crearUsuario(string $estado): User
    {
        return User::create([
            'nombre_1' => 'Usuario',
            'apellido_1' => 'Prueba',
            'tipo_identidad' => 'CC',
            'numero_identidad' => fake()->unique()->numerify('##########'),
            'telefono' => fake()->unique()->numerify('3#########'),
            'email' => fake()->unique()->safeEmail(),
            'password' => 'password123',
            'estado' => $estado,
        ]);
    }
}
