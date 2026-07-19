<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Actualiza la tabla interest_rates para usar rangos de meses (min_months, max_months)
     * en lugar de un solo valor, y renombra tenant_id a cemetery_id.
     */
    public function up(): void
    {
        Schema::table('interest_rates', function (Blueprint $table) {
            // Renombrar tenant_id a cemetery_id para consistencia con el dominio
            if (Schema::hasColumn('interest_rates', 'tenant_id')) {
                $table->renameColumn('tenant_id', 'cemetery_id');
            }
            
            // Agregar campos min_months y max_months si no existen
            if (!Schema::hasColumn('interest_rates', 'min_months')) {
                $table->integer('min_months')->default(1)->after('cemetery_id');
            }
            if (!Schema::hasColumn('interest_rates', 'max_months')) {
                $table->integer('max_months')->nullable()->after('min_months');
            }
            
            // Migrar datos existentes: copiar 'months' a min_months y max_months
            DB::statement('UPDATE interest_rates SET min_months = months, max_months = months WHERE months IS NOT NULL');
            
            // Eliminar columna months antigua
            if (Schema::hasColumn('interest_rates', 'months')) {
                $table->dropColumn('months');
            }
            
            // Actualizar índice único
            $table->dropUnique(['cemetery_id', 'min_months']);
            $table->unique(['cemetery_id', 'min_months', 'max_months'], 'interest_rates_cemetery_min_max_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interest_rates', function (Blueprint $table) {
            // Restaurar columna months
            if (!Schema::hasColumn('interest_rates', 'months')) {
                $table->integer('months')->nullable()->after('cemetery_id');
            }
            
            // Migrar datos hacia atrás
            DB::statement('UPDATE interest_rates SET months = min_months WHERE min_months IS NOT NULL');
            
            // Eliminar nuevos campos
            if (Schema::hasColumn('interest_rates', 'min_months')) {
                $table->dropColumn('min_months');
            }
            if (Schema::hasColumn('interest_rates', 'max_months')) {
                $table->dropColumn('max_months');
            }
            
            // Renombrar cemetery_id de vuelta a tenant_id
            if (Schema::hasColumn('interest_rates', 'cemetery_id')) {
                $table->renameColumn('cemetery_id', 'tenant_id');
            }
            
            // Restaurar índice original
            $table->dropUnique('interest_rates_cemetery_min_max_unique');
            $table->unique(['tenant_id', 'months']);
        });
    }
};
