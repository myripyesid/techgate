<?php
// Integracion del catalogo fusionado (PC + dispositivos electronicos)
// con el resto del sistema: seeder, ficha, carrito, comparador y compatibilidad.

namespace Tests\Feature;

use App\Models\Category;
use App\Models\ComponentCompatibility;
use App\Models\Product;
use App\Models\User;
use Database\Seeders\ProductSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CatalogoFusionadoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(ProductSeeder::class);
    }

    private function usuario(): User
    {
        return User::create([
            'name' => 'Cliente',
            'email' => 'cliente'.uniqid().'@techgate.com',
            'password' => 'secreto123',
            'rol' => User::ROL_USUARIO,
        ]);
    }

    private function producto(string $modelo): Product
    {
        return Product::where('modelo', $modelo)->firstOrFail();
    }

    public function test_el_seeder_carga_las_14_categorias_y_los_95_productos(): void
    {
        $this->assertSame(14, Category::count());
        $this->assertSame(95, Product::count());
        $this->assertSame(8, Category::where('grupo', 'pc')->count());
        $this->assertSame(6, Category::where('grupo', 'electronico')->count());
    }

    public function test_el_seeder_es_idempotente(): void
    {
        $this->seed(ProductSeeder::class);

        $this->assertSame(95, Product::count());
        $this->assertSame(14, Category::count());
    }

    public function test_las_especificaciones_se_guardan_y_se_leen_como_arreglo(): void
    {
        $cpu = $this->producto('Ryzen 5 9600X');

        $this->assertSame('cpu', $cpu->getCategorySlug());
        $this->assertSame('AM5', $cpu->getEspecificacion('socket'));
        $this->assertSame(6, $cpu->getEspecificacion('nucleos_totales'));
        $this->assertSame('AMD', $cpu->getMarca());
        $this->assertEquals(279.0, $cpu->getPrecioLanzamiento());
    }

    private function productoDeReferencia(): Product
    {
        // Producto marcado por el administrador como "solo referencia".
        $producto = $this->producto('X870E');
        $producto->update(['es_vendible' => false]);

        return $producto->fresh();
    }

    public function test_un_producto_de_referencia_no_se_compra(): void
    {
        $referencia = $this->productoDeReferencia();

        $this->assertFalse($referencia->esVendible());
        $this->assertFalse($referencia->estaDisponible());

        $carrito = $this->usuario()->carritoActivo();
        $this->assertFalse($carrito->agregarProducto($referencia, 1));
        $this->assertTrue($carrito->estaVacio());
    }

    public function test_todos_los_productos_del_catalogo_tienen_precio_y_se_pueden_comprar(): void
    {
        $this->assertSame(0, Product::where('precio', '<=', 0)->count());
        $this->assertSame(0, Product::where('es_vendible', false)->count());
        $this->assertTrue($this->producto('X870E')->estaDisponible());
    }

    public function test_un_producto_con_precio_se_puede_comprar(): void
    {
        $cpu = $this->producto('Ryzen 5 9600X');
        $usuario = $this->usuario();

        $this->actingAs($usuario)->post(route('cart.add', $cpu), ['cantidad' => 2])->assertRedirect();

        $this->assertSame(2, $usuario->carritoActivo()->getCantidadTotal());
        $this->assertEquals(558.0, $usuario->carritoActivo()->getSubtotal());
    }

    public function test_el_controlador_rechaza_agregar_productos_no_vendibles(): void
    {
        $usuario = $this->usuario();

        $this->actingAs($usuario)
            ->post(route('cart.add', $this->productoDeReferencia()))
            ->assertSessionHas('error');

        $this->assertTrue($usuario->carritoActivo()->estaVacio());
    }

    public function test_la_ficha_tecnica_muestra_las_especificaciones(): void
    {
        $this->actingAs($this->usuario())
            ->get(route('products.show', $this->producto('Ryzen 5 9600X')))
            ->assertOk()
            ->assertSee('Núcleos')
            ->assertSee('AM5');
    }

    public function test_el_listado_permite_buscar_por_marca(): void
    {
        $this->actingAs($this->usuario())
            ->get(route('products.index', ['buscar' => 'Nintendo']))
            ->assertOk()
            ->assertSee('Switch 2')
            ->assertDontSee('Ryzen');
    }

    public function test_compatibilidad_cpu_chipset_usa_el_socket(): void
    {
        $regla = new ComponentCompatibility();
        $cpu = $this->producto('Ryzen 5 9600X');

        $this->assertTrue($regla->evaluarCompatibilidad($cpu, $this->producto('B650')));
        $this->assertFalse($regla->evaluarCompatibilidad($cpu, $this->producto('Z890')));
        $this->assertStringContainsString('Socket distinto', $regla->obtenerRazonIncompatibilidad($cpu, $this->producto('Z890')));
    }

    public function test_dos_productos_del_mismo_tipo_no_son_compatibles(): void
    {
        $regla = new ComponentCompatibility();

        $this->assertFalse($regla->evaluarCompatibilidad($this->producto('Ryzen 5 9600X'), $this->producto('Ryzen 7 9700X')));
    }

    public function test_celular_y_nevera_no_tienen_relacion_de_compatibilidad(): void
    {
        $regla = new ComponentCompatibility();

        $this->assertFalse($regla->evaluarCompatibilidad($this->producto('iPhone 17'), $this->producto('LRMVS3006S')));
    }

    public function test_consola_y_televisor_se_evaluan_juntos(): void
    {
        $r = (new ComponentCompatibility())->evaluar($this->producto('Switch 2'), $this->producto('OLED65C5'));

        $this->assertTrue($r['compatible']);
        $this->assertStringContainsString('4K a 60 Hz', $r['mensaje']);
    }

    public function test_el_endpoint_de_compatibilidad_devuelve_el_estado(): void
    {
        $this->actingAs($this->usuario())
            ->postJson(route('compatibility.check'), [
                'product_a_id' => $this->producto('Ryzen 5 9600X')->id,
                'product_b_id' => $this->producto('Z890')->id,
            ])
            ->assertOk()
            ->assertJson(['es_compatible' => false, 'estado' => 'bad']);
    }

    public function test_el_comparador_compara_dos_productos_de_la_misma_categoria(): void
    {
        $this->actingAs($this->usuario())
            ->get(route('comparator.index', [
                'product_a_id' => $this->producto('iPhone 17 Pro Max')->id,
                'product_b_id' => $this->producto('Pixel 10')->id,
            ]))
            ->assertOk()
            ->assertSee('Mejor en especificaciones')
            ->assertSee('Precio de lanzamiento');
    }

    public function test_el_comparador_rechaza_categorias_distintas(): void
    {
        $this->actingAs($this->usuario())
            ->get(route('comparator.index', [
                'product_a_id' => $this->producto('iPhone 17')->id,
                'product_b_id' => $this->producto('OLED65C5')->id,
            ]))
            ->assertOk()
            ->assertSee('Solo se pueden comparar productos de la misma categoría.');
    }

    public function test_la_vista_todo_muestra_secciones_con_boton_ver_mas(): void
    {
        app()->setLocale('es');
        $categoria = Category::where('slug', 'gpu')->firstOrFail();

        $respuesta = $this->actingAs($this->usuario())->get(route('products.index'));

        $respuesta->assertOk()
            ->assertSee('Destacados en')
            ->assertSee('Ver más')
            ->assertSee(route('products.index', ['category_id' => $categoria->id]), false)
            ->assertDontSee('Por confirmar');

        // Cada categoria muestra como maximo 3 productos destacados.
        foreach ($respuesta->viewData('secciones') as $seccion) {
            $this->assertLessThanOrEqual(3, $seccion['productos']->count());
        }
        $this->assertCount(14, $respuesta->viewData('secciones'));
    }

    public function test_filtrar_por_categoria_lista_todos_sus_productos(): void
    {
        $categoria = Category::where('slug', 'gpu')->firstOrFail();

        $respuesta = $this->actingAs($this->usuario())
            ->get(route('products.index', ['category_id' => $categoria->id]));

        $respuesta->assertOk();
        $this->assertCount(14, $respuesta->viewData('products'));
    }
}
