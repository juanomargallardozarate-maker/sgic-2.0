<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Secciones / Manzanas
        Schema::create('sections', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('code', 20); // Ej: "A", "SAN_PEDRO"
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'is_active']);
        });

        // Bloques
        Schema::create('blocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('section_id')->constrained()->onDelete('cascade');
            $table->string('code', 20); // Ej: "1", "B1"
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->integer('order')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'section_id', 'code']);
            $table->index(['tenant_id', 'section_id']);
        });

        // Niveles
        Schema::create('levels', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('block_id')->constrained()->onDelete('cascade');
            $table->string('code', 20); // Ej: "1", "N1"
            $table->string('name', 100);
            $table->integer('height_order')->default(0); // Para visualización (1 = abajo)
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->unique(['tenant_id', 'block_id', 'code']);
        });

        // Criptas (entidad core)
        Schema::create('crypts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('level_id')->constrained()->onDelete('cascade');
            $table->foreignId('crypt_type_id')->constrained()->onDelete('restrict');
            $table->foreignId('crypt_status_id')->constrained()->onDelete('restrict');
            $table->string('code', 30); // Código único por tenant (Ej: "A-1-3-05")
            $table->integer('capacity')->default(1); // Máximo de urnas/ataúdes (RN-01)
            $table->integer('current_occupancy')->default(0); // Inhumaciones actuales
            $table->decimal('price', 12, 2)->default(0);
            $table->string('dimensions', 50)->nullable(); // Ej: "2.0x1.0x1.5m"
            $table->enum('door_type', ['marble', 'bronze', 'glass', 'stone', 'other'])->nullable();
            $table->text('notes')->nullable();
            // RN-04: Bloqueo por morosidad
            $table->boolean('is_blocked')->default(false);
            $table->string('blocked_reason', 100)->nullable();
            $table->timestamp('blocked_at')->nullable();
            $table->foreignId('blocked_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['tenant_id', 'code']);
            $table->index(['tenant_id', 'crypt_status_id']);
            $table->index(['tenant_id', 'is_blocked']);
            $table->index('level_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('crypts');
        Schema::dropIfExists('levels');
        Schema::dropIfExists('blocks');
        Schema::dropIfExists('sections');
    }
};