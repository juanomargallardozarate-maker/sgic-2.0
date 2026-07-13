<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('rfc', 13)->unique(); // RFC México
            $table->string('subdomain', 63)->unique(); // {tenant}.sgic.mx
            $table->enum('plan', ['basic', 'professional', 'enterprise'])->default('basic');
            $table->integer('grace_period_years')->default(3); // RN-03
            $table->integer('debt_months_to_block')->default(3); // RN-04
            $table->decimal('moratorium_interest_rate', 5, 4)->default(0.02); // 2% mensual
            $table->integer('reservation_days')->default(15);
            $table->decimal('reservation_deposit_percent', 5, 2)->default(20.00);
            $table->integer('maintenance_grace_days')->default(30);
            $table->json('settings')->nullable(); // Configuraciones adicionales
            $table->boolean('is_active')->default(true);
            $table->timestamp('subscription_ends_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->index('subdomain');
            $table->index('is_active');
        });

        // Trigger para prevenir eliminación física de tenants (solo soft delete)
        DB::unprepared('
            CREATE TRIGGER prevent_tenant_delete
            BEFORE DELETE ON tenants
            FOR EACH ROW
            BEGIN
                SIGNAL SQLSTATE "45000"
                SET MESSAGE_TEXT = "Tenants cannot be physically deleted. Use soft delete.";
            END
        ');
    }

    public function down(): void
    {
        DB::unprepared('DROP TRIGGER IF EXISTS prevent_tenant_delete');
        Schema::dropIfExists('tenants');
    }
};