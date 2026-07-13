<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->unique(); // basic, professional, enterprise
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('annual_price', 10, 2)->default(0);
            
            // Límites del plan
            $table->integer('max_users')->default(5);
            $table->integer('max_crypts')->default(500);
            $table->integer('max_contracts')->default(1000);
            $table->boolean('has_pwa')->default(false);
            $table->boolean('has_bi_reports')->default(false);
            $table->boolean('has_api_access')->default(false);
            $table->boolean('has_priority_support')->default(false);
            $table->boolean('has_custom_branding')->default(false);
            
            $table->boolean('is_active')->default(true);
            $table->integer('order')->default(0);
            $table->timestamps();
        });

        // Tabla de historial de suscripciones
        Schema::create('subscription_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('subscription_plan_id')->constrained()->onDelete('restrict');
            $table->enum('action', ['created', 'upgraded', 'downgraded', 'renewed', 'cancelled', 'expired']);
            $table->decimal('amount', 10, 2)->nullable();
            $table->date('starts_at');
            $table->date('ends_at');
            $table->text('notes')->nullable();
            $table->foreignId('changed_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            $table->index(['tenant_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subscription_history');
        Schema::dropIfExists('subscription_plans');
    }
};