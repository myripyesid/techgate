<?php
// Implementacion concreta #2 de ReportGenerator: reporte "PDF".
//
// Para no depender de librerias externas (dompdf, etc.), se genera un
// documento HTML imprimible que el navegador exporta a PDF con Ctrl+P /
// "Guardar como PDF". Demuestra igualmente la inversion de dependencias:
// misma interfaz, implementacion distinta.

namespace App\Services\Reports;

use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class PdfReportGenerator implements ReportGenerator
{
    public function formato(): string
    {
        return 'pdf';
    }

    public function generar(Collection $productos): Response
    {
        $filas = $productos->map(function ($p) {
            return '<tr>'
                .'<td>'.e($p->getId()).'</td>'
                .'<td>'.e($p->getNombre()).'</td>'
                .'<td>'.e($p->getMarca()).'</td>'
                .'<td>'.e($p->getNombreCategoria()).'</td>'
                .'<td style="text-align:right">$'.number_format($p->getPrecio(), 2).'</td>'
                .'<td style="text-align:center">'.e($p->getStock()).'</td>'
                .'</tr>';
        })->implode('');

        $fecha = now()->format('Y-m-d H:i');
        $html = <<<HTML
<!DOCTYPE html>
<html lang="es"><head><meta charset="UTF-8">
<title>Catálogo TechGate</title>
<style>
  body { font-family: Arial, sans-serif; color: #1d1d1b; margin: 2rem; }
  h1 { border-bottom: 3px solid #ffdd00; padding-bottom: .4rem; }
  .meta { color: #777; font-size: .85rem; margin-bottom: 1rem; }
  table { width: 100%; border-collapse: collapse; font-size: .85rem; }
  th, td { border: 1px solid #ddd; padding: .4rem .6rem; }
  th { background: #1d1d1b; color: #ffdd00; text-align: left; }
  tr:nth-child(even) td { background: #fafafa; }
  @media print { .noprint { display: none; } }
</style></head>
<body onload="window.print()">
  <h1>Catálogo TechGate</h1>
  <div class="meta">Reporte generado el {$fecha} · {$productos->count()} productos</div>
  <p class="noprint" style="color:#777">Usa Ctrl+P / «Guardar como PDF» para exportar este reporte.</p>
  <table>
    <thead><tr><th>ID</th><th>Nombre</th><th>Marca</th><th>Categoría</th><th>Precio</th><th>Stock</th></tr></thead>
    <tbody>{$filas}</tbody>
  </table>
</body></html>
HTML;

        return new Response($html, 200, ['Content-Type' => 'text/html; charset=UTF-8']);
    }
}
