<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase "Carrito de compras" del diagrama de clases.
 *
 * Atributos: id, fechaCreacion, estado, producto(s).
 * Operaciones: agregarProducto(), removerProducto(), calcularSubtotal(),
 *              vaciarCarrito().
 */
class Cart extends Model
{
    use HasFactory;

    public const ESTADO_ACTIVO = 'activo';
    public const ESTADO_CONVERTIDO = 'convertido';
    public const ESTADO_ABANDONADO = 'abandonado';

    protected $fillable = ['user_id', 'fechaCreacion', 'estado'];

    protected function casts(): array
    {
        return [
            'fechaCreacion' => 'datetime',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(CartItem::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'cart_items')
            ->withPivot(['cantidad', 'precio_unitario'])
            ->withTimestamps();
    }

    public function order()
    {
        return $this->hasOne(Order::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Operaciones del diagrama
    |--------------------------------------------------------------------------
    */

    /**
     * agregarProducto(Producto)
     *
     * Si el producto ya esta en el carrito suma la cantidad. Devuelve false
     * si no hay stock suficiente para la cantidad solicitada.
     */
    public function agregarProducto(Product $producto, int $cantidad = 1): bool
    {
        if ($cantidad < 1) {
            return false;
        }

        $item = $this->items()->where('product_id', $producto->getId())->first();
        $cantidadTotal = ($item?->getCantidad() ?? 0) + $cantidad;

        if ($producto->getStock() < $cantidadTotal) {
            return false;
        }

        if ($item) {
            $item->setCantidad($cantidadTotal)
                 ->setPrecioUnitario($producto->getPrecio());

            return $item->save();
        }

        $this->items()->create([
            'product_id' => $producto->getId(),
            'cantidad' => $cantidad,
            'precio_unitario' => $producto->getPrecio(),
        ]);

        return true;
    }

    /**
     * Cambia la cantidad de un producto ya presente en el carrito.
     */
    public function actualizarCantidad(Product $producto, int $cantidad): bool
    {
        $item = $this->items()->where('product_id', $producto->getId())->first();

        if (! $item) {
            return false;
        }

        if ($cantidad < 1) {
            return $this->removerProducto($producto);
        }

        if ($producto->getStock() < $cantidad) {
            return false;
        }

        $item->setCantidad($cantidad);

        return $item->save();
    }

    /**
     * removerProducto(Producto)
     */
    public function removerProducto(Product $producto): bool
    {
        return $this->items()->where('product_id', $producto->getId())->delete() > 0;
    }

    /**
     * calcularSubtotal()
     */
    public function calcularSubtotal(): float
    {
        return (float) $this->items->sum(
            fn (CartItem $item) => $item->calcularSubtotal()
        );
    }

    /**
     * vaciarCarrito()
     */
    public function vaciarCarrito(): void
    {
        $this->items()->delete();
        $this->load('items');
    }

    /*
    |--------------------------------------------------------------------------
    | Utilidades
    |--------------------------------------------------------------------------
    */

    public function estaVacio(): bool
    {
        return $this->items()->count() === 0;
    }

    public function cantidadTotal(): int
    {
        return (int) $this->items()->sum('cantidad');
    }

    /**
     * Devuelve el carrito activo del usuario, creandolo si no existe.
     */
    public static function activoPara(User $usuario): self
    {
        return self::firstOrCreate(
            [
                'user_id' => $usuario->getId(),
                'estado' => self::ESTADO_ACTIVO,
            ],
            [
                'fechaCreacion' => now(),
            ]
        );
    }

    /*
    |--------------------------------------------------------------------------
    | Getters y setters
    |--------------------------------------------------------------------------
    */

    public function getId(): ?int
    {
        return $this->getAttribute('id');
    }

    public function getFechaCreacion(): ?\Illuminate\Support\Carbon
    {
        return $this->getAttribute('fechaCreacion');
    }

    public function setFechaCreacion(\DateTimeInterface|string $fecha): static
    {
        $this->setAttribute('fechaCreacion', $fecha);

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->getAttribute('estado');
    }

    public function setEstado(string $estado): static
    {
        $permitidos = [self::ESTADO_ACTIVO, self::ESTADO_CONVERTIDO, self::ESTADO_ABANDONADO];

        if (! in_array($estado, $permitidos, true)) {
            throw new \InvalidArgumentException("Estado de carrito no válido: {$estado}");
        }

        $this->setAttribute('estado', $estado);

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->getAttribute('user_id');
    }

    public function setUserId(int $userId): static
    {
        $this->setAttribute('user_id', $userId);

        return $this;
    }

    public function getUsuario(): ?User
    {
        return $this->user;
    }

    public function setUsuario(User $usuario): static
    {
        return $this->setUserId($usuario->getId());
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, CartItem> */
    public function getItems()
    {
        return $this->items;
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Product> */
    public function getProductos()
    {
        return $this->products()->get();
    }

    public function getSubtotal(): float
    {
        return $this->calcularSubtotal();
    }

    public function getCantidadTotal(): int
    {
        return $this->cantidadTotal();
    }
}
