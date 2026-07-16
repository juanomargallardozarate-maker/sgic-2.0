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
        Schema::create('heirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('contract_id')->constrained()->cascadeOnDelete();
            
            // Datos del heredero (después de sucesión)
            $table->string('full_name');
            $table->string('rfc', 13);
            $table->string('curp', 18)->nullable();
            $table->string('email');
            $table->string('phone');
            
            // Dirección completa
            $table->string('street');
            $table->string('exterior_number');
            $table->string('interior_number')->nullable();
            $table->string('neighborhood');
            $table->string('city');
            $table->string('state');
            $table->string('zip_code', 5);
            
            // Documentación legal de sucesión
            $table->enum('succession_type', ['testamentary', 'intestate', 'judicial']);
            $table->string('legal_document_number'); // Número de escritura o sentencia
            $table->date('legal_document_date');
            $table->string('notary_public')->nullable(); // Nombre de la notaría
            $table->integer('notary_number')->nullable(); // Número de notaría
            $table->string('state_of_issue'); // Entidad federativa donde se tramitó
            
            // Archivos digitales
            $table->string('death_certificate_path'); // Acta de defunción del titular original
            $table->string('succession_document_path'); // Testamento o sentencia judicial
            $table->string('heir_ine_path'); // INE del heredero
            
            // Estado de validación
            $table->enum('validation_status', [
                'pending_review',
                'approved',
                'rejected',
                'requires_additional_docs'
            ])->default('pending_review');
            
            $table->text('rejection_reason')->nullable();
            $table->foreignId('reviewed_by')->nullable()->constrained('users');
            $table->timestamp('reviewed_at')->nullable();
            
            $table->timestamps();
            
            $table->index(['contract_id', 'validation_status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('heirs');
    }
};
