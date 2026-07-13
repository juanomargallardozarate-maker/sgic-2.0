<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tipos de contrato
        Schema::create('contract_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // perpetual, temporary_10, temporary_25, temporary_50
            $table->string('name', 100);
            $table->integer('years')->nullable(); // NULL para perpetuidad
            $table->boolean('is_temporary')->default(false); // RN-02
            $table->boolean('requires_renewal')->default(false);
            $table->text('description')->nullable();
            $table->timestamps();
        });

	// Contratos (RN-02, RN-03, RN-05)
	Schema::create('contracts', function (Blueprint $table) {
  	 $table->id();
  	 $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
   	 $table->foreignId('customer_id')->constrained()->onDelete('restrict');
   	 $table->foreignId('crypt_id')->constrained()->onDelete('restrict');
   	 $table->foreignId('contract_type_id')->constrained()->onDelete('restrict');
   	 $table->string('contract_number', 50)->unique(); // Folio único por tenant
   	 $table->date('start_date');
    	 $table->date('end_date')->nullable(); // NULL para perpetuidad
    	 $table->decimal('price', 12, 2);
   	 $table->decimal('annual_maintenance_fee', 10, 2); // Cuota anual de mantenimiento
   	 $table->enum('payment_type', ['cash', 'installments', 'mixed']);
   	 $table->integer('installments_count')->nullable();
   	 // RN-05: Sucesión
  	 $table->boolean('is_succession_pending')->default(false);
  	 $table->string('heir_document_url', 500)->nullable();
   	 $table->date('succession_completed_at')->nullable();
   	 // Firma digital
  	 $table->timestamp('signed_at')->nullable();
  	 $table->string('signature_hash', 64)->nullable(); // SHA-256 de la firma
   	 $table->string('signature_ip', 45)->nullable();
   	 $table->string('signed_document_url', 500)->nullable();
  	 // Estado del contrato
  	 $table->enum('status', ['draft', 'active', 'expired', 'grace_period', 'decaying', 'terminated', 'renewed'])->default('draft');
  	 // RN-03: Decadencia
  	 $table->date('grace_period_ends_at')->nullable();
  	 $table->date('decay_process_started_at')->nullable();
  	 $table->text('notes')->nullable();
 	 // ✅ CORRECCIÓN: Agregar ->nullable() antes de ->constrained()
  	 $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
  	 $table->timestamps();
   	 $table->softDeletes();
   	 $table->index(['tenant_id', 'status']);
   	 $table->index(['tenant_id', 'customer_id']);
   	 $table->index(['tenant_id', 'crypt_id']);
	 $table->index(['tenant_id', 'end_date']); // Para RN-03
 	 $table->index(['tenant_id', 'contract_number']);
	});
        // Beneficiarios autorizados
        Schema::create('beneficiaries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->string('relationship', 50); // Esposo/a, hijo/a, etc.
            $table->boolean('is_primary')->default(false);
            $table->timestamps();
            $table->unique(['contract_id', 'customer_id']);
        });

        // Herederos designados (RN-05)
        Schema::create('heirs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->boolean('is_designated')->default(false);
            $table->decimal('inheritance_percent', 5, 2)->default(100.00);
            $table->timestamps();
            $table->unique(['contract_id', 'customer_id']);
        });

        // Reservas
        Schema::create('reservations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('crypt_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->timestamp('reserved_at');
            $table->timestamp('expires_at');
            $table->enum('status', ['active', 'converted', 'expired', 'cancelled'])->default('active');
            $table->foreignId('contract_id')->nullable()->constrained()->nullOnDelete(); // Si se convierte
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index('expires_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reservations');
        Schema::dropIfExists('heirs');
        Schema::dropIfExists('beneficiaries');
        Schema::dropIfExists('contracts');
        Schema::dropIfExists('contract_types');
    }
};