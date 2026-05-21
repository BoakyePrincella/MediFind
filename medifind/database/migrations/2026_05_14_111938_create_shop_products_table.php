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
    Schema::create('shop_products', function (Blueprint $table) {
        $table->id();
        $table->foreignId('shop_id')
               ->constrained()
               ->cascadeOnDelete();
        $table->foreignId('product_id')
               ->constrained()
               ->cascadeOnDelete();
        $table->decimal('price', 10, 2);
        $table->boolean('in_stock')->default(true);
        $table->string('notes')->nullable(); // e.g. "prescription required"
        $table->timestamps();

        // A shop can only list the same product once
        $table->unique(['shop_id', 'product_id']);
    });
}
    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shop_products');
    }
};
