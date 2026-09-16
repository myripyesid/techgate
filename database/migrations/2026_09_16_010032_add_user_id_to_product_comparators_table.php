<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('product_comparators', 'user_id')) {
            Schema::table('product_comparators', function (Blueprint $table) {
                $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('product_comparators', 'user_id')) {
            Schema::table('product_comparators', function (Blueprint $table) {
                $table->dropForeign(['user_id']);
                $table->dropColumn('user_id');
            });
        }
    }
};