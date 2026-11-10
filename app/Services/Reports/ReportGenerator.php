<?php
// Inversion de dependencias: interfaz del generador de reportes del catalogo.
//
// Hay DOS clases concretas que la implementan (PdfReportGenerator y
// CsvReportGenerator). El controlador depende de esta INTERFAZ, no de una
// implementacion concreta; la implementacion se decide en el contenedor de
// servicios (AppServiceProvider) o por parametro de la ruta.

namespace App\Services\Reports;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

interface ReportGenerator
{
    // Genera el reporte del catalogo y devuelve la respuesta descargable.
    //
    // @param  Collection<int, \App\Models\Product>  $productos
    public function generar(Collection $productos): Response;

    // Extension/formato del reporte (para el nombre del archivo y la UI).
    public function formato(): string;
}
