<?php

namespace App\Modules\Shared\Services;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;

class PDFService
{
    public function descargar(string $vista, array $datos = [], string $nombreArchivo = 'documento.pdf'): Response
    {
        return Pdf::loadView($vista, $datos)->download($nombreArchivo);
    }

    public function ver(string $vista, array $datos = [], string $nombreArchivo = 'documento.pdf'): Response
    {
        return Pdf::loadView($vista, $datos)->stream($nombreArchivo);
    }
}
