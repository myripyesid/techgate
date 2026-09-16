<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['category_id', 'nombre', 'precio', 'stock'];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function reducirStock(int $cantidad): bool
    {
        if ($this->getStock() >= $cantidad) {
            $this->setStock($this->getStock() - $cantidad);

            return $this->save();
        }

        return false;
    }

    public function aumentarStock(int $cantidad): bool
    {
        $this->setStock($this->getStock() + $cantidad);

        return $this->save();
    }

    public function estaDisponible(): bool
    {
        return $this->getStock() > 0;
    }

    public function aplicarDescuento(float $porcentaje): float
    {
        $descuento = $this->getPrecio() * ($porcentaje / 100);
        $this->setPrecio($this->getPrecio() - $descuento);
        $this->save();

        return $this->getPrecio();
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

    public function getNombre(): ?string
    {
        return $this->getAttribute('nombre');
    }

    public function setNombre(string $nombre): static
    {
        $this->setAttribute('nombre', $nombre);

        return $this;
    }

    public function getPrecio(): float
    {
        return (float) $this->getAttribute('precio');
    }

    public function setPrecio(float $precio): static
    {
        if ($precio < 0) {
            throw new \InvalidArgumentException('El precio no puede ser negativo.');
        }

        $this->setAttribute('precio', $precio);

        return $this;
    }

    public function getStock(): int
    {
        return (int) $this->getAttribute('stock');
    }

    public function setStock(int $stock): static
    {
        if ($stock < 0) {
            throw new \InvalidArgumentException('El stock no puede ser negativo.');
        }

        $this->setAttribute('stock', $stock);

        return $this;
    }

    public function getCategoryId(): ?int
    {
        return $this->getAttribute('category_id');
    }

    public function setCategoryId(int $categoryId): static
    {
        $this->setAttribute('category_id', $categoryId);

        return $this;
    }

    public function getCategoria(): ?Category
    {
        return $this->category;
    }

    public function setCategoria(Category $categoria): static
    {
        return $this->setCategoryId($categoria->getId());
    }

    /**
     * Nombre de la categoria, listo para mostrar en las vistas.
     */
    public function getNombreCategoria(): string
    {
        return $this->getCategoria()?->getNombre() ?? 'N/A';
    }
}
