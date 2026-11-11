<?php

namespace App\Services;

use App\Contracts\ReportInterface;

class ExcelReportService implements ReportInterface
{
    public function generate(array $data)
    {
        // Lógica simulada o real para retornar un archivo Excel/CSV
        return response()->streamDownload(function () use ($data) {
            echo "ID,Nombre,Precio,Stock\n";
            foreach ($data as $item) {
                echo "{$item->id},{$item->nombre},{$item->precio},{$item->stock}\n";
            }
        }, 'reporte-catalogo.csv');
    }
}