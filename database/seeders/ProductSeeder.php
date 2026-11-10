<?php
// Catalogo fusionado: componentes de PC + dispositivos electronicos.
//
// Reemplaza el catalogo de ejemplo anterior. Las categorias salen de
// config/catalogo.php y los productos (con sus especificaciones tecnicas)
// de database/seeders/data/catalogo.json, generado a partir de las bases
// pc_componentes y dispositivos_electronicos.
//
// Es idempotente: se puede ejecutar varias veces sin duplicar registros.
//
// Nota: el stock (10 unidades) y los precios de los productos que no tenian
// precio de lanzamiento confirmado (chipsets, kits de RAM, almacenamiento,
// fuentes, neveras, televisores, etc.) son valores ESTIMADOS de demostracion
// en USD. Todos los productos del catalogo son vendibles. Para dejar un
// producto solo como referencia, el administrador desmarca "Disponible para
// la venta" al editarlo.

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use RuntimeException;

class ProductSeeder extends Seeder
{
    // Stock minimo de demostracion para productos vendibles.
    private const STOCK_DEMO = 10;

    public function run(): void
    {
        $ruta = database_path('seeders/data/catalogo.json');

        if (! is_file($ruta)) {
            throw new RuntimeException("No se encontro el archivo del catalogo: {$ruta}");
        }

        $productos = json_decode(file_get_contents($ruta), true, 512, JSON_THROW_ON_ERROR);

        $categorias = [];
        foreach (config('catalogo.categorias') as $slug => $datos) {
            $categorias[$slug] = Category::updateOrCreate(
                ['slug' => $slug],
                [
                    'nombre' => $datos['nombre'],
                    'grupo' => $datos['grupo'],
                    'descripcion' => $datos['descripcion'],
                ]
            );
        }

        foreach ($productos as $item) {
            $categoria = $categorias[$item['categoria']] ?? null;

            if (! $categoria) {
                throw new RuntimeException("Categoria desconocida en el catalogo: {$item['categoria']}");
            }

            // Stock de demostracion: para que el carrito y el armador de PC
            // puedan operar, todo producto vendible arranca con al menos 10
            // unidades. Si el JSON trae un stock mayor, se respeta.
            $stock = (int) $item['stock'];
            if ($item['es_vendible'] && $stock < self::STOCK_DEMO) {
                $stock = self::STOCK_DEMO;
            }

            Product::updateOrCreate(
                [
                    'category_id' => $categoria->id,
                    'marca' => $item['marca'],
                    'modelo' => $item['modelo'],
                ],
                [
                    'nombre' => $item['nombre'],
                    'lanzamiento' => $item['lanzamiento'],
                    'precio' => $item['precio'],
                    'precio_lanzamiento_usd' => $item['precio_lanzamiento_usd'],
                    'stock' => $stock,
                    'es_vendible' => $item['es_vendible'],
                    'confianza' => $item['confianza'],
                    'especificaciones' => $item['especificaciones'],
                ]
            );
        }
    }
}
