<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->string('marca');
            $table->string('modelo');
            $table->string('nombre');
            $table->date('lanzamiento')->nullable();
            $table->decimal('precio', 10, 2);
            $table->decimal('precio_lanzamiento_usd', 10, 2)->nullable();
            $table->integer('stock')->default(0);
            $table->boolean('es_vendible')->default(true);
            $table->string('confianza')->default('alta');
            $table->json('especificaciones')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};