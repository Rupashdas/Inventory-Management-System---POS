<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Stock is the half the application was named for and did not have: a sale
     * recorded an invoice and left the catalogue untouched, so "how many are
     * left" had no answer anywhere in the schema.
     *
     * Held as an integer rather than the string the other numeric columns use,
     * because this one is decremented under a lock and compared against zero.
     */
    public function up(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->unsignedInteger('stock')->default(0)->after('unit');
            // Per-product, because "low" is a different number for a 12-taka
            // envelope than for a 1450-taka desk lamp.
            $table->unsignedInteger('low_stock_threshold')->default(5)->after('stock');
        });
    }

    public function down(): void {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['stock', 'low_stock_threshold']);
        });
    }
};
