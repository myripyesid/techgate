<?php
// Autor: Marbin

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

// Catalogo de productos.
// index() lo ve cualquier usuario autenticado.
// create/store/edit/update/destroy son EXCLUSIVOS del administrador
// (se protegen con el middleware "admin" en routes/web.php).
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::orderBy('id')->get();
        $filtrando = $request->filled('buscar') || $request->filled('category_id');

        // Vista "Todo": unos pocos productos destacados por categoria, cada
        // seccion con un boton "Ver más" hacia esa categoria.
        if (! $filtrando) {
            $secciones = $categories->map(function (Category $categoria) {
                $productos = Product::with('category')
                    ->where('category_id', $categoria->getId())
                    ->withSum('orderItems as vendidos', 'cantidad')
                    ->orderByDesc('vendidos')
                    ->orderByRaw('lanzamiento IS NULL')
                    ->orderByDesc('lanzamiento')
                    ->orderBy('nombre')
                    ->limit(3)
                    ->get();

                return ['categoria' => $categoria, 'productos' => $productos];
            })->filter(fn ($seccion) => $seccion['productos']->isNotEmpty())->values();

            return view('products.index', [
                'secciones' => $secciones,
                'products' => collect(),
                'categories' => $categories,
                'filtrando' => false,
            ]);
        }

        $products = Product::with('category')
            ->when($request->filled('buscar'), function ($q) use ($request) {
                $termino = '%'.$request->string('buscar').'%';

                $q->where(fn ($q2) => $q2->where('nombre', 'like', $termino)
                    ->orWhere('marca', 'like', $termino)
                    ->orWhere('modelo', 'like', $termino));
            })
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->orderBy('category_id')
            ->orderBy('nombre')
            ->get();

        return view('products.index', [
            'secciones' => collect(),
            'products' => $products,
            'categories' => $categories,
            'filtrando' => true,
        ]);
    }

    // Ficha tecnica: todas las especificaciones del producto.
    public function show(Product $product)
    {
        $product->load('category');
        $definiciones = config('catalogo.categorias.'.$product->getCategorySlug().'.specs', []);

        return view('products.show', [
            'product' => $product,
            'definiciones' => $definiciones,
        ]);
    }

    public function create()
    {
        $categories = Category::orderBy('nombre')->get();

        return view('products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'nombre' => 'required|string|max:255',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        // Se construye con los setters de la clase Producto.
        $product = (new Product())
            ->setNombre($validated['nombre'])
            ->setCategoryId((int) $validated['category_id'])
            ->setPrecio((float) $validated['precio'])
            ->setStock((int) $validated['stock']);

        $product->marca = $validated['marca'] ?? null;
        $product->modelo = $validated['modelo'] ?? null;
        $product->es_vendible = $request->boolean('es_vendible', true);

        $product->save();

        return redirect()->route('products.index')
            ->with('success', 'Producto creado exitosamente.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('nombre')->get();

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $validated = $request->validate([
            'category_id' => 'required|exists:categories,id',
            'nombre' => 'required|string|max:255',
            'marca' => 'nullable|string|max:255',
            'modelo' => 'nullable|string|max:255',
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product->setNombre($validated['nombre'])
                ->setCategoryId((int) $validated['category_id'])
                ->setPrecio((float) $validated['precio'])
                ->setStock((int) $validated['stock']);

        // Solo se modifican los campos de catalogo que vengan en la solicitud.
        if (array_key_exists('marca', $validated)) {
            $product->marca = $validated['marca'];
        }
        if (array_key_exists('modelo', $validated)) {
            $product->modelo = $validated['modelo'];
        }
        $product->es_vendible = $request->boolean('es_vendible', $product->esVendible());
        $product->save();

        return redirect()->route('products.index')
            ->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Producto eliminado.');
    }
}
