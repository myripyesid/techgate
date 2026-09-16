<?php

namespace Tests\Unit;

use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\Product;
use App\Models\User;
use InvalidArgumentException;
use Tests\TestCase;

/**
 * Comprueba que los getters y setters de los modelos funcionen y que
 * los setters validen los datos antes de asignarlos.
 */
class GettersSettersTest extends TestCase
{
    public function test_setters_de_producto_son_encadenables(): void
    {
        $producto = (new Product())
            ->setNombre('Procesador Intel Core i5')
            ->setPrecio(189.99)
            ->setStock(25);

        $this->assertSame('Procesador Intel Core i5', $producto->getNombre());
        $this->assertSame(189.99, $producto->getPrecio());
        $this->assertSame(25, $producto->getStock());
    }

    public function test_no_se_admite_un_precio_negativo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Product())->setPrecio(-10);
    }

    public function test_no_se_admite_un_stock_negativo(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Product())->setStock(-1);
    }

    public function test_setters_de_usuario(): void
    {
        $usuario = (new User())
            ->setNombre('Ana')
            ->setEmail('ana@techgate.com')
            ->setTelefono('3001234567')
            ->setRol(User::ROL_ADMINISTRADOR);

        $this->assertSame('Ana', $usuario->getNombre());
        $this->assertSame('ana@techgate.com', $usuario->getEmail());
        $this->assertSame('3001234567', $usuario->getTelefono());
        $this->assertTrue($usuario->esAdministrador());
    }

    public function test_el_rol_se_valida(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new User())->setRol('superusuario');
    }

    public function test_la_contrasena_solo_se_escribe_y_se_guarda_hasheada(): void
    {
        $usuario = (new User())->setContrasena('secreto123');

        $this->assertNotSame('secreto123', $usuario->getAuthPassword());
        $this->assertTrue($usuario->verificarContrasena('secreto123'));
        $this->assertFalse(method_exists($usuario, 'getContrasena'));
    }

    public function test_setter_de_numero_de_tarjeta_quita_los_espacios(): void
    {
        $formaPago = (new PaymentMethod())
            ->setTipo('tarjeta_credito')
            ->setNumeroTarjeta('4111 1111 1111 1111')
            ->setTitular('Ana Perez');

        $this->assertSame('4111111111111111', $formaPago->getNumeroTarjeta());
        $this->assertSame('**** **** **** 1111', $formaPago->getNumeroEnmascarado());
        $this->assertTrue($formaPago->validarMetodoPago());
    }

    public function test_el_tipo_de_pago_se_valida(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new PaymentMethod())->setTipo('criptomonedas');
    }

    public function test_el_estado_del_carrito_se_valida(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Cart())->setEstado('perdido');
    }

    public function test_el_estado_del_pedido_se_valida(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Order())->setEstado('en_la_luna');
    }
}
