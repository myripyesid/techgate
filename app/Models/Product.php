<?php
// Autor: Marbin

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_id', 'nombre', 'marca', 'modelo', 'lanzamiento',
        'precio', 'precio_lanzamiento_usd', 'stock', 'es_vendible',
        'confianza', 'especificaciones',
    ];

    protected function casts(): array
    {
        return [
            'es_vendible' => 'boolean',
            'especificaciones' => 'array',
            'precio_lanzamiento_usd' => 'decimal:2',
        ];
    }

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

    // Un producto esta disponible para la venta si es vendible (tiene
    // precio confirmado y no es solo de referencia) y hay stock.
    public function estaDisponible(): bool
    {
        return $this->esVendible() && $this->getStock() > 0;
    }

    public function esVendible(): bool
    {
        return (bool) ($this->getAttribute('es_vendible') ?? true);
    }

    // Catalogo fusionado: especificaciones tecnicas

    // Devuelve todas las especificaciones (campo => valor).
    public function getEspecificaciones(): array
    {
        return $this->getAttribute('especificaciones') ?? [];
    }

    // Devuelve una especificacion puntual o null si no existe.
    public function getEspecificacion(string $campo): mixed
    {
        return $this->getEspecificaciones()[$campo] ?? null;
    }

    public function getMarca(): ?string
    {
        return $this->getAttribute('marca');
    }

    public function getModelo(): ?string
    {
        return $this->getAttribute('modelo');
    }

    public function getLanzamiento(): ?string
    {
        return $this->getAttribute('lanzamiento');
    }

    public function getPrecioLanzamiento(): ?float
    {
        $valor = $this->getAttribute('precio_lanzamiento_usd');

        return $valor === null ? null : (float) $valor;
    }

    public function getConfianza(): ?string
    {
        return $this->getAttribute('confianza');
    }

    // Slug de la categoria (cpu, gpu, celular, ...). Lo usan las reglas de
    // compatibilidad y de comparacion.
    public function getCategorySlug(): ?string
    {
        return $this->getCategoria()?->getSlug();
    }

    public function scopeVendibles($query)
    {
        return $query->where('es_vendible', true);
    }

    public function aplicarDescuento(float $porcentaje): float
    {
        $descuento = $this->getPrecio() * ($porcentaje / 100);
        $this->setPrecio($this->getPrecio() - $descuento);
        $this->save();

        return $this->getPrecio();
    }

    // Getters y setters

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

    // Nombre de la categoria, listo para mostrar en las vistas.
    public function getNombreCategoria(): string
    {
        return $this->getCategoria()?->getNombre() ?? 'N/A';
    }
}
