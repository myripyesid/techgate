<?php
// Implementacion concreta #1 de ReportGenerator: reporte en CSV (abrible en
// Excel). No requiere librerias externas.

namespace App\Services\Reports;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvReportGenerator implements ReportGenerator
{
    public function formato(): string
    {
        return 'csv';
    }

    public function generar(Collection $productos): Response
    {
        $nombre = 'catalogo-techgate-'.now()->format('Ymd-His').'.csv';

        return new StreamedResponse(function () use ($productos) {
            $salida = fopen('php://output', 'w');
            // BOM para que Excel reconozca UTF-8.
            fwrite($salida, "\xEF\xBB\xBF");
            fputcsv($salida, ['ID', 'Nombre', 'Marca', 'Categoría', 'Precio (USD)', 'Stock', 'Vendible']);

            foreach ($productos as $p) {
                fputcsv($salida, [
                    $p->getId(),
                    $p->getNombre(),
                    $p->getMarca(),
                    $p->getNombreCategoria(),
                    number_format($p->getPrecio(), 2, '.', ''),
                    $p->getStock(),
                    $p->esVendible() ? 'Sí' : 'No',
                ]);
            }

            fclose($salida);
        }, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="'.$nombre.'"',
        ]);
    }
}
