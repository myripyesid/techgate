<?php
// Servicio web (API JSON) de TechGate. Expuesto para que otros equipos
// consuman la informacion del catalogo. Entregable #2.

use App\Http\Controllers\Api\ProductApiController;
use Illuminate\Support\Facades\Route;

// GET /api/productos -> productos en stock en venta, con enlace a cada ficha.
Route::get('/productos', [ProductApiController::class, 'index'])->name('api.products');
