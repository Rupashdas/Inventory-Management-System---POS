<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * A running ledger of every change to a product's stock.
     *
     * The `stock` column on products is the balance; this table is how that
     * balance came to be. Without it a wrong number on the shelf is
     * unexplainable — you can see that stock is 3 and no reason it is 3.
     */
    public function up(): void {
        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('product_id')->constrained('products')->onDelete('cascade');
            // Nullable: a manual adjustment belongs to no invoice, and an
            // invoice that is later deleted should not take its history with it.
            $table->foreignId('invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            // 'sale' and 'sale_reversal' are written by the invoice flow;
            // 'purchase' and 'adjustment' by a human restocking the shelf.
            $table->enum('reason', ['purchase', 'sale', 'sale_reversal', 'adjustment']);
            // Signed: negative takes stock off the shelf, positive puts it back.
            $table->integer('quantity');
            // The balance immediately after this movement, so the ledger can be
            // read without replaying every row before it.
            $table->unsignedInteger('balance_after');
            $table->string('note', 255)->nullable();
            $table->timestamps();

            $table->index(['user_id', 'product_id']);
        });
    }

    public function down(): void {
        Schema::dropIfExists('stock_movements');
    }
};
