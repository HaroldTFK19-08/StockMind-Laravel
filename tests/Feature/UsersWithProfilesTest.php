<?php

namespace Tests\Feature;

use App\Modules\Ambientes\Models\Environment;
use App\Modules\Centros\Models\Trainingcenter;
use App\Modules\Fichas\Models\Coursegroup;
use App\Modules\Sedes\Models\Headquarter;
use App\Modules\Usuarios\Models\Apprentice;
use App\Modules\Usuarios\Models\Danteaccount;
use App\Modules\Usuarios\Models\Teacher;
use App\Modules\Usuarios\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UsersWithProfilesTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_users_can_list_users_with_apprentice_teacher_or_dante_profiles(): void
    {
        $instructorUser = $this->createUser('instructor');
        $instructor = Teacher::create([
            'user_id' => $instructorUser->id,
            'tipo_instructor' => 'planta',
            'especialidad' => 'Sistemas',
        ]);

        $coursegroup = Coursegroup::create([
            'teacher_id' => $instructor->id,
            'codigo' => 'FIC-001',
            'programa' => 'Sistemas',
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-12-31',
        ]);

        $apprenticeUser = $this->createUser('apprentice');
        Apprentice::create([
            'user_id' => $apprenticeUser->id,
            'coursegroup_id' => $coursegroup->id,
            'estado' => 'En formacion',
        ]);

        $trainingcenter = Trainingcenter::create([
            'nombre' => 'Centro de prueba',
            'foto_centro' => 'centro.jpg',
            'ciudad' => 'Popayán',
        ]);
        $headquarter = Headquarter::create([
            'trainingcenter_id' => $trainingcenter->id,
            'nombre' => 'Sede principal',
            'direccion' => 'Dirección de prueba',
            'zona' => 'urbana',
        ]);
        $environment = Environment::create([
            'headquarter_id' => $headquarter->id,
            'teacher_id' => $instructor->id,
            'nombre' => 'Ambiente de prueba',
            'capacidad' => 20,
            'tipo' => 'Aula',
        ]);

        $danteUser = $this->createUser('dante');
        Danteaccount::create([
            'user_id' => $danteUser->id,
            'environment_id' => $environment->id,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => null,
        ]);

        $unrelatedUser = $this->createUser('unrelated');

        $response = $this->actingAs($instructorUser)
            ->getJson('/v1/usuarios/perfiles')
            ->assertOk();

        $users = collect($response->json('data'))->keyBy('id');

        $this->assertEqualsCanonicalizing(
            [$instructorUser->id, $apprenticeUser->id, $danteUser->id],
            $users->keys()->all()
        );
        $this->assertArrayNotHasKey($unrelatedUser->id, $users->all());
        $this->assertNotNull($users[$instructorUser->id]['teacher']);
        $this->assertNotNull($users[$apprenticeUser->id]['apprentice']);
        $this->assertNotEmpty($users[$danteUser->id]['danteaccounts']);
        $this->assertArrayNotHasKey('email', $users[$instructorUser->id]);
    }

    public function test_profile_list_requires_authentication(): void
    {
        $this->getJson('/v1/usuarios/perfiles')
            ->assertUnauthorized();
    }

    private function createUser(string $suffix): User
    {
        return User::create([
            'foto_perfil' => 'perfil.jpg',
            'nombre_1' => 'Usuario',
            'nombre_2' => '',
            'apellido_1' => ucfirst($suffix),
            'apellido_2' => '',
            'tipo_identidad' => 'CC',
            'numero_identidad' => '1000' . $suffix,
            'telefono' => '300' . $suffix,
            'email' => $suffix . '@example.test',
            'password' => 'password',
        ]);
    }
}
