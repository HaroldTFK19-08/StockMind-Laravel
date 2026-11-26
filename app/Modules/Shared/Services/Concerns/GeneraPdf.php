<?php

namespace App\Modules\Shared\Services\Concerns;

use Illuminate\Http\Response;

/**
 * Centraliza el patrón "ver/descargar" de los PDF.
 *
 * Cada servicio define qué vista renderiza y qué nombre de archivo usa;
 * el renderizado real sigue delegado en PDFService para no duplicar DomPDF.
 */
trait GeneraPdf
{
    /** Vista Blade que genera el documento; cada servicio la especifica. */
    protected function vistaPdf(): string
    {
        return 'documento';
    }

    /** Nombre con el que se entrega el PDF; cada servicio lo especifica. */
    protected function nombrePdf(): string
    {
        return 'documento.pdf';
    }

    /** Muestra el PDF en el navegador. */
    public function pdfVer(): Response
    {
        return $this->verPdf($this->datosParaPdf(), $this->vistaPdf(), $this->nombrePdf());
    }

    /** Fuerza la descarga del PDF. */
    public function pdfDescargar(): Response
    {
        return $this->descargarPdf($this->datosParaPdf(), $this->vistaPdf(), $this->nombrePdf());
    }

    /** stream() evita descargas involuntarias al revisar un documento. */
    protected function verPdf(array $datos, string $vista, string $archivo): Response
    {
        return $this->pdfService->ver($vista, $datos, $archivo);
    }

    /** download() entrega el archivo con el nombre acordado por dominio. */
    protected function descargarPdf(array $datos, string $vista, string $archivo): Response
    {
        return $this->pdfService->descargar($vista, $datos, $archivo);
    }
}
