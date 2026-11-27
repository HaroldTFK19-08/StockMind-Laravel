<?php

namespace Tests\Feature;

use App\Modules\Ambientes\Models\Environment;
use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Categorias\Models\Category;
use App\Modules\Centros\Models\Trainingcenter;
use App\Modules\Elementos\Models\Element;
use App\Modules\Fichas\Models\Coursegroup;
use App\Modules\Prestamos\Models\Loan;
use App\Modules\Reportes\Models\Report;
use App\Modules\Sedes\Models\Headquarter;
use App\Modules\Usuarios\Models\Apprentice;
use App\Modules\Usuarios\Models\Danteaccount;
use App\Modules\Usuarios\Models\Teacher;
use App\Modules\Usuarios\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConsultasTest extends TestCase
{
    use RefreshDatabase;

    public function test_instructores_filtra_y_no_expone_datos_sensibles(): void
    {
        $activo = $this->crearUsuario('instructor@sena.test');
        $inactivo = $this->crearUsuario('inactivo@sena.test', 'Inactivo');
        $teacher = Teacher::create([
            'user_id' => $activo->id,
            'tipo_instructor' => 'planta',
            'especialidad' => 'Sistemas',
        ]);
        Teacher::create([
            'user_id' => $inactivo->id,
            'tipo_instructor' => 'planta',
            'especialidad' => 'Sistemas',
        ]);
        $this->crearFicha($teacher, 'FIC-1');

        $respuesta = $this->actingAs($activo)
            ->getJson('/v1/consultas/instructores?especialidad=Sistemas&tipo_instructor=planta')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.coursegroups_count', 1)
            ->assertJsonPath('data.0.user.estado', 'Activo');

        $this->assertArrayNotHasKey('email', $respuesta->json('data.0.user'));
        $this->assertArrayNotHasKey('telefono', $respuesta->json('data.0.user'));

        $this->actingAs($activo)
            ->getJson('/v1/consultas/instructores?estado=Inactivo')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.user.estado', 'Inactivo');
    }

    public function test_cuentas_dante_muestra_vigentes_por_defecto_y_permite_ver_el_historial(): void
    {
        $dante = $this->crearUsuario('dante@sena.test');
        $ambiente = $this->crearAmbiente();
        Danteaccount::create([
            'user_id' => $dante->id,
            'environment_id' => $ambiente->id,
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => null,
        ]);
        Danteaccount::create([
            'user_id' => $dante->id,
            'environment_id' => $ambiente->id,
            'fecha_inicio' => '2025-01-01',
            'fecha_fin' => '2025-12-31',
        ]);

        $respuesta = $this->actingAs($dante)
            ->getJson("/v1/consultas/cuentas-dante?ambiente_id={$ambiente->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data');

        $this->assertStringStartsWith('2026-01-01', $respuesta->json('data.0.fecha_inicio'));

        $this->actingAs($dante)
            ->getJson("/v1/consultas/cuentas-dante?solo_vigentes=0")
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_reportes_se_filtran_por_tipo_estado_y_fecha(): void
    {
        $instructor = $this->crearUsuario('reportes@sena.test');
        $teacher = Teacher::create([
            'user_id' => $instructor->id,
            'tipo_instructor' => 'planta',
        ]);
        $ficha = $this->crearFicha($teacher, 'FIC-REP');
        $apprentice = Apprentice::create([
            'user_id' => $this->crearUsuario('aprendiz-reporte@sena.test')->id,
            'coursegroup_id' => $ficha->id,
            'estado' => 'En formacion',
        ]);
        Report::create([
            'apprentice_id' => $apprentice->id,
            'tipo' => 'falla',
            'descripcion' => 'Equipo sin encender',
            'fecha_reporte' => '2026-01-10 10:00:00',
            'foto_probatorio' => 'falla.jpg',
            'estado' => 'pendiente',
        ]);
        Report::create([
            'apprentice_id' => $apprentice->id,
            'tipo' => 'perdida',
            'descripcion' => 'Mouse extraviado',
            'fecha_reporte' => '2026-02-10 10:00:00',
            'foto_probatorio' => 'perdida.jpg',
            'estado' => 'resuelto',
        ]);

        $this->actingAs($instructor)
            ->getJson('/v1/consultas/reportes?tipo=falla&estado=pendiente&desde=2026-01-01&hasta=2026-01-31')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.tipo', 'falla')
            ->assertJsonPath('data.0.apprentice.user.nombre_1', 'Usuario');
    }

    public function test_mis_solicitudes_separa_asignaciones_y_prestamos_por_usuario(): void
    {
        $instructor = $this->crearUsuario('solicitudes@sena.test');
        $teacher = Teacher::create([
            'user_id' => $instructor->id,
            'tipo_instructor' => 'planta',
        ]);
        $ficha = $this->crearFicha($teacher, 'FIC-SOL');
        $aprendizUser = $this->crearUsuario('aprendiz-solicitudes@sena.test');
        $apprentice = Apprentice::create([
            'user_id' => $aprendizUser->id,
            'coursegroup_id' => $ficha->id,
            'estado' => 'En formacion',
        ]);
        $elemento = $this->crearElemento('SOL-1');

        Assignment::create([
            'element_id' => $elemento->id,
            'apprentice_id' => $apprentice->id,
            'teacher_id' => $teacher->id,
            'fecha_asignacion' => '2026-01-10 09:00:00',
            'fecha_devolucion_esperada' => '2026-01-20',
            'estado' => 'activa',
        ]);
        Loan::create([
            'element_id' => $elemento->id,
            'teacher_id' => $teacher->id,
            'fecha_prestamo' => '2026-01-11 09:00:00',
            'fecha_devolucion_esperada' => '2026-01-21',
            'estado' => 'activo',
        ]);

        $this->actingAs($aprendizUser)
            ->getJson('/v1/consultas/mis-solicitudes')
            ->assertOk()
            ->assertJsonCount(1, 'asignaciones.data')
            ->assertJsonCount(0, 'prestamos.data');

        $this->actingAs($instructor)
            ->getJson('/v1/consultas/mis-solicitudes?estado=activo')
            ->assertOk()
            ->assertJsonCount(0, 'asignaciones.data')
            ->assertJsonCount(1, 'prestamos.data');
    }

    public function test_solicitudes_ficha_usa_las_fichas_del_instructor_cuando_no_recibe_el_id(): void
    {
        $instructor = $this->crearUsuario('ficha@sena.test');
        $teacher = Teacher::create([
            'user_id' => $instructor->id,
            'tipo_instructor' => 'planta',
        ]);
        $ficha = $this->crearFicha($teacher, 'FIC-IN');
        $otraFicha = $this->crearFicha($teacher, 'FIC-OUT');
        $apprentice = Apprentice::create([
            'user_id' => $this->crearUsuario('aprendiz-ficha@sena.test')->id,
            'coursegroup_id' => $ficha->id,
            'estado' => 'En formacion',
        ]);
        $otroApprentice = Apprentice::create([
            'user_id' => $this->crearUsuario('otro-ficha@sena.test')->id,
            'coursegroup_id' => $otraFicha->id,
            'estado' => 'En formacion',
        ]);
        $elemento = $this->crearElemento('FIC-1');

        foreach ([$apprentice, $otroApprentice] as $persona) {
            Assignment::create([
                'element_id' => $elemento->id,
                'apprentice_id' => $persona->id,
                'teacher_id' => $teacher->id,
                'fecha_asignacion' => '2026-01-10 09:00:00',
                'fecha_devolucion_esperada' => '2026-01-20',
                'estado' => 'activa',
            ]);
        }

        $this->actingAs($instructor)
            ->getJson("/v1/consultas/solicitudes-ficha?ficha_id={$ficha->id}")
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.apprentice.coursegroup_id', $ficha->id);

        $this->actingAs($instructor)
            ->getJson('/v1/consultas/solicitudes-ficha')
            ->assertOk()
            ->assertJsonCount(2, 'data');
    }

    public function test_las_consultas_requieren_autenticacion_y_limitan_el_tamano_de_pagina(): void
    {
        foreach ([
            '/v1/consultas/instructores',
            '/v1/consultas/cuentas-dante',
            '/v1/consultas/reportes',
            '/v1/consultas/mis-solicitudes',
            '/v1/consultas/solicitudes-ficha',
        ] as $ruta) {
            $this->getJson($ruta)->assertUnauthorized();
        }

        $this->actingAs($this->crearUsuario('limite@sena.test'))
            ->getJson('/v1/consultas/instructores?per_page=100')
            ->assertStatus(422);
    }

    private function crearUsuario(string $email, string $estado = 'Activo'): User
    {
        return User::create([
            'nombre_1' => 'Usuario',
            'apellido_1' => 'Sena',
            'tipo_identidad' => 'CC',
            'numero_identidad' => (string) random_int(100000, 999999),
            'telefono' => (string) random_int(3000000000, 3999999999),
            'email' => $email,
            'password' => 'password',
            'estado' => $estado,
        ]);
    }

    private function crearFicha(Teacher $teacher, string $codigo): Coursegroup
    {
        return Coursegroup::create([
            'teacher_id' => $teacher->id,
            'codigo' => $codigo,
            'programa' => 'Sistemas',
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-12-31',
        ]);
    }

    private function crearAmbiente(): Environment
    {
        $centro = Trainingcenter::create([
            'nombre' => 'Centro de prueba',
            'ciudad' => 'Popayán',
        ]);
        $sede = Headquarter::create([
            'trainingcenter_id' => $centro->id,
            'nombre' => 'Sede principal',
            'direccion' => 'Dirección de prueba',
            'zona' => 'urbana',
        ]);

        return Environment::create([
            'headquarter_id' => $sede->id,
            'nombre' => 'Ambiente de prueba',
            'capacidad' => 20,
            'tipo' => 'Aula',
        ]);
    }

    private function crearElemento(string $codigo): Element
    {
        $categoria = Category::create([
            'nombre' => 'Categoría ' . $codigo,
            'descripcion' => 'Prueba',
        ]);

        return Element::create([
            'category_id' => $categoria->id,
            'codigo_inventario' => $codigo,
            'nombre' => 'Equipo ' . $codigo,
            'img_elemento' => 'equipo.jpg',
        ]);
    }
}
