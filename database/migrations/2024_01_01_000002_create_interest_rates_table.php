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
        Schema::create('interest_rates', function (Blueprint $table) {
            $table->id();
            $table->integer('months'); // Cantidad de meses (ej: 6, 12, 18, 24)
            $table->decimal('percentage', 5, 2); // Porcentaje de interés (ej: 15.00)
            $table->string('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            
            $table->unique(['months']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('interest_rates');
    }
};
