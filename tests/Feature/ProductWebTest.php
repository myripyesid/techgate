<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductWebTest extends TestCase
{
    use RefreshDatabase;

    public function test_usuario_puede_crear_producto_desde_formulario(): void
    {
        $category = Category::create(['nombre' => 'Pantallas']);

        $response = $this->post(route('products.store'), [
            'category_id' => $category->id,
            'nombre' => 'LG 22MR410',
            'precio' => 120.00,
            'stock' => 15,
        ]);

        $response->assertRedirect(route('products.index'));
        $this->assertDatabaseHas('products', ['nombre' => 'LG 22MR410']);
    }
}