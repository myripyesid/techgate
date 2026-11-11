<?php

namespace App\Services;

use App\Contracts\ReportInterface;

class PdfReportService implements ReportInterface
{
    public function generate(array $data)
    {
        // Lógica simulada o real para retornar un archivo PDF o vista en PDF
        return response()->streamDownload(function () use ($data) {
            echo "Generando Reporte en formato PDF con los datos del catálogo...";
        }, 'reporte-catalogo.pdf');
    }
}