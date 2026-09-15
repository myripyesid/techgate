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
            'id' => $this->id,
            'nombre' => $this->nombre,
            'descripcion' => $this->descripcion,
            'total_productos' => $this->products()->count(),
        ];
    }

    public function asociarProducto(Product $producto): bool
    {
        $producto->category_id = $this->id;
        return $producto->save();
    }
}