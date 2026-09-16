<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Clase "Pedido" del diagrama de clases.
 *
 * Atributos: id, fecha, estado, total.
 * Operaciones: procesarPedido(), actualizarEstado(nuevoEstado),
 *              cancelarPedido(), generarFactura().
 */
class Order extends Model
{
    use HasFactory;

    public const ESTADO_PENDIENTE = 'pendiente';
    public const ESTADO_PAGADO = 'pagado';
    public const ESTADO_ENVIADO = 'enviado';
    public const ESTADO_ENTREGADO = 'entregado';
    public const ESTADO_CANCELADO = 'cancelado';

    public const ESTADOS = [
        self::ESTADO_PENDIENTE,
        self::ESTADO_PAGADO,
        self::ESTADO_ENVIADO,
        self::ESTADO_ENTREGADO,
        self::ESTADO_CANCELADO,
    ];

    protected $fillable = ['user_id', 'payment_method_id', 'cart_id', 'fecha', 'estado', 'total'];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'total' => 'decimal:2',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    public function cart()
    {
        return $this->belongsTo(Cart::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Creacion del pedido a partir del carrito
    |--------------------------------------------------------------------------
    */

    /**
     * Crea un pedido "pendiente" copiando las lineas del carrito.
     * No descuenta stock todavia: eso ocurre en procesarPedido().
     */
    public static function crearDesdeCarrito(User $usuario, Cart $carrito, PaymentMethod $formaPago): self
    {
        $carrito->load('items.product');

        if ($carrito->estaVacio()) {
            throw new \RuntimeException('El carrito esta vacio.');
        }

        return DB::transaction(function () use ($usuario, $carrito, $formaPago) {
            $pedido = self::create([
                'user_id' => $usuario->getId(),
                'payment_method_id' => $formaPago->getId(),
                'cart_id' => $carrito->getId(),
                'fecha' => now(),
                'estado' => self::ESTADO_PENDIENTE,
                'total' => $carrito->getSubtotal(),
            ]);

            foreach ($carrito->getItems() as $item) {
                $pedido->items()->create([
                    'product_id' => $item->getProductId(),
                    'nombre_producto' => $item->getNombreProducto(),
                    'cantidad' => $item->getCantidad(),
                    'precio_unitario' => $item->getPrecioUnitario(),
                    'subtotal' => $item->getSubtotal(),
                ]);
            }

            return $pedido;
        });
    }

    /*
    |--------------------------------------------------------------------------
    | Operaciones del diagrama
    |--------------------------------------------------------------------------
    */

    /**
     * procesarPedido()
     *
     * Valida stock, cobra con la forma de pago asociada, descuenta el
     * stock de cada producto y marca el pedido como pagado.
     */
    public function procesarPedido(): bool
    {
        if ($this->getEstado() !== self::ESTADO_PENDIENTE) {
            return false;
        }

        $this->load('items.product', 'paymentMethod');

        if ($this->items->isEmpty()) {
            return false;
        }

        // 1. Verificar que haya stock suficiente para todas las lineas.
        foreach ($this->getItems() as $item) {
            $producto = $item->getProducto();

            if (! $producto || ! $producto->estaDisponible()) {
                return false;
            }

            if ($producto->getStock() < $item->getCantidad()) {
                return false;
            }
        }

        // 2. Cobrar.
        if (! $this->getFormaPago() || ! $this->getFormaPago()->procesarTransaccion($this->getTotal())) {
            return false;
        }

        // 3. Descontar stock y confirmar.
        return DB::transaction(function () {
            foreach ($this->getItems() as $item) {
                $item->getProducto()->reducirStock($item->getCantidad());
            }

            $this->actualizarEstado(self::ESTADO_PAGADO);

            if ($carrito = $this->getCarrito()) {
                $carrito->vaciarCarrito();
                $carrito->setEstado(Cart::ESTADO_CONVERTIDO)->save();
            }

            return true;
        });
    }

    /**
     * actualizarEstado(nuevoEstado)
     */
    public function actualizarEstado(string $nuevoEstado): bool
    {
        if (! in_array($nuevoEstado, self::ESTADOS, true)) {
            return false;
        }

        $this->setEstado($nuevoEstado);

        return $this->save();
    }

    /**
     * cancelarPedido()
     *
     * Si el pedido ya estaba pagado, devuelve el stock reservado.
     */
    public function cancelarPedido(): bool
    {
        if (! $this->sePuedeCancelar()) {
            return false;
        }

        return DB::transaction(function () {
            if (in_array($this->getEstado(), [self::ESTADO_PAGADO, self::ESTADO_ENVIADO], true)) {
                $this->load('items.product');

                foreach ($this->getItems() as $item) {
                    $item->getProducto()?->aumentarStock($item->getCantidad());
                }
            }

            return $this->actualizarEstado(self::ESTADO_CANCELADO);
        });
    }

    /**
     * generarFactura()
     *
     * Devuelve los datos de la factura del pedido.
     */
    public function generarFactura(): array
    {
        $this->load('items', 'user', 'paymentMethod');

        return [
            'numero_factura' => $this->getNumeroFactura(),
            'fecha' => $this->getFecha()?->format('d/m/Y H:i'),
            'estado' => $this->getEstado(),
            'cliente' => [
                'nombre' => $this->getUsuario()?->getNombre() ?? '-',
                'email' => $this->getUsuario()?->getEmail() ?? '-',
                'telefono' => $this->getUsuario()?->getTelefono() ?? '-',
            ],
            'forma_pago' => $this->getFormaPago()?->getEtiqueta() ?? 'No registrada',
            'lineas' => $this->getItems()->map(fn (OrderItem $item) => [
                'producto' => $item->getNombreProducto(),
                'cantidad' => $item->getCantidad(),
                'precio_unitario' => $item->getPrecioUnitario(),
                'subtotal' => $item->getSubtotal(),
            ])->all(),
            'total' => $this->getTotal(),
        ];
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

    public function getFecha(): ?\Illuminate\Support\Carbon
    {
        return $this->getAttribute('fecha');
    }

    public function setFecha(\DateTimeInterface|string $fecha): static
    {
        $this->setAttribute('fecha', $fecha);

        return $this;
    }

    public function getEstado(): ?string
    {
        return $this->getAttribute('estado');
    }

    /**
     * Setter del estado. No guarda en base de datos: para eso esta la
     * operacion actualizarEstado() del diagrama.
     */
    public function setEstado(string $estado): static
    {
        if (! in_array($estado, self::ESTADOS, true)) {
            throw new \InvalidArgumentException("Estado de pedido no válido: {$estado}");
        }

        $this->setAttribute('estado', $estado);

        return $this;
    }

    public function getTotal(): float
    {
        return (float) $this->getAttribute('total');
    }

    public function setTotal(float $total): static
    {
        if ($total < 0) {
            throw new \InvalidArgumentException('El total no puede ser negativo.');
        }

        $this->setAttribute('total', $total);

        return $this;
    }

    public function getUserId(): ?int
    {
        return $this->getAttribute('user_id');
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

    public function getFormaPago(): ?PaymentMethod
    {
        return $this->paymentMethod;
    }

    public function setFormaPago(PaymentMethod $formaPago): static
    {
        $this->setAttribute('payment_method_id', $formaPago->getId());

        return $this;
    }

    public function getCarrito(): ?Cart
    {
        return $this->cart;
    }

    public function setCarrito(Cart $carrito): static
    {
        $this->setAttribute('cart_id', $carrito->getId());

        return $this;
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, OrderItem> */
    public function getItems()
    {
        return $this->items;
    }

    public function getNumeroFactura(): string
    {
        return 'TG-'.str_pad((string) $this->getId(), 6, '0', STR_PAD_LEFT);
    }

    public function getCantidadTotal(): int
    {
        return (int) $this->items->sum('cantidad');
    }

    public function sePuedeCancelar(): bool
    {
        return ! in_array($this->getEstado(), [self::ESTADO_CANCELADO, self::ESTADO_ENTREGADO], true);
    }
}
