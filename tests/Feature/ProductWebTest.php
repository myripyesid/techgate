<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_administrador_puede_crear_producto_desde_formulario(): void
    {
        $admin = User::factory()->administrador()->create();
        $category = Category::create(['nombre' => 'Pantallas']);

        $response = $this->actingAs($admin)->post(route('products.store'), [
            'category_id' => $category->id,
            'nombre' => 'LG 22MR410',
            'precio' => 120.00,
            'stock' => 15,
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', ['nombre' => 'LG 22MR410']);
    }

    public function test_usuario_normal_no_puede_crear_productos(): void
    {
        $usuario = User::factory()->create();
        $category = Category::create(['nombre' => 'Pantallas']);

        $response = $this->actingAs($usuario)->post(route('products.store'), [
            'category_id' => $category->id,
            'nombre' => 'Producto prohibido',
            'precio' => 100.00,
            'stock' => 5,
        ]);

        $response->assertForbidden();
        $this->assertDatabaseMissing('products', ['nombre' => 'Producto prohibido']);
    }

    public function test_invitado_es_redirigido_al_login(): void
    {
        $this->get(route('products.index'))->assertRedirect(route('login'));
    }
}
