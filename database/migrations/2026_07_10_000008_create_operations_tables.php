<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // =====================================================
        // 1. TIPOS DE ÓRDENES DE TRABAJO (sin dependencias)
        // =====================================================
        Schema::create('work_order_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // inhumation, exhumation, transfer, cleaning, maintenance
            $table->string('name', 100);
            $table->boolean('requires_sanitary_validation')->default(false); // RN-06
            $table->boolean('requires_death_certificate')->default(false);
            $table->boolean('requires_family_signature')->default(true);
            $table->integer('min_photos')->default(1);
            $table->integer('max_photos')->default(10);
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // =====================================================
        // 2. CUADRILLAS (solo depende de tenant)
        // =====================================================
        Schema::create('crews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name', 100);
            $table->string('vehicle_plate', 20)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // =====================================================
        // 3. MIEMBROS DE CUADRILLA (depende de crews y users)
        // =====================================================
        Schema::create('crew_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('crew_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('role', ['leader', 'member'])->default('member');
            $table->timestamps();
            $table->unique(['crew_id', 'user_id']);
        });

        // =====================================================
        // 4. ÓRDENES DE TRABAJO (RN-06) - Múltiples dependencias
        // =====================================================
        Schema::create('work_orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('work_order_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('crypt_id')->constrained()->onDelete('restrict');
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('contract_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('crew_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('assigned_to_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('order_number', 50); // Folio único por tenant
            $table->timestamp('scheduled_at');
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            // RN-06: Validación sanitaria
            $table->boolean('sanitary_validated')->default(false);
            $table->string('death_certificate_url', 500)->nullable();
            $table->enum('body_type', ['corpse', 'urn'])->nullable();
            $table->string('coffin_type', 50)->nullable(); // Tipo de ataúd/urna
            $table->string('coffin_seal_number', 50)->nullable(); // Número de sello
            // RN-04: Excepción judicial
            $table->boolean('judicial_exception')->default(false);
            $table->string('judicial_order_url', 500)->nullable();
            $table->text('judicial_notes')->nullable();
            // Evidencia
            $table->string('signature_url', 500)->nullable();
            $table->string('signature_hash', 64)->nullable();
            $table->string('signature_ip', 45)->nullable();
            $table->timestamp('signature_at')->nullable();
            $table->text('observations')->nullable();
            // Estado
            $table->enum('status', ['pending', 'assigned', 'in_progress', 'completed', 'cancelled', 'failed'])->default('pending');
            $table->enum('sync_status', ['pending', 'synced', 'conflict', 'error'])->default('pending'); // Para PWA offline
            $table->string('offline_id', 36)->nullable(); // UUID generado offline
            $table->text('conflict_notes')->nullable();
            // ✅ TODOS los FK a users con ->nullable()
            $table->foreignId('created_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('completed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['tenant_id', 'status']);
            $table->index(['tenant_id', 'scheduled_at']);
            $table->index(['tenant_id', 'crypt_id']);
            $table->index(['tenant_id', 'crew_id']);
            $table->index('offline_id');
            $table->index('sync_status');
        });

        // =====================================================
        // 5. EVIDENCIAS DE OT (fotos, firmas) - Depende de work_orders
        // =====================================================
        Schema::create('work_order_evidences', function (Blueprint $table) {
            $table->id();
            $table->foreignId('work_order_id')->constrained()->onDelete('cascade');
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['photo', 'signature', 'document']);
            $table->string('file_url', 500);
            $table->string('file_hash', 64); // SHA-256 del archivo
            $table->integer('file_size')->nullable(); // bytes
            $table->string('mime_type', 50);
            $table->json('metadata')->nullable(); // EXIF, GPS, etc.
            $table->string('gps_latitude', 20)->nullable();
            $table->string('gps_longitude', 20)->nullable();
            $table->timestamp('taken_at');
            $table->foreignId('uploaded_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['work_order_id', 'type']);
            $table->index('taken_at');
        });
    }

    public function down(): void
    {
        // Orden inverso exacto al de creación
        Schema::dropIfExists('work_order_evidences');
        Schema::dropIfExists('work_orders');
        Schema::dropIfExists('crew_members');
        Schema::dropIfExists('crews');
        Schema::dropIfExists('work_order_types');
    }
};