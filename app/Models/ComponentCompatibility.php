<?php
// Autor: Marbin
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use App\Services\CompatibilityService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class ComponentCompatibility extends Model
{
    use HasFactory;

    protected $fillable = ['product_comparator_id', 'nombre', 'email', 'telefono', 'contraseña'];

    public function productComparator()
    {
        return $this->belongsTo(ProductComparator::class);
    }

    // Resultado completo de la verificacion: estado (ok | warn | bad | na |
    // ninguna), mensaje y bandera "compatible". Un registro manual en
    // product_compatibilities tiene prioridad sobre las reglas automaticas,
    // salvo que los dos productos sean del mismo tipo.
    public function evaluar(Product $productoA, Product $productoB): array
    {
        $servicio = app(CompatibilityService::class);
        $automatico = $servicio->evaluar($productoA, $productoB);

        $mismoTipo = $productoA->getCategorySlug() !== null
            && $productoA->getCategorySlug() === $productoB->getCategorySlug();

        if ($mismoTipo) {
            return $automatico;
        }

        $registro = $this->buscarRegistro($productoA, $productoB);

        if ($registro) {
            $compatible = (bool) $registro->es_compatible;

            return [
                'estado' => $compatible ? 'ok' : 'bad',
                'mensaje' => $compatible
                    ? 'Compatibilidad confirmada por un registro manual.'
                    : ($registro->razon_incompatibilidad ?? 'Incompatibilidad registrada sin motivo especifico.'),
                'compatible' => $compatible,
            ];
        }

        return $automatico;
    }

    public function evaluarCompatibilidad(Product $productoA, Product $productoB): bool
    {
        return $this->evaluar($productoA, $productoB)['compatible'];
    }

    public function obtenerRazonIncompatibilidad(Product $productoA, Product $productoB): ?string
    {
        $resultado = $this->evaluar($productoA, $productoB);

        return $resultado['compatible'] ? null : $resultado['mensaje'];
    }

    private function buscarRegistro(Product $productoA, Product $productoB)
    {
        return DB::table('product_compatibilities')
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
