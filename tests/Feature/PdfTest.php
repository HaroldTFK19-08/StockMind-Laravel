<?php

namespace Tests\Feature;

use App\Modules\Ambientes\Models\Environment;
use App\Modules\Asignaciones\Models\Assignment;
use App\Modules\Categorias\Models\Category;
use App\Modules\Centros\Models\Trainingcenter;
use App\Modules\Elementos\Models\Element;
use App\Modules\Fichas\Models\Coursegroup;
use App\Modules\Reportes\Models\Report;
use App\Modules\Sedes\Models\Headquarter;
use App\Modules\Shared\Enums\EstadoUsuario;
use App\Modules\Shared\Services\ResponsablesElementoService;
use App\Modules\Usuarios\Models\Apprentice;
use App\Modules\Usuarios\Models\Danteaccount;
use App\Modules\Usuarios\Models\Teacher;
use App\Modules\Usuarios\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_no_existe_pdf_de_un_reporte_especifico(): void
    {
        $datos = $this->crearEscenario();

        // El PDF de reportes es siempre general; estas rutas ya no se exponen.
        $this->get("/v1/reportes/{$datos['reporte']->id}/pdf/ver")
            ->assertNotFound();
        $this->get("/v1/reportes/{$datos['reporte']->id}/pdf/descargar")
            ->assertNotFound();
    }

    public function test_el_listado_de_reportes_genera_un_pdf(): void
    {
        $this->crearEscenario();

        $respuesta = $this->get('/v1/reportes/pdf/ver');

        $respuesta->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
    }

    public function test_el_inventario_genera_un_pdf_descargable(): void
    {
        $this->crearEscenario();

        $respuesta = $this->get('/v1/elementos/pdf/descargar');

        $respuesta->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $contentDisposition = $respuesta->headers->get('Content-Disposition') ?? '';

        $this->assertStringContainsString('attachment', $contentDisposition);
        $this->assertStringContainsString('inventario.pdf', $contentDisposition);
        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
    }

    public function test_el_pdf_de_asignaciones_genera_un_documento_valido(): void
    {
        $this->crearEscenario();

        $respuesta = $this->get('/v1/asignaciones/pdf/ver');

        $respuesta->assertOk()
            ->assertHeader('Content-Type', 'application/pdf');

        $this->assertStringStartsWith('%PDF', $respuesta->getContent());
    }

    public function test_los_estilos_pdf_usan_la_paleta_del_panel_y_son_planos(): void
    {
        $css = (string) file_get_contents(public_path('css/pdf.css'));

        $this->assertStringContainsString('#39a900', $css);
        $this->assertStringContainsString('#17330f', $css);
        $this->assertStringContainsString('.pdf-table th', $css);
        $this->assertStringNotContainsString('@layer', $css);
        $this->assertStringNotContainsString('@supports', $css);
    }

    public function test_la_ficha_de_elemento_muestra_al_aprendiz_responsable_y_su_uso(): void
    {
        $datos = $this->crearEscenario();
        $elemento = Element::query()
            ->with(ResponsablesElementoService::relacionesParaPdf())
            ->findOrFail($datos['elemento']->id);

        $ficha = $this->app->make(ResponsablesElementoService::class)->ficha($elemento);

        $this->assertSame('Aprendiz Prueba', $ficha['responsable']);
        $this->assertSame('Aprendiz con asignación activa', $ficha['tipo_responsable']);
        $this->assertSame(1, $ficha['uso']['asignaciones']);
        $this->assertSame(1, $ficha['uso']['asignaciones_activas']);

        $html = view('elementos.pdf', [
            'elementos' => [$ficha],
            'total' => 1,
            'generado_en' => now(),
        ])->render();

        $this->assertStringContainsString('Aprendiz Prueba', $html);
        $this->assertStringContainsString('Asignaciones: 1 (1 activas)', $html);
    }

    /**
     * @return array<string, mixed>
     */
    private function crearEscenario(): array
    {
        $instructor = $this->crearUsuario('Instructor');
        $aprendiz = $this->crearUsuario('Aprendiz');
        $cuentadante = $this->crearUsuario('Cuentadante');

        $teacher = Teacher::create([
            'user_id' => $instructor->id,
            'tipo_instructor' => 'planta',
        ]);

        $ficha = Coursegroup::create([
            'teacher_id' => $teacher->id,
            'codigo' => 'FICHA-PDF',
            'programa' => 'Programa de prueba',
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => now()->addMonth()->toDateString(),
        ]);

        $aprendizModelo = Apprentice::create([
            'user_id' => $aprendiz->id,
            'coursegroup_id' => $ficha->id,
        ]);

        $centro = Trainingcenter::create([
            'nombre' => 'Centro PDF',
            'regional' => 'Cauca',
            'ciudad' => 'Popayán',
        ]);
        $sede = Headquarter::create([
            'trainingcenter_id' => $centro->id,
            'nombre' => 'Sede PDF',
            'direccion' => 'Dirección de prueba',
            'zona' => 'urbana',
        ]);
        $ambiente = Environment::create([
            'headquarter_id' => $sede->id,
            'teacher_id' => $teacher->id,
            'nombre' => 'Ambiente PDF',
            'capacidad' => 10,
            'tipo' => 'sala de sistemas',
            'estado' => 'activo',
        ]);

        Danteaccount::create([
            'user_id' => $cuentadante->id,
            'environment_id' => $ambiente->id,
            'fecha_inicio' => now()->toDateString(),
            'fecha_fin' => null,
        ]);

        $categoria = Category::create([
            'nombre' => 'Categoría PDF',
            'descripcion' => 'Categoría de prueba',
        ]);
        $elemento = Element::create([
            'category_id' => $categoria->id,
            'environment_id' => $ambiente->id,
            'codigo_inventario' => 'ELEM-PDF-001',
            'nombre' => 'Computador de prueba',
            'img_elemento' => 'https://example.com/elemento.png',
            'estado' => 'asignado',
            'condicion' => 'bueno',
        ]);

        $asignacion = Assignment::create([
            'element_id' => $elemento->id,
            'apprentice_id' => $aprendizModelo->id,
            'teacher_id' => $teacher->id,
            'fecha_asignacion' => now(),
            'fecha_devolucion_esperada' => now()->addWeek()->toDateString(),
            'estado' => 'activa',
            'observaciones' => 'Uso para prueba de PDF',
        ]);

        $reporte = Report::create([
            'apprentice_id' => $aprendizModelo->id,
            'tipo' => 'falla',
            'descripcion' => 'El equipo presenta fallas.',
            'fecha_reporte' => now(),
            'foto_probatorio' => '',
            'foto_probatorio_public_id' => null,
            'estado' => 'pendiente',
        ]);
        $reporte->elements()->attach($elemento->id);

        return [
            'reporte' => $reporte,
            'asignacion' => $asignacion,
            'elemento' => $elemento,
        ];
    }

    private function crearUsuario(string $prefijo): User
    {
        static $secuencia = 0;
        $secuencia++;

        return User::create([
            'nombre_1' => $prefijo,
            'apellido_1' => 'Prueba',
            'tipo_identidad' => 'CC',
            'numero_identidad' => (string) (8000000000 + $secuencia),
            'telefono' => (string) (3000000000 + $secuencia),
            'email' => strtolower($prefijo) . $secuencia . '-pdf@example.com',
            'password' => 'password123',
            'estado' => EstadoUsuario::Activo->value,
        ]);
    }
}
