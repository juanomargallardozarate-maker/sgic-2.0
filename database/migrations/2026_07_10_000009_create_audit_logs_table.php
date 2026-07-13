<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('action', 50); // created, updated, deleted, restored, signed, etc.
            $table->string('model', 100); // App\Models\Customer
            $table->unsignedBigInteger('model_id');
            $table->string('model_code', 100)->nullable(); // Código/identificador del modelo
            $table->json('old_values')->nullable(); // Valores antes del cambio
            $table->json('new_values')->nullable(); // Valores después del cambio
            $table->json('pivot_changes')->nullable(); // Cambios en relaciones pivot
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->string('url', 500)->nullable();
            $table->text('description')->nullable(); // Descripción legible del cambio
            $table->string('reason', 255)->nullable(); // Motivo del cambio (para operaciones críticas)
            $table->timestamps();
            
            $table->index(['tenant_id', 'model', 'model_id']);
            $table->index(['tenant_id', 'action']);
            $table->index(['tenant_id', 'user_id']);
            $table->index(['tenant_id', 'created_at']);
            $table->index(['model', 'model_id']);
        });

        // RN-07: Triggers para hacer la tabla INMUTABLE
        // Prevenir UPDATE
        DB::unprepared('
            CREATE TRIGGER prevent_audit_update
            BEFORE UPDATE ON audit_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "Audit logs cannot be modified. This action is logged.";
            END
        ');

        // Prevenir DELETE
        DB::unprepared('
            CREATE TRIGGER prevent_audit_delete
            BEFORE DELETE ON audit_logs
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "Audit logs cannot be deleted. This action is logged.";
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_audit_delete');
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_audit_update');
        Schema::dropIfExists('audit_logs');
    }
};