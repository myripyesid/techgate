<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CartOrderTest extends TestCase
{
    use RefreshDatabase;

    private function producto(int $stock = 10, float $precio = 100): Product
    {
        $categoria = Category::firstOrCreate(['nombre' => 'Componentes de PC']);

        return Product::create([
            'category_id' => $categoria->id,
            'nombre' => 'Procesador de prueba '.uniqid(),
            'precio' => $precio,
            'stock' => $stock,
        ]);
    }

    public function test_usuario_puede_agregar_productos_al_carrito(): void
    {
        $usuario = User::factory()->create();
        $producto = $this->producto();

        $this->actingAs($usuario)
            ->post(route('cart.add', $producto), ['cantidad' => 2])
            ->assertRedirect();

        $carrito = $usuario->carritoActivo();

        $this->assertSame(2, $carrito->getCantidadTotal());
        $this->assertEquals(200.0, $carrito->getSubtotal());
    }

    public function test_no_se_puede_agregar_mas_cantidad_que_el_stock(): void
    {
        $usuario = User::factory()->create();
        $producto = $this->producto(stock: 3);

        $carrito = $usuario->carritoActivo();

        $this->assertFalse($carrito->agregarProducto($producto, 5));
        $this->assertTrue($carrito->estaVacio());
    }

    public function test_vaciar_carrito_elimina_las_lineas(): void
    {
        $usuario = User::factory()->create();
        $carrito = $usuario->carritoActivo();
        $carrito->agregarProducto($this->producto(), 1);

        $carrito->vaciarCarrito();

        $this->assertTrue($carrito->estaVacio());
    }

    public function test_procesar_pedido_descuenta_stock_y_vacia_el_carrito(): void
    {
        $usuario = User::factory()->create();
        $producto = $this->producto(stock: 10, precio: 50);

        $formaPago = PaymentMethod::create([
            'user_id' => $usuario->id,
            'tipo' => 'tarjeta_credito',
            'numeroTarjeta' => '4111111111111111',
            'titular' => 'Ana Perez',
        ]);

        $carrito = $usuario->carritoActivo();
        $carrito->agregarProducto($producto, 3);

        $pedido = Order::crearDesdeCarrito($usuario, $carrito, $formaPago);

        $this->assertTrue($pedido->procesarPedido());
        $this->assertSame(Order::ESTADO_PAGADO, $pedido->fresh()->getEstado());
        $this->assertEquals(150.0, $pedido->getTotal());
        $this->assertSame(7, $producto->fresh()->getStock());
        $this->assertTrue($carrito->fresh()->estaVacio());
    }

    public function test_cancelar_pedido_pagado_devuelve_el_stock(): void
    {
        $usuario = User::factory()->create();
        $producto = $this->producto(stock: 10, precio: 50);

        $formaPago = PaymentMethod::create([
            'user_id' => $usuario->id,
            'tipo' => 'efectivo',
        ]);

        $carrito = $usuario->carritoActivo();
        $carrito->agregarProducto($producto, 4);

        $pedido = Order::crearDesdeCarrito($usuario, $carrito, $formaPago);
        $pedido->procesarPedido();

        $this->assertSame(6, $producto->fresh()->getStock());

        $this->assertTrue($pedido->cancelarPedido());
        $this->assertSame(10, $producto->fresh()->getStock());
        $this->assertSame(Order::ESTADO_CANCELADO, $pedido->fresh()->getEstado());
    }

    public function test_forma_de_pago_con_tarjeta_invalida_no_valida(): void
    {
        $formaPago = new PaymentMethod([
            'tipo' => 'tarjeta_credito',
            'numeroTarjeta' => '1234567890123',
            'titular' => 'Ana Perez',
        ]);

        $this->assertFalse($formaPago->validarMetodoPago());
    }

    public function test_generar_factura_devuelve_los_datos_del_pedido(): void
    {
        $usuario = User::factory()->create(['nombre' => 'Ana']);
        $producto = $this->producto(precio: 25);

        $formaPago = PaymentMethod::create([
            'user_id' => $usuario->id,
            'tipo' => 'pse',
        ]);

        $carrito = $usuario->carritoActivo();
        $carrito->agregarProducto($producto, 2);

        $pedido = Order::crearDesdeCarrito($usuario, $carrito, $formaPago);
        $factura = $pedido->generarFactura();

        $this->assertSame('Ana', $factura['cliente']['nombre']);
        $this->assertCount(1, $factura['lineas']);
        $this->assertEquals(50.0, $factura['total']);
    }
}
