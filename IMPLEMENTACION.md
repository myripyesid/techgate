# TechGate — Implementación de las clases faltantes y login por roles

## 1. Cómo levantarlo

Las columnas de la tabla `users` cambiaron (ahora son `nombre`, `telefono`,
`contraseña`, `rol`), así que hay que rehacer la base de datos:

```sh
composer install
php artisan migrate:fresh --seed
php artisan serve
```

Cuentas creadas por el seeder:

| Rol           | Email                  | Contraseña   |
|---------------|------------------------|--------------|
| Administrador | admin@techgate.com     | admin1234    |
| Usuario       | usuario@techgate.com   | usuario1234  |

El registro público (`/registro`) siempre crea usuarios con rol `usuario`.

## 2. Clases del diagrama y su implementación

| Clase del diagrama          | Modelo              | Tabla(s)                        |
|-----------------------------|---------------------|---------------------------------|
| Usuario                     | `App\Models\User`             | `users`                 |
| Formas de pago              | `App\Models\PaymentMethod`    | `payment_methods`       |
| Carrito de compras          | `App\Models\Cart` + `CartItem`| `carts`, `cart_items`   |
| Pedido                      | `App\Models\Order` + `OrderItem` | `orders`, `order_items` |
| Producto                    | `App\Models\Product`          | `products`              |
| Categorías                  | `App\Models\Category`         | `categories`            |
| Comparador de productos     | `App\Models\ProductComparator`| `product_comparators`   |
| Compatibilidad entre comp.  | `App\Models\ComponentCompatibility` | `component_compatibilities` |

Las operaciones del diagrama se implementaron con el mismo nombre:

- **Usuario**: `iniciarSesion()`, `cerrarSesion()`, `actualizarPerfil()`, `cambiarContrasena()`.
- **Formas de pago**: `validarMetodoPago()` (valida tipo, titular y número con
  algoritmo de Luhn), `procesarTransaccion(monto)`.
- **Carrito de compras**: `agregarProducto()`, `removerProducto()`,
  `calcularSubtotal()`, `vaciarCarrito()`.
- **Pedido**: `procesarPedido()` (valida stock → cobra → descuenta stock →
  marca pagado → vacía el carrito), `actualizarEstado()`, `cancelarPedido()`
  (devuelve el stock si ya estaba pagado), `generarFactura()`.

Detalle de diseño: `cart_items` y `order_items` guardan la cantidad y el precio
unitario. En `order_items` además se copia el nombre del producto, para que una
factura vieja no cambie si después se edita o borra el producto.

## 3. Login y separación de roles

- La columna `users.rol` acepta `usuario` o `administrador`.
- La columna de la contraseña se llama `contraseña` (como en el diagrama), por
  eso `User` sobreescribe `getAuthPassword()` y `getAuthPasswordName()`.
- El middleware `App\Http\Middleware\EnsureUserIsAdmin` está registrado con el
  alias `admin` en `bootstrap/app.php`.

Reparto de permisos en `routes/web.php`:

**Solo el usuario**
- Carrito de compras (`/carrito`)
- Pedidos y factura (`/pedidos`)
- Formas de pago (`/formas-de-pago`)
- Comparador de productos (`/comparador`)
- Compatibilidad entre componentes (`/compatibilidad`)

**Solo el administrador**
- Crear, editar y eliminar productos (`/productos/nuevo`, `/productos/{id}/editar`, …)
- Crear categorías (`/categorias/nueva`)
- Ver y cambiar el estado de todos los pedidos (`/admin/pedidos`)

**Ambos (autenticados)**: ver el catálogo, ver categorías y editar su perfil.

El menú de navegación se arma según el rol, y aunque alguien escriba la URL a
mano el middleware `admin` responde 403.

## 4. Flujo de compra

1. El usuario agrega productos al carrito desde el catálogo.
2. Registra una forma de pago en `/formas-de-pago`.
3. Desde el carrito elige la forma de pago y genera el pedido:
   `Order::crearDesdeCarrito()` crea el pedido en estado `pendiente` y
   `procesarPedido()` lo cobra, descuenta stock y lo pasa a `pagado`.
4. Se muestra la factura (`generarFactura()`), que se puede cancelar mientras
   el pedido no esté entregado.

## 5. Getters y setters

Todos los modelos exponen getters y setters explícitos además de las
operaciones del diagrama. Los setters devuelven `$this`, así que se pueden
encadenar, y validan antes de asignar:

```php
$producto = (new Product())
    ->setNombre('Procesador Intel Core i5-13400F')
    ->setCategoryId($categoria->getId())
    ->setPrecio(189.99)
    ->setStock(25);

$producto->save();

echo $producto->getNombre();          // Procesador Intel Core i5-13400F
echo $producto->getNombreCategoria(); // Componentes de PC
$producto->setPrecio(-5);             // InvalidArgumentException
```

Validaciones que hacen los setters:

- `Product::setPrecio()` y `setStock()` rechazan valores negativos.
- `User::setRol()` solo acepta `usuario` o `administrador`.
- `PaymentMethod::setTipo()` solo acepta los tipos de `PaymentMethod::TIPOS`;
  `setNumeroTarjeta()` normaliza el número quitando espacios.
- `Cart::setEstado()` y `Order::setEstado()` solo aceptan estados del dominio.
- `CartItem::setCantidad()` y `OrderItem::setCantidad()` exigen cantidad ≥ 1.

Sobre la contraseña: existe `setContrasena()` (la guarda hasheada) pero **no**
existe `getContrasena()`, porque no tiene sentido leerla en texto plano. Para
compararla se usa `verificarContrasena($textoPlano)`.

Algunos getters son de conveniencia y evitan repetir lógica en las vistas:
`Product::getNombreCategoria()`, `Cart::getSubtotal()`,
`CartItem::getNombreProducto()`, `Order::getNumeroFactura()`,
`Order::getCantidadTotal()`, `Order::sePuedeCancelar()`,
`PaymentMethod::getNumeroEnmascarado()` y `getEtiqueta()`.

Los métodos internos de los modelos, los controladores y las vistas Blade usan
estos getters y setters en lugar de tocar los atributos directamente.

## 6. Pruebas

```sh
php artisan test
```

Se agregaron `tests/Feature/AuthTest.php` (login, registro, logout) y
`tests/Feature/CartOrderTest.php` (carrito, stock, procesamiento y cancelación
de pedidos, validación de tarjeta, factura), más
`tests/Unit/GettersSettersTest.php`, que comprueba el encadenamiento y las
validaciones de los setters. `ProductWebTest` ahora comprueba también que un
usuario normal reciba 403 al intentar crear productos.

## 7. Nota sobre el entorno

Este proyecto usa Laravel 13, que requiere **PHP 8.4 o superior**. Si al correr
`php artisan` aparece un error de "platform check", actualiza PHP.
