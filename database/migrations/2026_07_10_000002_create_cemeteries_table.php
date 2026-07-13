<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cemeteries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->string('name', 150);
            $table->string('address', 255);
            $table->string('municipality', 100);
            $table->string('state', 50);
            $table->string('postal_code', 5);
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('legal_representative', 150);
            $table->string('legal_representative_rfc', 13);
            $table->time('opening_time')->default('08:00');
            $table->time('closing_time')->default('18:00');
            $table->json('schedule')->nullable(); // Horarios especiales
            $table->timestamps();
            $table->index(['tenant_id', 'municipality']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cemeteries');
    }
};