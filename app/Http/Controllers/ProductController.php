<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

/**
 * Catalogo de productos.
 *
 * index() lo ve cualquier usuario autenticado.
 * create/store/edit/update/destroy son EXCLUSIVOS del administrador
 * (se protegen con el middleware "admin" en routes/web.php).
 */
class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->filled('buscar'), fn ($q) => $q->where('nombre', 'like', '%'.$request->string('buscar').'%'))
            ->when($request->filled('category_id'), fn ($q) => $q->where('category_id', $request->integer('category_id')))
            ->latest()
            ->get();

        return view('products.index', [
            'products' => $products,
            'categories' => Category::orderBy('nombre')->get(),
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
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        // Se construye con los setters de la clase Producto.
        $product = (new Product())
            ->setNombre($validated['nombre'])
            ->setCategoryId((int) $validated['category_id'])
            ->setPrecio((float) $validated['precio'])
            ->setStock((int) $validated['stock']);

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
            'precio' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
        ]);

        $product->setNombre($validated['nombre'])
                ->setCategoryId((int) $validated['category_id'])
                ->setPrecio((float) $validated['precio'])
                ->setStock((int) $validated['stock'])
                ->save();

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
