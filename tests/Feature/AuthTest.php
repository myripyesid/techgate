<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_iniciar_sesion_con_credenciales_correctas(): void
    {
        $usuario = User::create([
            'nombre' => 'Ana',
            'email' => 'ana@techgate.com',
            'contraseña' => 'secreto123',
            'rol' => User::ROL_USUARIO,
        ]);

        $response = $this->post(route('login'), [
            'email' => 'ana@techgate.com',
            'contraseña' => 'secreto123',
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertAuthenticatedAs($usuario);
    }

    public function test_no_inicia_sesion_con_contrasena_incorrecta(): void
    {
        User::create([
            'nombre' => 'Ana',
            'email' => 'ana@techgate.com',
            'contraseña' => 'secreto123',
            'rol' => User::ROL_USUARIO,
        ]);

        $this->post(route('login'), [
            'email' => 'ana@techgate.com',
            'contraseña' => 'incorrecta',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_registro_publico_crea_usuarios_no_administradores(): void
    {
        $this->post(route('register'), [
            'nombre' => 'Nuevo',
            'email' => 'nuevo@techgate.com',
            'telefono' => '3001234567',
            'contraseña' => 'clave12345',
            'contraseña_confirmation' => 'clave12345',
        ])->assertRedirect(route('products.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'nuevo@techgate.com',
            'rol' => User::ROL_USUARIO,
        ]);
    }

    public function test_usuario_puede_cerrar_sesion(): void
    {
        $usuario = User::factory()->create();

        $this->actingAs($usuario)
            ->post(route('logout'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
