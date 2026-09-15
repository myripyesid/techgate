<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;

Route::get('/', function () {
    return redirect()->route('products.index');
});

// Rutas de productos y categorías (Vistas Blade)
Route::resource('categories', CategoryController::class);
Route::resource('products', ProductController::class);
