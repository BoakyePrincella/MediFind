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
    Schema::create('shops', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')
               ->constrained()
               ->cascadeOnDelete();
        $table->string('name');
        $table->string('slug')->unique();
        $table->text('description')->nullable();
        $table->string('phone')->nullable();
        $table->string('address');
        $table->string('city');           // 'Accra' or 'Kumasi'
        $table->decimal('latitude', 10, 7)->nullable();
        $table->decimal('longitude', 10, 7)->nullable();
        $table->string('logo')->nullable();
        $table->boolean('is_verified')->default(false);
        $table->boolean('is_active')->default(true);
        $table->boolean('offers_delivery')->default(false);
        $table->decimal('delivery_radius_km', 5, 2)->nullable();
        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('shops');
    }
};
