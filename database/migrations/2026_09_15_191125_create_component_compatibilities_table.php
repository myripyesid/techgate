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
    Schema::create('component_compatibilities', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_comparator_id')->unique()->constrained('product_comparators')->onDelete('cascade');
        $table->string('nombre');
        $table->string('email');
        $table->string('telefono')->nullable();
        $table->string('contraseña');
        $table->timestamps();
    });

    // Tabla de reglas/matriz de compatibilidad entre 2 productos
    Schema::create('product_compatibilities', function (Blueprint $table) {
        $table->id();
        $table->foreignId('product_a_id')->constrained('products')->onDelete('cascade');
        $table->foreignId('product_b_id')->constrained('products')->onDelete('cascade');
        $table->boolean('es_compatible')->default(true);
        $table->text('razon_incompatibilidad')->nullable();
        $table->timestamps();
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('component_compatibilities');
    }
};
