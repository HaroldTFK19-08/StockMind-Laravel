<?php

namespace App\Modules\Reportes\Services;

use App\Modules\Shared\Services\PDFService;

class PdfReporteService extends PDFService
{
    public function descargarReporte(
        string $vista,
        array $datos = [],
        string $nombreArchivo = 'reporte.pdf'
    ) {
        return parent::descargar($vista, $datos, $nombreArchivo);
    }

    public function verReporte(
        string $vista,
        array $datos = [],
        string $nombreArchivo = 'reporte.pdf'
    ) {
        return parent::ver($vista, $datos, $nombreArchivo);
    }
}
