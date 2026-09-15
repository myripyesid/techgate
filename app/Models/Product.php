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

    public function reducirStock(int $cantidad): bool
    {
        if ($this->stock >= $cantidad) {
            $this->stock -= $cantidad;
            return $this->save();
        }
        return false;
    }

    public function aumentarStock(int $cantidad): bool
    {
        $this->stock += $cantidad;
        return $this->save();
    }

    public function estaDisponible(): bool
    {
        return $this->stock > 0;
    }

    public function aplicarDescuento(float $porcentaje): float
    {
        $descuento = $this->precio * ($porcentaje / 100);
        $this->precio -= $descuento;
        $this->save();

        return $this->precio;
    }
}