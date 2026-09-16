<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Linea del carrito de compras: relaciona un Carrito con un Producto
 * y guarda la cantidad y el precio al momento de agregarlo.
 */
class CartItem extends Model
{
    use HasFactory;

    protected $fillable = ['cart_id', 'product_id', 'cantidad', 'precio_unitario'];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_unitario' => 'decimal:2',
        ];
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function calcularSubtotal(): float
    {
        return (float) $this->getAttribute('precio_unitario') * (int) $this->getAttribute('cantidad');
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

    public function getCantidad(): int
    {
        return (int) $this->getAttribute('cantidad');
    }

    public function setCantidad(int $cantidad): static
    {
        if ($cantidad < 1) {
            throw new \InvalidArgumentException('La cantidad debe ser al menos 1.');
        }

        $this->setAttribute('cantidad', $cantidad);

        return $this;
    }

    public function getPrecioUnitario(): float
    {
        return (float) $this->getAttribute('precio_unitario');
    }

    public function setPrecioUnitario(float $precio): static
    {
        if ($precio < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo.');
        }

        $this->setAttribute('precio_unitario', $precio);

        return $this;
    }

    public function getProductId(): ?int
    {
        return $this->getAttribute('product_id');
    }

    public function getProducto(): ?Product
    {
        return $this->product;
    }

    public function setProducto(Product $producto): static
    {
        $this->setAttribute('product_id', $producto->getId());
        $this->setPrecioUnitario($producto->getPrecio());

        return $this;
    }

    public function getCarrito(): ?Cart
    {
        return $this->cart;
    }

    public function getSubtotal(): float
    {
        return $this->calcularSubtotal();
    }

    /**
     * Nombre del producto listo para mostrar (aunque haya sido eliminado).
     */
    public function getNombreProducto(): string
    {
        return $this->getProducto()?->getNombre() ?? 'Producto eliminado';
    }
}
