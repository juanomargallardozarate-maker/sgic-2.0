<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

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
            // El nombre del índice existente es 'beneficiaries_contract_id_customer_id_unique'
            // porque fue creado antes del renameColumn
            // Este índice puede estar siendo usado por una FK, así que necesitamos
            // primero eliminar la FK y luego el índice
            if (Schema::hasIndex('beneficiaries', 'beneficiaries_contract_id_customer_id_unique')) {
                // Primero eliminamos la foreign key constraint sobre customer_id si existe
                // La FK original se llamaba 'beneficiaries_customer_id_foreign' (creada por constrained())
                try {
                    $table->dropForeign(['customer_id']);
                } catch (\Exception $e) {
                    // Si no existe la FK, continuamos
                }
                
                // Ahora podemos eliminar el índice único
                try {
                    DB::statement('ALTER TABLE beneficiaries DROP INDEX beneficiaries_contract_id_customer_id_unique');
                } catch (\Exception $e) {
                    // Si no se puede eliminar, continuamos
                }
            }
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
