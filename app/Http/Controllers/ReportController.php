<?php
// Descarga de reportes del catalogo. Demuestra la INVERSION DE DEPENDENCIAS:
// el metodo depende de la interfaz ReportGenerator; la implementacion concreta
// (PDF o CSV) se resuelve en tiempo de ejecucion segun el parametro {formato}.

namespace App\Http\Controllers;

use App\Models\Product;
use App\Services\Reports\CsvReportGenerator;
use App\Services\Reports\PdfReportGenerator;
use App\Services\Reports\ReportGenerator;

class ReportController extends Controller
{
    public function download(string $formato)
    {
        // Seleccion de la implementacion concreta a traves de la interfaz.
        $generador = $this->resolverGenerador($formato);

        $productos = Product::with('category')->orderBy('nombre')->get();

        return $generador->generar($productos);
    }

    private function resolverGenerador(string $formato): ReportGenerator
    {
        return match ($formato) {
            'pdf' => app(PdfReportGenerator::class),
            default => app(CsvReportGenerator::class),
        };
    }
}
