<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductComparator extends Model
{
    use HasFactory;

    protected $fillable = ['nombre', 'email', 'telefono', 'contraseña'];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'comparator_product');
    }

    public function agregarAlComparador(Product $producto): void
    {
        $this->products()->syncWithoutDetaching([$producto->id]);
    }

    public function removerDelComparador(Product $producto): void
    {
        $this->products()->detach($producto->id);
    }

    public function generarCuadroComparativo()
    {
        return $this->products()->with('category')->get();
    }

    public function verificarArmado(): bool
    {
        // Lógica básica de verificación de ensamble
        return $this->products()->count() > 1;
    }
}