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
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            
            // Datos del beneficiario
            $table->string('full_name');
            $table->string('rfc', 13)->nullable();
            $table->string('curp', 18)->nullable();
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            
            // Parentesco con el titular
            $table->string('relationship'); // Esposo(a), Hijo(a), Padre, Madre, etc.
            $table->integer('priority')->default(1); // Orden de prioridad en sucesión
            
            // Porcentaje de herencia (para múltiples beneficiarios)
            $table->decimal('inheritance_percentage', 5, 2)->default(100.00);
            
            // Validación de identidad
            $table->string('id_document_path')->nullable(); // INE/Pasaporte escaneado
            $table->boolean('is_verified')->default(false);
            
            $table->timestamps();
            
            $table->index(['contract_id', 'priority']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('beneficiaries');
    }
};
