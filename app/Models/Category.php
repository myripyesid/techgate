<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'descripcion'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function mostrarDetallesCategoria(): array
    {
        return [
            'id' => $this->getId(),
            'nombre' => $this->getNombre(),
            'descripcion' => $this->getDescripcion(),
            'total_productos' => $this->products()->count(),
        ];
    }

    public function asociarProducto(Product $producto): bool
    {
        $producto->setCategoryId($this->getId());

        return $producto->save();
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

    public function getDescripcion(): ?string
    {
        return $this->getAttribute('descripcion');
    }

    public function setDescripcion(?string $descripcion): static
    {
        $this->setAttribute('descripcion', $descripcion);

        return $this;
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Product> */
    public function getProductos()
    {
        return $this->products()->get();
    }
}
