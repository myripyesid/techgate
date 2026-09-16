<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Clase "Usuario" del diagrama de clases.
 *
 * Atributos: id, nombre, email, telefono, contraseña (+ rol).
 * Operaciones: iniciarSesion(), cerrarSesion(), actualizarPerfil(),
 *              cambiarContrasena().
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROL_USUARIO = 'usuario';
    public const ROL_ADMINISTRADOR = 'administrador';

    protected $fillable = [
        'name',
        'email',
        'telefono',
        'password',
        'rol',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'password' => 'hashed',
        ];
    }

    /*
    |--------------------------------------------------------------------------
    | Integracion con el sistema de autenticacion de Laravel
    |--------------------------------------------------------------------------
    | La columna de la contraseña se llama "contraseña" (segun el diagrama)
    | en vez del "password" por defecto, por lo que hay que indicarselo al
    | guard de autenticacion.
    */

    public function getAuthPasswordName(): string
    {
        return 'contraseña';
    }

    public function getAuthPassword(): string
    {
        return (string) $this->getAttribute('contraseña');
    }

    /*
    |--------------------------------------------------------------------------
    | Relaciones
    |--------------------------------------------------------------------------
    */

    public function carts()
    {
        return $this->hasMany(Cart::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function paymentMethods()
    {
        return $this->hasMany(PaymentMethod::class);
    }

    public function productComparator()
    {
        return $this->hasOne(ProductComparator::class);
    }

    /*
    |--------------------------------------------------------------------------
    | Roles
    |--------------------------------------------------------------------------
    */

    public function esAdministrador(): bool
    {
        return $this->getRol() === self::ROL_ADMINISTRADOR;
    }

    public function esUsuario(): bool
    {
        return $this->getRol() === self::ROL_USUARIO;
    }

    /*
    |--------------------------------------------------------------------------
    | Operaciones del diagrama
    |--------------------------------------------------------------------------
    */

    /**
     * iniciarSesion(email, contrasena)
     *
     * Valida las credenciales y, si son correctas, deja al usuario
     * autenticado en la sesion.
     */
    public static function iniciarSesion(string $email, string $contrasena, bool $recordar = false): bool
    {
        return Auth::attempt(
            ['email' => $email, 'password' => $contrasena],
            $recordar
        );
    }

    /**
     * cerrarSesion()
     */
    public function cerrarSesion(): void
    {
        Auth::logout();
    }

    /**
     * actualizarPerfil(nuevosDatos)
     *
     * Solo permite modificar datos de perfil; nunca el rol ni la
     * contraseña (para eso existe cambiarContrasena).
     */
    public function actualizarPerfil(array $nuevosDatos): bool
    {
        // Si la solicitud envía 'nombre', la remapeamos a 'name'
        if (isset($nuevosDatos['nombre'])) {
            $nuevosDatos['name'] = $nuevosDatos['nombre'];
            unset($nuevosDatos['nombre']);
        }

        $permitidos = array_intersect_key(
            $nuevosDatos,
            array_flip(['name', 'email', 'telefono'])
        );

        if ($permitidos === []) {
            return false;
        }

        return $this->fill($permitidos)->save();
    }
    /**
     * cambiarContrasena(nuevaContrasena)
     */
    public function cambiarContrasena(string $nuevaContrasena): bool
    {
        $this->setContrasena($nuevaContrasena);
        $this->setRememberToken(Str::random(60));

        return $this->save();
    }

    /**
     * Comprueba si una contraseña en texto plano corresponde a la del usuario.
     */
    public function verificarContrasena(string $contrasena): bool
    {
        return Hash::check($contrasena, $this->getAuthPassword());
    }

    /**
     * Devuelve el carrito activo del usuario, creandolo si no existe.
     */
    public function carritoActivo(): Cart
    {
        return Cart::activoPara($this);
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
    return $this->getAttribute('name'); // Lee la columna 'name'
}

public function setNombre(string $nombre): static
{
    $this->setAttribute('name', $nombre); // Escribe en la columna 'name'

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

    /**
     * No existe getContrasena(): la contraseña solo se escribe, nunca se lee
     * en texto plano. Para compararla se usa verificarContrasena().
     */
    public function setContrasena(string $contrasena): static
    {
        $this->setAttribute('password', $contrasena);

        return $this;
    }

    public function getRol(): ?string
    {
        return $this->getAttribute('rol');
    }

    public function setRol(string $rol): static
    {
        if (! in_array($rol, [self::ROL_USUARIO, self::ROL_ADMINISTRADOR], true)) {
            throw new \InvalidArgumentException("Rol no válido: {$rol}");
        }

        $this->setAttribute('rol', $rol);

        return $this;
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, Order> */
    public function getPedidos()
    {
        return $this->orders()->get();
    }

    /** @return \Illuminate\Database\Eloquent\Collection<int, PaymentMethod> */
    public function getFormasPago()
    {
        return $this->paymentMethods()->get();
    }

    public function getComparador(): ProductComparator
    {
        return ProductComparator::paraUsuario($this);
    }
}
