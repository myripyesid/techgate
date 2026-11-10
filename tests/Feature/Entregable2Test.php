<?php
// Pruebas del entregable #2: servicio web JSON, consumo de servicio aliado,
// cambio de idioma (LANG) e inversion de dependencias (reportes).

namespace Tests\Feature;

use App\Models\User;
use App\Services\Reports\CsvReportGenerator;
use App\Services\Reports\PdfReportGenerator;
use App\Services\Reports\ReportGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Tests\TestCase;

class Entregable2Test extends TestCase
{
    use RefreshDatabase;

    private function usuario(): User
    {
        $this->seed();

        return User::where('rol', 'usuario')->firstOrFail();
    }

    public function test_el_servicio_web_devuelve_productos_en_json_con_enlace(): void
    {
        $this->seed();

        $resp = $this->getJson('/api/productos');

        $resp->assertOk()
            ->assertJsonStructure([
                'tienda', 'total',
                'productos' => [['id', 'nombre', 'precio', 'stock', 'url']],
            ]);

        // El enlace directo a la ficha debe apuntar a la ruta de visualizacion.
        $this->assertStringContainsString('/ficha', $resp->json('productos.0.url'));
    }

    public function test_productos_aliados_consume_el_servicio_y_los_muestra(): void
    {
        $this->actingAs($this->usuario())
            ->get(route('allied.index'))
            ->assertOk()
            ->assertViewHas('productos');
    }

    public function test_el_cambio_de_idioma_se_guarda_en_la_sesion(): void
    {
        $this->actingAs($this->usuario());

        $this->get(route('language.switch', 'en'))->assertRedirect();
        $this->assertSame('en', session('locale'));

        $this->get(route('language.switch', 'es'))->assertRedirect();
        $this->assertSame('es', session('locale'));
    }

    public function test_la_interfaz_de_reportes_tiene_dos_implementaciones(): void
    {
        // Inversion de dependencias: una interfaz, dos clases concretas.
        $this->assertInstanceOf(ReportGenerator::class, new CsvReportGenerator());
        $this->assertInstanceOf(ReportGenerator::class, new PdfReportGenerator());

        $this->assertSame('csv', (new CsvReportGenerator())->formato());
        $this->assertSame('pdf', (new PdfReportGenerator())->formato());
    }

    public function test_el_binding_por_defecto_resuelve_una_implementacion(): void
    {
        // El contenedor resuelve la interfaz a una implementacion concreta.
        $generador = app(ReportGenerator::class);

        $this->assertInstanceOf(ReportGenerator::class, $generador);

        $resp = $generador->generar(new Collection());
        $this->assertSame(200, $resp->getStatusCode());
    }
}
