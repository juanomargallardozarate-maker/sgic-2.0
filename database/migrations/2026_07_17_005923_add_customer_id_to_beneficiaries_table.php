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
        Schema::table('beneficiaries', function (Blueprint $table) {
            // Renombrar customer_id a beneficiary_customer_id para mayor claridad
            // Solo si customer_id existe y beneficiary_customer_id no existe
            if (Schema::hasColumn('beneficiaries', 'customer_id') && !Schema::hasColumn('beneficiaries', 'beneficiary_customer_id')) {
                $table->renameColumn('customer_id', 'beneficiary_customer_id');
            }
        });

        Schema::table('beneficiaries', function (Blueprint $table) {
            // Agregar customer_id que es el cliente dueño del beneficiario
            if (!Schema::hasColumn('beneficiaries', 'customer_id')) {
                $table->foreignId('customer_id')
                    ->after('beneficiary_customer_id')
                    ->constrained()
                    ->onDelete('cascade')
                    ->name('beneficiaries_customer_foreign');
            }
            
            // Actualizar índice único para prevenir duplicados
            $table->dropUnique(['contract_id', 'beneficiary_customer_id']);
            $table->unique(['customer_id', 'beneficiary_customer_id'], 'beneficiaries_customer_beneficiary_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('beneficiaries', function (Blueprint $table) {
            $table->dropUnique('beneficiaries_customer_beneficiary_unique');
            $table->dropForeign(['customer_id']);
            $table->dropColumn('customer_id');
            $table->renameColumn('beneficiary_customer_id', 'customer_id');
            $table->unique(['contract_id', 'customer_id']);
        });
    }
};
