<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Intentar eliminar el índice antiguo o corrupto (ignorando errores si no existe)
        try {
            DB::statement("ALTER TABLE interest_rates DROP INDEX interest_rates_tenant_id_months_unique");
        } catch (\Exception $e) {
            // Si no existe, no pasa nada, continuamos.
        }

        try {
            DB::statement("ALTER TABLE interest_rates DROP INDEX unique_tenant_months");
        } catch (\Exception $e) {
            // Si no existe, no pasa nada.
        }

        // 2. Crear el índice correcto compuesto por las 3 columnas
        // Usamos un nombre nuevo para evitar conflictos de caché
        DB::statement("ALTER TABLE interest_rates ADD UNIQUE KEY unique_tenant_months_correct (tenant_id, min_months, max_months)");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        try {
            DB::statement("ALTER TABLE interest_rates DROP INDEX unique_tenant_months_correct");
            // Restaurar el índice incorrecto original (solo por consistencia al revertir)
            DB::statement("ALTER TABLE interest_rates ADD UNIQUE KEY interest_rates_tenant_id_months_unique (tenant_id)");
        } catch (\Exception $e) {
            // Ignorar errores al revertir
        }
    }
};