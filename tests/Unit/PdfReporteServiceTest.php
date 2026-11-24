<?php

namespace Tests\Unit;

use App\Modules\Reportes\Services\PdfReporteService;
use Barryvdh\DomPDF\Facade\Pdf;
use Barryvdh\DomPDF\PDF as DomPdfDocument;
use Illuminate\Http\Response;
use Mockery;
use Tests\TestCase;

class PdfReporteServiceTest extends TestCase
{
    public function test_descargar_reporte_delega_en_el_servicio_pdf(): void
    {
        $resultado = new Response;
        $pdf = Mockery::mock(DomPdfDocument::class);
        $pdf->shouldReceive('download')
            ->once()
            ->with('reporte.pdf')
            ->andReturn($resultado);

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('reportes.detalle', ['id' => 1])
            ->andReturn($pdf);

        $this->assertSame(
            $resultado,
            (new PdfReporteService)->descargarReporte('reportes.detalle', ['id' => 1])
        );
    }

    public function test_ver_reporte_delega_en_el_servicio_pdf(): void
    {
        $resultado = new Response;
        $pdf = Mockery::mock(DomPdfDocument::class);
        $pdf->shouldReceive('stream')
            ->once()
            ->with('reporte.pdf')
            ->andReturn($resultado);

        Pdf::shouldReceive('loadView')
            ->once()
            ->with('reportes.detalle', ['id' => 1])
            ->andReturn($pdf);

        $this->assertSame(
            $resultado,
            (new PdfReporteService)->verReporte('reportes.detalle', ['id' => 1])
        );
    }
}
