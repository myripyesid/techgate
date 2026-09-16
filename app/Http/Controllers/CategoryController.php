<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::all();
        return view('categories.index', compact('categories'));
    }

    // Método para mostrar el formulario de creación
    public function create()
    {
        return view('categories.create');
    }

    // Método para guardar la nueva categoría en la BD
    public function store(Request $request)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
        ]);

        $category = (new Category())
            ->setNombre($request->string('nombre')->toString())
            ->setDescripcion($request->input('descripcion'));

        $category->save();

        return redirect()->route('categories.index')->with('success', 'Categoría creada con éxito.');
    }
}