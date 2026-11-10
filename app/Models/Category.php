<?php
// Autor: Kevin
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'slug', 'grupo', 'descripcion'];

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

    public function getSlug(): ?string
    {
        return $this->getAttribute('slug');
    }

    public function setSlug(?string $slug): static
    {
        $this->setAttribute('slug', $slug);

        return $this;
    }

    // pc | electronico
    public function getGrupo(): ?string
    {
        return $this->getAttribute('grupo');
    }

    public function setGrupo(?string $grupo): static
    {
        $this->setAttribute('grupo', $grupo);

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

    // @return \Illuminate\Database\Eloquent\Collection<int, Product>
    public function getProductos()
    {
        return $this->products()->get();
    }
}
