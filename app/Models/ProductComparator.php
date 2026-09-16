<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProductComparator extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'nombre', 'email', 'telefono', 'contraseña'];

    protected $hidden = ['contraseña'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function products()
    {
        return $this->belongsToMany(Product::class, 'comparator_product');
    }

    public function agregarAlComparador(Product $producto): void
    {
        $this->products()->syncWithoutDetaching([$producto->getId()]);
    }

    public function removerDelComparador(Product $producto): void
    {
        $this->products()->detach($producto->getId());
    }

    public function generarCuadroComparativo()
    {
        return $this->products()->with('category')->get()->map(function (Product $producto) {
            return [
                'id' => $producto->getId(),
                'nombre' => $producto->getNombre(),
                'categoria' => $producto->getNombreCategoria(),
                'precio' => $producto->getPrecio(),
                'stock' => $producto->getStock(),
                'disponible' => $producto->estaDisponible(),
            ];
        });
    }

    /**
     * Verifica, par por par, si los productos agregados al comparador
     * son compatibles entre si.
     */
    public function verificarArmado(): array
    {
        $productos = $this->products()->get();
        $compatibilidad = new ComponentCompatibility();
        $resultado = [];

        for ($i = 0; $i < count($productos); $i++) {
            for ($j = $i + 1; $j < count($productos); $j++) {
                $a = $productos[$i];
                $b = $productos[$j];
                $esCompatible = $compatibilidad->evaluarCompatibilidad($a, $b);

                $resultado[] = [
                    'producto_a' => $a->getNombre(),
                    'producto_b' => $b->getNombre(),
                    'compatible' => $esCompatible,
                    'razon' => $esCompatible ? null : $compatibilidad->obtenerRazonIncompatibilidad($a, $b),
                ];
            }
        }

        return $resultado;
    }

    /**
     * Obtiene (o crea) el comparador que pertenece a un usuario.
     * Cada usuario tiene el suyo (relacion 1 a 1).
     */
    public static function paraUsuario(User $usuario): self
    {
        return self::firstOrCreate(
            ['user_id' => $usuario->getId()],
            [
                'nombre' => 'Comparador de '.$usuario->getNombre(),
                'email' => $usuario->getEmail(),
                'telefono' => $usuario->getTelefono(),
                'contraseña' => $usuario->getAuthPassword(),
            ]
        );
    }

    /**
     * Vacia el comparador.
     */
    public function vaciar(): void
    {
        $this->products()->detach();
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

    public function getUsuario(): ?User
    {
        return $this->user;
    }

    public function setUsuario(User $usuario): static
    {
        $this->setAttribute('user_id', $usuario->getId());

        return $this;
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Product> */
    public function getProductos()
    {
        return $this->products()->get();
    }

    public function getCantidadProductos(): int
    {
        return $this->products()->count();
    }
}
