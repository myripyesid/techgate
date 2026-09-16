<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Linea de detalle de un Pedido. Guarda una "foto" del producto
 * (nombre y precio) para que la factura no cambie si el producto
 * se edita o se elimina despues.
 */
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'product_id',
        'nombre_producto',
        'cantidad',
        'precio_unitario',
        'subtotal',
    ];

    protected function casts(): array
    {
        return [
            'cantidad' => 'integer',
            'precio_unitario' => 'decimal:2',
            'subtotal' => 'decimal:2',
        ];
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
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

    public function getNombreProducto(): string
    {
        return (string) $this->getAttribute('nombre_producto');
    }

    public function setNombreProducto(string $nombre): static
    {
        $this->setAttribute('nombre_producto', $nombre);

        return $this;
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
        $this->setAttribute('precio_unitario', $precio);

        return $this;
    }

    public function getSubtotal(): float
    {
        return (float) $this->getAttribute('subtotal');
    }

    public function setSubtotal(float $subtotal): static
    {
        $this->setAttribute('subtotal', $subtotal);

        return $this;
    }

    public function getProducto(): ?Product
    {
        return $this->product;
    }

    public function getPedido(): ?Order
    {
        return $this->order;
    }
}
