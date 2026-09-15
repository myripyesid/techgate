<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ComponentCompatibility extends Model
{
    use HasFactory;

    protected $fillable = ['product_comparator_id', 'nombre', 'email', 'telefono', 'contraseña'];

    public function productComparator()
    {
        return $this->belongsTo(ProductComparator::class);
    }

    public function evaluarCompatibilidad(Product $productoA, Product $productoB): bool
    {
        $registro = \DB::table('product_compatibilities')
            ->where(function ($query) use ($productoA, $productoB) {
                $query->where('product_a_id', $productoA->id)
                      ->where('product_b_id', $productoB->id);
            })
            ->orWhere(function ($query) use ($productoA, $productoB) {
                $query->where('product_a_id', $productoB->id)
                      ->where('product_b_id', $productoA->id);
            })
            ->first();

        return $registro ? (bool) $registro->es_compatible : true;
    }

    public function obtenerRazonIncompatibilidad(Product $productoA, Product $productoB): ?string
    {
        $registro = \DB::table('product_compatibilities')
            ->where(function ($query) use ($productoA, $productoB) {
                $query->where('product_a_id', $productoA->id)
                      ->where('product_b_id', $productoB->id);
            })
            ->orWhere(function ($query) use ($productoA, $productoB) {
                $query->where('product_a_id', $productoB->id)
                      ->where('product_b_id', $productoA->id);
            })
            ->first();

        return $registro ? $registro->razon_incompatibilidad : 'No hay registro de incompatibilidad especificado.';
    }
}