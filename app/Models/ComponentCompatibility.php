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
        // Regla principal: dos productos del mismo tipo cumplen el mismo
        // rol (dos procesadores, dos tarjetas graficas, dos celulares,
        // dos televisores, etc.) y por lo tanto NUNCA son compatibles,
        // sin importar si hay un registro manual o si comparten categoria.
        $tipoA = $this->obtenerTipoProducto($productoA);
        $tipoB = $this->obtenerTipoProducto($productoB);

        if ($tipoA !== null && $tipoA === $tipoB) {
            return false;
        }

        $registro = $this->buscarRegistro($productoA, $productoB);

        if ($registro) {
            return (bool) $registro->es_compatible;
        }

        // Sin registro manual: se asume compatibilidad entre productos
        // de la misma categoria (p. ej. dos componentes de PC distintos).
        return $productoA->getCategoryId() === $productoB->getCategoryId();
    }

    public function obtenerRazonIncompatibilidad(Product $productoA, Product $productoB): ?string
    {
        $tipoA = $this->obtenerTipoProducto($productoA);
        $tipoB = $this->obtenerTipoProducto($productoB);

        if ($tipoA !== null && $tipoA === $tipoB) {
            return 'Ambos productos cumplen la misma funcion (son del mismo tipo), por lo que no tiene sentido usarlos juntos.';
        }

        $registro = $this->buscarRegistro($productoA, $productoB);

        if ($registro && ! $registro->es_compatible) {
            return $registro->razon_incompatibilidad ?? 'Incompatibilidad registrada sin motivo especifico.';
        }

        if ($productoA->getCategoryId() !== $productoB->getCategoryId()) {
            return 'Los productos pertenecen a categorias distintas.';
        }

        return null;
    }

    /**
     * Infiere el "tipo" de un producto a partir de palabras clave en su
     * nombre (procesador, tarjeta grafica, smartphone, televisor, etc).
     * Devuelve null si no se reconoce ningun tipo conocido.
     */
    private function obtenerTipoProducto(Product $producto): ?string
    {
        $nombre = mb_strtolower((string) $producto->getNombre());

        $tipos = [
            'procesador' => ['procesador'],
            'tarjeta_madre' => ['tarjeta madre'],
            'memoria_ram' => ['memoria ram'],
            'tarjeta_grafica' => ['tarjeta grafica', 'tarjeta gráfica'],
            'disco_ssd' => ['disco ssd'],
            'disco_duro' => ['disco duro'],
            'fuente_poder' => ['fuente de poder'],
            'gabinete' => ['gabinete'],
            'disipador' => ['disipador'],
            'monitor' => ['monitor'],
            'teclado' => ['teclado'],
            'smartphone' => ['smartphone'],
            'laptop' => ['laptop'],
            'audifonos' => ['audifonos', 'audífonos'],
            'smartwatch' => ['smartwatch'],
            'tablet' => ['tablet'],
            'parlante' => ['parlante'],
            'televisor' => ['televisor', 'television', 'televisión'],
            'consola' => ['consola'],
            'camara' => ['camara', 'cámara'],
            'impresora' => ['impresora'],
            'router' => ['router'],
        ];

        foreach ($tipos as $tipo => $palabrasClave) {
            foreach ($palabrasClave as $palabra) {
                if (str_contains($nombre, $palabra)) {
                    return $tipo;
                }
            }
        }

        return null;
    }

    private function buscarRegistro(Product $productoA, Product $productoB)
    {
        return \DB::table('product_compatibilities')
            ->where(function ($query) use ($productoA, $productoB) {
                $query->where('product_a_id', $productoA->getId())
                      ->where('product_b_id', $productoB->getId());
            })
            ->orWhere(function ($query) use ($productoA, $productoB) {
                $query->where('product_a_id', $productoB->getId())
                      ->where('product_b_id', $productoA->getId());
            })
            ->first();
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

    public function getEmail(): ?string
    {
        return $this->getAttribute('email');
    }

    public function setEmail(string $email): static
    {
        $this->setAttribute('email', $email);

        return $this;
    }

    public function getTelefono(): ?string
    {
        return $this->getAttribute('telefono');
    }

    public function setTelefono(?string $telefono): static
    {
        $this->setAttribute('telefono', $telefono);

        return $this;
    }

    public function setContrasena(string $contrasena): static
    {
        $this->setAttribute('contraseña', $contrasena);

        return $this;
    }

    public function getComparador(): ?ProductComparator
    {
        return $this->productComparator;
    }

    public function setComparador(ProductComparator $comparador): static
    {
        $this->setAttribute('product_comparator_id', $comparador->getId());

        return $this;
    }
}
