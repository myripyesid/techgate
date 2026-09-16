<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Clase "Formas de pago" del diagrama de clases.
 *
 * Atributos: id, tipo, numeroTarjeta, titular.
 * Operaciones: validarMetodoPago(), procesarTransaccion(monto).
 */
class PaymentMethod extends Model
{
    use HasFactory;

    public const TIPOS = [
        'tarjeta_credito' => 'Tarjeta de credito',
        'tarjeta_debito' => 'Tarjeta de debito',
        'pse' => 'PSE / Transferencia',
        'efectivo' => 'Efectivo contra entrega',
    ];

    protected $fillable = ['user_id', 'tipo', 'numeroTarjeta', 'titular'];

    protected $hidden = ['numeroTarjeta'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Operaciones del diagrama
    |--------------------------------------------------------------------------
    */

    /**
     * validarMetodoPago()
     *
     * Verifica que el tipo sea valido y, si es tarjeta, que el numero
     * tenga el formato correcto (algoritmo de Luhn) y que haya titular.
     */
    public function validarMetodoPago(): bool
    {
        if (! array_key_exists((string) $this->getAttribute('tipo'), self::TIPOS)) {
            return false;
        }

        if (! $this->requiereTarjeta()) {
            return true;
        }

        $numero = preg_replace('/\D/', '', (string) $this->getNumeroTarjeta());

        if (strlen($numero) < 13 || strlen($numero) > 19) {
            return false;
        }

        if (trim((string) $this->getTitular()) === '') {
            return false;
        }

        return self::validarLuhn($numero);
    }

    /**
     * procesarTransaccion(monto)
     *
     * Simula el cobro contra la pasarela de pagos. Devuelve true si el
     * cobro se realizo correctamente.
     */
    public function procesarTransaccion(float $monto): bool
    {
        if ($monto <= 0) {
            return false;
        }

        if (! $this->validarMetodoPago()) {
            return false;
        }

        // Aqui iria la integracion real con la pasarela de pagos.
        return true;
    }

    /*
    |--------------------------------------------------------------------------
    | Utilidades
    |--------------------------------------------------------------------------
    */

    public function requiereTarjeta(): bool
    {
        return in_array($this->getAttribute('tipo'), ['tarjeta_credito', 'tarjeta_debito'], true);
    }

    public function tipoLegible(): string
    {
        return self::TIPOS[$this->getAttribute('tipo')] ?? (string) $this->getAttribute('tipo');
    }

    /**
     * Muestra solo los ultimos 4 digitos de la tarjeta.
     */
    public function numeroEnmascarado(): string
    {
        if (! $this->requiereTarjeta() || ! $this->getNumeroTarjeta()) {
            return '-';
        }

        $numero = preg_replace('/\D/', '', (string) $this->getNumeroTarjeta());

        return '**** **** **** '.substr($numero, -4);
    }

    public function etiqueta(): string
    {
        return $this->requiereTarjeta()
            ? $this->tipoLegible().' '.$this->numeroEnmascarado()
            : $this->tipoLegible();
    }

    private static function validarLuhn(string $numero): bool
    {
        $suma = 0;
        $alternar = false;

        for ($i = strlen($numero) - 1; $i >= 0; $i--) {
            $digito = (int) $numero[$i];

            if ($alternar) {
                $digito *= 2;
                if ($digito > 9) {
                    $digito -= 9;
                }
            }

            $suma += $digito;
            $alternar = ! $alternar;
        }

        return $suma % 10 === 0;
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

    public function getTipo(): ?string
    {
        return $this->getAttribute('tipo');
    }

    public function setTipo(string $tipo): static
    {
        if (! array_key_exists($tipo, self::TIPOS)) {
            throw new \InvalidArgumentException("Tipo de pago no válido: {$tipo}");
        }

        $this->setAttribute('tipo', $tipo);

        return $this;
    }

    /**
     * Devuelve el numero completo. Para mostrarlo en pantalla se usa
     * getNumeroEnmascarado().
     */
    public function getNumeroTarjeta(): ?string
    {
        return $this->getAttribute('numeroTarjeta');
    }

    public function setNumeroTarjeta(?string $numero): static
    {
        $this->setAttribute(
            'numeroTarjeta',
            $numero === null ? null : preg_replace('/\s+/', '', $numero)
        );

        return $this;
    }

    public function getNumeroEnmascarado(): string
    {
        return $this->numeroEnmascarado();
    }

    public function getTitular(): ?string
    {
        return $this->getAttribute('titular');
    }

    public function setTitular(?string $titular): static
    {
        $this->setAttribute('titular', $titular);

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->getAttribute('user_id');
    }

    public function setUserId(int $userId): static
    {
        $this->setAttribute('user_id', $userId);

        return $this;
    }

    public function getUsuario(): ?User
    {
        return $this->user;
    }

    public function setUsuario(User $usuario): static
    {
        return $this->setUserId($usuario->getId());
    }

    public function getTipoLegible(): string
    {
        return $this->tipoLegible();
    }

    public function getEtiqueta(): string
    {
        return $this->etiqueta();
    }
}
