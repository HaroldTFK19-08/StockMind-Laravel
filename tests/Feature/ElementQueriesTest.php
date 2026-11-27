<?php

namespace Tests\Feature;

use App\Modules\Almacenes\Models\Warehouse;
use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Categorias\Models\Category;
use App\Modules\Centros\Models\Trainingcenter;
use App\Modules\Elementos\Models\Element;
use App\Modules\Fichas\Models\Coursegroup;
use App\Modules\Prestamos\Models\Loan;
use App\Modules\Sedes\Models\Headquarter;
use App\Modules\Usuarios\Models\Apprentice;
use App\Modules\Usuarios\Models\Teacher;
use App\Modules\Usuarios\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ElementQueriesTest extends TestCase
{
    use RefreshDatabase;

    public function test_element_history_combines_loans_and_assignments_in_date_order(): void
    {
        $instructorUser = $this->createUser('instructor');
        $instructor = Teacher::create([
            'user_id' => $instructorUser->id,
            'tipo_instructor' => 'planta',
            'especialidad' => 'Sistemas',
        ]);
        $apprenticeUser = $this->createUser('apprentice');
        $coursegroup = Coursegroup::create([
            'teacher_id' => $instructor->id,
            'codigo' => 'FIC-001',
            'programa' => 'Sistemas',
            'fecha_inicio' => '2026-01-01',
            'fecha_fin' => '2026-12-31',
        ]);
        $apprentice = Apprentice::create([
            'user_id' => $apprenticeUser->id,
            'coursegroup_id' => $coursegroup->id,
            'estado' => 'En formacion',
        ]);
        $category = Category::create([
            'nombre' => 'Computadores',
            'descripcion' => 'Equipos de cómputo',
        ]);
        $element = $this->createElement($category, 'INV-001');

        Loan::create([
            'element_id' => $element->id,
            'teacher_id' => $instructor->id,
            'fecha_prestamo' => '2026-01-10 09:00:00',
            'fecha_devolucion_esperada' => '2026-01-15',
            'fecha_devolucion_real' => '2026-01-14 16:00:00',
            'estado' => 'devuelto',
        ]);
        Assignment::create([
            'element_id' => $element->id,
            'apprentice_id' => $apprentice->id,
            'teacher_id' => $instructor->id,
            'fecha_asignacion' => '2026-02-01 09:00:00',
            'fecha_devolucion_esperada' => '2026-02-10',
            'fecha_devolucion_real' => null,
            'estado' => 'activa',
        ]);

        $response = $this->actingAs($instructorUser)
            ->getJson('/v1/elementos/INV-001/historial')
            ->assertOk()
            ->assertJsonPath('data.elemento.codigo_inventario', 'INV-001')
            ->assertJsonCount(2, 'data.historial');

        $historial = $response->json('data.historial');

        $this->assertSame('Asignación', $historial[0]['tipo']);
        $this->assertSame('Aprendiz', $historial[0]['tipo_persona']);
        $this->assertSame('Préstamo', $historial[1]['tipo']);
        $this->assertSame('Instructor', $historial[1]['tipo_persona']);
    }

    public function test_inventory_groups_warehouse_elements_by_site_and_category(): void
    {
        $instructorUser = $this->createUser('instructor');
        $instructor = Teacher::create([
            'user_id' => $instructorUser->id,
            'tipo_instructor' => 'planta',
            'especialidad' => 'Sistemas',
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
        $warehouse = Warehouse::create([
            'headquarter_id' => $headquarter->id,
            'nombre' => 'Almacén principal',
            'capacidad_elementos' => 100,
        ]);
        $category = Category::create([
            'nombre' => 'Computadores',
            'descripcion' => 'Equipos de cómputo',
        ]);

        $this->createElement($category, 'INV-001', $warehouse);
        $this->createElement($category, 'INV-002', $warehouse);
        $this->createElement($category, 'INV-003');

        $response = $this->actingAs($instructorUser)
            ->getJson('/v1/elementos/inventario-por-sede-almacen-categoria')
            ->assertOk()
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.sede', 'Sede principal')
            ->assertJsonPath('data.0.almacen', 'Almacén principal')
            ->assertJsonPath('data.0.categoria', 'Computadores')
            ->assertJsonPath('data.0.cantidad', 2);

        $this->assertSame(1, $response->json('data.0.sede_id'));
    }

    private function createElement(
        Category $category,
        string $codigo,
        ?Warehouse $warehouse = null
    ): Element {
        return Element::create([
            'category_id' => $category->id,
            'warehouse_id' => $warehouse?->id,
            'codigo_inventario' => $codigo,
            'nombre' => 'Equipo ' . $codigo,
            'img_elemento' => 'equipo.jpg',
        ]);
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
            'numero_identidad' => '2000' . $suffix,
            'telefono' => '400' . $suffix,
            'email' => $suffix . '@example.test',
            'password' => 'password',
        ]);
    }
}
