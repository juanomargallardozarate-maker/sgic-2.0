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
        // Configuraciones del tenant
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('key', 100); // Ej: "maintenance_fee", "grace_period_years"
            $table->string('type', 30)->default('string'); // string, number, boolean, json
            $table->text('value'); // Valor almacenado como texto
            $table->text('description')->nullable();
            $table->string('group', 50)->nullable(); // general, financial, operations, notifications
            $table->boolean('is_public')->default(false); // Visible para clientes
            $table->timestamps();
            $table->unique(['tenant_id', 'key']);
            $table->index(['tenant_id', 'group']);
        });

        // Plantillas de notificaciones
        Schema::create('notification_templates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('code', 50)->unique(); // contract_expiry, payment_due, debt_blocked, etc.
            $table->string('name', 150);
            $table->enum('channel', ['email', 'sms', 'whatsapp', 'push']);
            $table->string('subject', 200)->nullable(); // Para email
            $table->text('body'); // Cuerpo del mensaje (con placeholders)
            $table->json('variables')->nullable(); // Variables disponibles {contract_number}, {customer_name}, etc.
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'code', 'channel']);
        });

        // Notificaciones enviadas
        Schema::create('notifications_sent', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained()->nullOnDelete();
            $table->string('template_code', 50)->nullable();
            $table->enum('channel', ['email', 'sms', 'whatsapp', 'push']);
            $table->string('recipient', 200); // Email, teléfono, etc.
            $table->string('subject', 200)->nullable();
            $table->text('body');
            $table->json('metadata')->nullable(); // Datos adicionales
            $table->enum('status', ['pending', 'sent', 'delivered', 'failed'])->default('pending');
            $table->timestamp('sent_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'sent_at']);
            $table->index(['customer_id', 'status']);
        });

        // Recordatorios automáticos
        Schema::create('reminders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('contract_id')->constrained()->onDelete('cascade');
            $table->string('type', 50); // payment_due, contract_expiry, maintenance_due
            $table->timestamp('scheduled_at');
            $table->timestamp('sent_at')->nullable();
            $table->enum('status', ['pending', 'sent', 'cancelled'])->default('pending');
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'scheduled_at']);
        });

        // Documentos del sistema
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 50); // contract, receipt, certificate, legal, other
            $table->string('name', 200);
            $table->string('file_url', 500);
            $table->string('file_type', 50)->nullable(); // pdf, jpg, png, docx
            $table->integer('file_size')->nullable(); // en bytes
            $table->text('description')->nullable();
            $table->date('issued_at')->nullable();
            $table->date('expires_at')->nullable();
            $table->boolean('is_public')->default(false); // Visible para el cliente
            // ✅ CORRECCIÓN: Agregar ->nullable() antes de ->constrained()
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'type']);
            $table->index(['tenant_id', 'customer_id']);
            $table->index(['tenant_id', 'contract_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
        Schema::dropIfExists('reminders');
        Schema::dropIfExists('notifications_sent');
        Schema::dropIfExists('notification_templates');
        Schema::dropIfExists('settings');
    }
};