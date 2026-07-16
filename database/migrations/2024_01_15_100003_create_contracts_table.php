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
        Schema::create('contracts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            
            // Relación con cripta y cliente
            $table->foreignId('crypt_id')->constrained()->cascadeOnDelete();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->foreignId('contract_type_id')->constrained()->cascadeOnDelete();
            
            // Estado del contrato (RN-02)
            $table->enum('status', [
                'draft',           // Borrador
                'pending_signature', // Pendiente de firma
                'active',          // Activo (firmado)
                'expired',         // Expirado (temporales)
                'cancelled',       // Cancelado
                'in_succession'    // En proceso de sucesión (RN-05)
            ])->default('draft')->index();
            
            // Fechas clave
            $table->date('start_date');
            $table->date('end_date')->nullable(); // Null para perpetuos
            $table->date('signed_date')->nullable();
            $table->date('expiry_warning_sent_at')->nullable(); // Última notificación de expiración
            
            // Datos financieros del contrato
            $table->decimal('total_amount', 12, 2);
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('balance', 12, 2);
            $table->integer('payment_installments')->default(1);
            $table->integer('installments_paid')->default(0);
            
            // Firma digital (evidencia RN-06)
            $table->string('digital_signature_hash')->nullable();
            $table->string('ip_address_at_signing')->nullable();
            $table->timestamp('signed_at')->nullable();
            
            // Metadata
            $table->string('contract_number')->unique(); // Generado automáticamente
            $table->text('special_conditions')->nullable();
            $table->json('metadata')->nullable();
            
            $table->timestamps();
            $table->softDeletes();

            // Índices para búsquedas rápidas
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'crypt_id']);
            $table->index(['tenant_id', 'customer_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('contracts');
    }
};
