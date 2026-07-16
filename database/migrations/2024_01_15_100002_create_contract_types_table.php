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
        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            
            $table->string('name'); // Ej: "Perpetuo", "Temporal 5 años", "Preventa"
            $table->enum('modality', ['perpetual', 'temporary'])->index();
            $table->integer('duration_years')->nullable(); // Null para perpetuos
            $table->boolean('is_renewable')->default(false);
            
            // Configuración financiera por defecto
            $table->decimal('base_price', 12, 2)->default(0);
            $table->decimal('maintenance_fee_annual', 12, 2)->default(0);
            $table->integer('grace_period_days')->default(30); // Días de gracia antes de mora
            
            $table->text('terms_and_conditions')->nullable();
            $table->boolean('is_active')->default(true);
            
            $table->timestamps();
            
            $table->index(['tenant_id', 'modality']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contract_types');
    }
};
