<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ComponentCompatibilityController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentMethodController;
use App\Http\Controllers\ProductComparatorController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('products.index');
});

/*
|--------------------------------------------------------------------------
| Autenticacion (solo invitados)
|--------------------------------------------------------------------------
*/
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
    Route::get('/registro', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/registro', [AuthController::class, 'register']);
});

Route::post('/logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/*
|--------------------------------------------------------------------------
| Zona autenticada (usuario y administrador)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {

    // Catalogo (solo lectura para el usuario)
    Route::get('/productos', [ProductController::class, 'index'])->name('products.index');
    Route::get('/categorias', [CategoryController::class, 'index'])->name('categories.index');

    // Perfil
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::put('/perfil/contrasena', [ProfileController::class, 'updatePassword'])->name('profile.password');

    /*
    |----------------------------------------------------------------------
    | Funciones exclusivas del USUARIO
    |----------------------------------------------------------------------
    | Carrito de compras, pedidos, formas de pago, comparador de
    | componentes y verificacion de compatibilidad.
    */

    // Carrito de compras
    Route::get('/carrito', [CartController::class, 'index'])->name('cart.index');
    Route::post('/carrito/agregar/{product}', [CartController::class, 'add'])->name('cart.add');
    Route::put('/carrito/actualizar/{product}', [CartController::class, 'update'])->name('cart.update');
    Route::delete('/carrito/remover/{product}', [CartController::class, 'remove'])->name('cart.remove');
    Route::delete('/carrito/vaciar', [CartController::class, 'clear'])->name('cart.clear');

    // Formas de pago
    Route::get('/formas-de-pago', [PaymentMethodController::class, 'index'])->name('payment-methods.index');
    Route::get('/formas-de-pago/nueva', [PaymentMethodController::class, 'create'])->name('payment-methods.create');
    Route::post('/formas-de-pago', [PaymentMethodController::class, 'store'])->name('payment-methods.store');
    Route::delete('/formas-de-pago/{payment_method}', [PaymentMethodController::class, 'destroy'])->name('payment-methods.destroy');

    // Pedidos
    Route::get('/pedidos', [OrderController::class, 'index'])->name('orders.index');
    Route::post('/pedidos', [OrderController::class, 'store'])->name('orders.store');
    Route::get('/pedidos/{order}', [OrderController::class, 'show'])->name('orders.show');
    Route::post('/pedidos/{order}/cancelar', [OrderController::class, 'cancel'])->name('orders.cancel');

    // Comparador de productos
    Route::get('/comparador', [ProductComparatorController::class, 'index'])->name('comparator.index');
    Route::post('/comparador/agregar/{product}', [ProductComparatorController::class, 'addToComparator'])->name('comparator.add');
    Route::post('/comparador/remover/{product}', [ProductComparatorController::class, 'removeFromComparator'])->name('comparator.remove');
    Route::delete('/comparador/vaciar', [ProductComparatorController::class, 'clear'])->name('comparator.clear');

    // Compatibilidad entre componentes
    Route::get('/compatibilidad', [ComponentCompatibilityController::class, 'index'])->name('compatibility.index');
    Route::post('/compatibilidad/verificar', [ProductComparatorController::class, 'checkCompatibility'])->name('compatibility.check');

    /*
    |----------------------------------------------------------------------
    | Funciones exclusivas del ADMINISTRADOR
    |----------------------------------------------------------------------
    | Alta, edicion y baja de productos y categorias, y gestion de
    | todos los pedidos de la tienda.
    */
    Route::middleware('admin')->group(function () {
        Route::get('/productos/nuevo', [ProductController::class, 'create'])->name('products.create');
        Route::post('/productos', [ProductController::class, 'store'])->name('products.store');
        Route::get('/productos/{product}/editar', [ProductController::class, 'edit'])->name('products.edit');
        Route::put('/productos/{product}', [ProductController::class, 'update'])->name('products.update');
        Route::delete('/productos/{product}', [ProductController::class, 'destroy'])->name('products.destroy');

        Route::get('/categorias/nueva', [CategoryController::class, 'create'])->name('categories.create');
        Route::post('/categorias', [CategoryController::class, 'store'])->name('categories.store');

        Route::get('/admin/pedidos', [OrderController::class, 'adminIndex'])->name('orders.admin');
        Route::put('/admin/pedidos/{order}/estado', [OrderController::class, 'updateStatus'])->name('orders.status');
    });
});
