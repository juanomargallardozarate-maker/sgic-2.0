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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            
            // Datos Fiscales
            $table->enum('type', ['individual', 'company'])->default('individual'); // Persona Física o Moral
            $table->string('rfc', 13)->index(); // 12-13 caracteres
            $table->string('curp', 18)->nullable(); // Solo para físicos
            $table->string('legal_name'); // Razón Social o Nombre Completo
            $table->string('commercial_name')->nullable(); // Nombre Comercial
            
            // Contacto
            $table->string('email')->index();
            $table->string('phone');
            $table->string('mobile')->nullable();
            
            // Dirección Fiscal Completa (México)
            $table->string('street');
            $table->string('exterior_number');
            $table->string('interior_number')->nullable();
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code', 5);
            $table->string('country')->default('México');
            
            // Metadata
            $table->text('notes')->nullable();
            $table->json('metadata')->nullable(); // Datos extra (ej. origen del lead)
            
            $table->timestamps();
            $table->softDeletes();

            // Índices compuestos para búsquedas rápidas por tenant
            $table->unique(['tenant_id', 'rfc']);
            $table->index(['tenant_id', 'email']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
