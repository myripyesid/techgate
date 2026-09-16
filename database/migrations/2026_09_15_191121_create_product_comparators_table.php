<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
public function up(): void
{
    Schema::create('product_comparators', function (Blueprint $table) {
        $table->id();
        // Cada usuario tiene su propio comparador (relacion 1 a 1).
        $table->foreignId('user_id')->nullable()->unique()->constrained('users')->onDelete('cascade');
        $table->string('nombre');
        $table->string('email');
        $table->string('telefono')->nullable();
        $table->string('contraseña');
        $table->timestamps();
    });

    // Tabla pivote para la relación N a N entre Comparador y Productos
    Schema::create('comparator_product', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_comparator_id')->constrained('product_comparators')->onDelete('cascade');
        $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('comparator_product');
        Schema::dropIfExists('product_comparators');
    }
};
