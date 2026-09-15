<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ProductTest extends TestCase
{
    use RefreshDatabase;

    public function test_puede_reducir_stock_correctamente(): void
    {
        $category = Category::create(['nombre' => 'Procesadores']);
        $product = Product::create([
            'category_id' => $category->id,
            'nombre' => 'Ryzen 5 8600G',
            'precio' => 200.00,
            'stock' => 10
        ]);

        $resultado = $product->reducirStock(3);

        $this->assertTrue($resultado);
        $this->assertEquals(7, $product->stock);
    }

    public function test_no_permite_reducir_stock_insuficiente(): void
    {
        $category = Category::create(['nombre' => 'Procesadores']);
        $product = Product::create([
            'category_id' => $category->id,
            'nombre' => 'Ryzen 5 8600G',
            'precio' => 200.00,
            'stock' => 2
        ]);

        $resultado = $product->reducirStock(5);

        $this->assertFalse($resultado);
        $this->assertEquals(2, $product->stock);
    }
}