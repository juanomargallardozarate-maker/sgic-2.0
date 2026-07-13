<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tipos de pago
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // cash, card, transfer, check
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Pagos (RN-04)
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->constrained()->onDelete('restrict');
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('payment_number', 50); // Número de pago/folio
            $table->enum('type', ['initial', 'installment', 'maintenance', 'penalty', 'other']);
            $table->decimal('amount', 12, 2);
            $table->date('due_date');
            $table->date('paid_at')->nullable();
            $table->enum('status', ['pending', 'paid', 'partial', 'cancelled'])->default('pending');
            $table->decimal('amount_paid', 12, 2)->default(0);
            $table->decimal('interest', 10, 2)->default(0); // Interés moratorio (RN-04)
            $table->string('reference', 100)->nullable(); // Referencia bancaria
            $table->string('receipt_url', 500)->nullable(); // Comprobante de pago
            $table->text('notes')->nullable();
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'due_date']); // Para RN-04
            $table->index(['contract_id', 'status']);
        });

        // Facturas CFDI 4.0 (SAT México)
        Schema::create('invoices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->constrained()->onDelete('restrict');
            $table->foreignId('payment_id')->nullable()->constrained()->nullOnDelete();
            $table->string('folio', 50)->unique(); // Folio fiscal interno
            $table->string('cfdi_uuid', 36)->unique()->nullable(); // UUID del SAT
            $table->string('cfdi_version', 10)->default('4.0');
            $table->enum('type', ['ingreso', 'egreso', 'traslado', 'nomina', 'pago']);
            $table->string('payment_form', 2); // c_CondPago
            $table->string('payment_method', 4); // c_MetodoPago
            $table->string('currency', 3)->default('MXN');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('tax', 10, 2)->default(0);
            $table->decimal('total', 12, 2);
            $table->decimal('exchange_rate', 10, 4)->default(1);
            $table->enum('status', ['draft', 'issued', 'cancelled', 'expired'])->default('draft');
            $table->timestamp('issued_at')->nullable();
            $table->timestamp('cancelled_at')->nullable();
            $table->string('cancellation_reason', 2)->nullable(); // c_Motivo
            $table->string('replacement_uuid', 36)->nullable(); // UUID de reemplazo
            $table->text('cfdi_xml')->nullable(); // XML completo del CFDI
            $table->string('xml_url', 500)->nullable();
            $table->string('pdf_url', 500)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'issued_at']);
            $table->index('cfdi_uuid');
        });

        // Deudas acumuladas (RN-04)
        Schema::create('debts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->foreignId('crypt_id')->constrained()->onDelete('cascade');
            $table->decimal('total_debt', 12, 2)->default(0);
            $table->decimal('principal', 12, 2)->default(0);
            $table->decimal('interest', 12, 2)->default(0);
            $table->decimal('penalties', 12, 2)->default(0);
            $table->integer('overdue_months')->default(0);
            $table->date('first_overdue_date')->nullable();
            $table->date('last_payment_date')->nullable();
            // RN-04: Bloqueo automático
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('blocked_at')->nullable();
            $table->foreignId('blocked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->enum('status', ['active', 'negotiating', 'payment_plan', 'resolved', 'written_off'])->default('active');
            $table->text('notes')->nullable();
            $table->decimal('pending_amount', 12, 2);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'contract_id']);
            $table->index(['tenant_id', 'is_blocked']);
            $table->index(['tenant_id', 'overdue_months']);
        });

        // Planes de pago (para deudas)
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('debt_id')->constrained()->onDelete('cascade');
            $table->string('plan_number', 50)->unique();
            $table->decimal('total_amount', 12, 2);
            $table->integer('installments_count');
            $table->decimal('installment_amount', 10, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['active', 'completed', 'cancelled', 'breached'])->default('active');
            $table->text('terms')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_plans');
        Schema::dropIfExists('debts');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('payments');
        Schema::dropIfExists('payment_methods');
    }
};