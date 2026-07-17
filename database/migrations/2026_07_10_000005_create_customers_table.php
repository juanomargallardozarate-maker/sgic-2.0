<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['persona_fisica', 'persona_moral']);
            $table->string('rfc_encrypted', 500);
            $table->string('rfc_hash', 64);
            $table->string('curp_encrypted', 500)->nullable();
            $table->string('name', 200);
            $table->string('email', 150)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('mobile', 20)->nullable();
            $table->text('address')->nullable();
            $table->string('colonia', 150)->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('estado', 100)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('ine_url', 500)->nullable();
            $table->string('proof_of_address_url', 500)->nullable();
            $table->boolean('is_deceased')->default(false);
            $table->date('deceased_at')->nullable();
            $table->string('death_certificate_url', 500)->nullable();
            $table->string('heir_declaration_url', 500)->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->index(['tenant_id', 'rfc_hash']);
            $table->index(['tenant_id', 'is_deceased']);
            $table->index(['tenant_id', 'type']);
            $table->fullText('name');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};