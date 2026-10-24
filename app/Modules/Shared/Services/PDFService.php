<?php

namespace App\Modules\Shared\Services;

use Barryvdh\DomPDF\Facade\Pdf;

class PDFService
{
    public function descargar(string $vista, array $datos = [], string $nombreArchivo = "")
    {
        $pdf = Pdf::loadView($vista, $datos)->download($nombreArchivo);
        return $pdf;
    }
    public function ver(string $vista, array $datos = [], string $nombreArchivo = "")
    {
        $ver = Pdf::loadView($vista, $datos)->stream($nombreArchivo);
        return $ver;
    }
}
