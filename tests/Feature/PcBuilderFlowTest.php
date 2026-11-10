<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PcBuilderFlowTest extends TestCase
{
    use RefreshDatabase;

    private function usuario(): User
    {
        $this->seed();

        return User::where('rol', 'usuario')->firstOrFail();
    }

    private function productoDe(string $slug): Product
    {
        return Product::whereHas('category', fn ($q) => $q->where('slug', $slug))
            ->where('es_vendible', true)->where('stock', '>', 0)->firstOrFail();
    }

    public function test_la_pestana_arma_tu_pc_carga(): void
    {
        app()->setLocale('es');
        $this->actingAs($this->usuario())
            ->get(route('builder.index'))
            ->assertOk()
            ->assertSee('Arma tu PC')
            ->assertSee('Procesador');
    }

    public function test_el_comparador_muestra_los_graficos(): void
    {
        app()->setLocale('es');
        $usuario = $this->usuario();
        $a = Product::whereHas('category', fn ($q) => $q->where('slug', 'cpu'))->first();
        $b = Product::whereHas('category', fn ($q) => $q->where('slug', 'cpu'))->orderByDesc('precio')->first();

        $this->actingAs($usuario)
            ->get(route('comparator.index', ['product_a_id' => $a->id, 'product_b_id' => $b->id]))
            ->assertOk()
            ->assertSee('Índice de rendimiento')
            ->assertSee('chartRadar', false)
            ->assertSee('chart.js', false);
    }

    public function test_evaluar_devuelve_json_con_compatibilidad(): void
    {
        $usuario = $this->usuario();
        $cpu = $this->productoDe('cpu');
        $chip = Product::whereHas('category', fn ($q) => $q->where('slug', 'chipset'))
            ->where('especificaciones->socket', $cpu->getEspecificacion('socket'))->first();

        $this->actingAs($usuario)
            ->postJson(route('builder.evaluate'), ['cpu' => $cpu->id, 'chipset' => $chip?->id])
            ->assertOk()
            ->assertJsonStructure(['componentes', 'pares', 'consumo', 'total', 'estado_global', 'faltantes']);
    }

    public function test_agregar_todo_al_carrito_agrega_los_componentes(): void
    {
        $usuario = $this->usuario();
        $cpu = $this->productoDe('cpu');
        $gpu = $this->productoDe('gpu');
        $ram = $this->productoDe('ram');

        $resp = $this->actingAs($usuario)->post(route('builder.add-to-cart'), [
            'cpu' => $cpu->id,
            'gpu' => $gpu->id,
            'ram' => $ram->id,
        ]);

        $resp->assertRedirect(route('cart.index'));

        $carrito = $usuario->carritoActivo();
        $this->assertSame(3, $carrito->items()->count());
        $this->assertSame(3, $carrito->cantidadTotal());
    }
}
