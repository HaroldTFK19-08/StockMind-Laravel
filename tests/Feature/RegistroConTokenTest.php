<?php

namespace Tests\Feature;

use App\Modules\Roles\Models\Rol;
use App\Modules\Shared\Enums\EstadoUsuario;
use App\Modules\Usuarios\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\PersonalAccessToken;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class RegistroConTokenTest extends TestCase
{
    use RefreshDatabase;

    public function test_el_registro_crea_el_token_pero_no_lo_devuelve(): void
    {
        $respuesta = $this->postJson('/v1/auth/register', $this->datosRegistro());

        $respuesta->assertCreated()->assertJsonMissingPath('token');

        $usuario = User::where('email', 'ana@example.com')->firstOrFail();

        $this->assertSame(EstadoUsuario::Pendiente->value, $usuario->estado);
        $this->assertCount(0, $usuario->roles);
        $this->assertCount(1, $usuario->tokens);

        // Sin rol asignado, el usuario todavía no puede iniciar sesión.
        $this->postJson('/v1/auth/login', [
            'email' => 'ana@example.com',
            'password' => 'password123',
        ])->assertForbidden();
    }

    public function test_al_asignar_el_rol_se_activa_la_cuenta_y_se_entrega_el_token(): void
    {
        $this->postJson('/v1/auth/register', $this->datosRegistro())->assertCreated();

        $usuario = User::where('email', 'ana@example.com')->firstOrFail();
        $tokenPendiente = $usuario->tokens()->first()->token;
        $rol = Rol::create([
            'rol' => 'Aprendiz',
            'descripcion' => 'Rol de prueba',
        ]);

        Sanctum::actingAs($this->crearAdministrador());

        $respuesta = $this->putJson("/v1/usuarios/{$usuario->id}", [
            'role_ids' => [$rol->id],
        ]);

        $respuesta->assertOk()
            ->assertJsonPath('data.estado', EstadoUsuario::Activo->value)
            ->assertJsonStructure(['token']);

        $usuario->refresh();

        $this->assertSame(EstadoUsuario::Activo->value, $usuario->estado);
        $this->assertTrue($usuario->roles()->whereKey($rol->id)->exists());
        $this->assertCount(1, $usuario->tokens);
        $this->assertNotSame($tokenPendiente, $usuario->tokens()->first()->token);

        $token = PersonalAccessToken::findToken($respuesta->json('token'));
        $this->assertNotNull($token);
        $this->assertSame($usuario->id, $token->tokenable_id);
    }

    private function datosRegistro(): array
    {
        return [
            'nombre_1' => 'Ana',
            'apellido_1' => 'Pérez',
            'tipo_identidad' => 'CC',
            'numero_identidad' => '1234567890',
            'telefono' => '3001234567',
            'email' => 'ana@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];
    }

    private function crearAdministrador(): User
    {
        return User::create([
            'nombre_1' => 'Admin',
            'apellido_1' => 'Sistema',
            'tipo_identidad' => 'CC',
            'numero_identidad' => '9999999999',
            'telefono' => '3009999999',
            'email' => 'admin@example.com',
            'password' => 'password123',
            'estado' => EstadoUsuario::Activo->value,
        ]);
    }
}
