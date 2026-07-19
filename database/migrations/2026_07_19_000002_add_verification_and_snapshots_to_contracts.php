<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agrega campos adicionales a la tabla contracts para:
     * - phone_verified: Verificación de teléfono vía WhatsApp
     * - verification_code: Código de 6 dígitos para verificación
     * - verified_at: Timestamp de verificación
     * - maintenance_fee_snapshot: Valor congelado de cuota de mantenimiento
     * - interest_rate_snapshot: Tasa de interés congelada
     * - total_price: Campo inmutable para el precio total (separado de price)
     */
    public function up(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Campos de verificación de teléfono
            if (!Schema::hasColumn('contracts', 'phone_verified')) {
                $table->boolean('phone_verified')->default(false)->after('whatsapp_verified_at');
            }
            if (!Schema::hasColumn('contracts', 'verification_code')) {
                $table->string('verification_code', 6)->nullable()->after('phone_verified');
            }
            if (!Schema::hasColumn('contracts', 'verified_at')) {
                $table->timestamp('verified_at')->nullable()->after('verification_code');
            }
            
            // Snapshots de valores financieros al momento de crear el contrato
            if (!Schema::hasColumn('contracts', 'maintenance_fee_snapshot')) {
                $table->decimal('maintenance_fee_snapshot', 10, 2)->nullable()->after('verified_at');
            }
            if (!Schema::hasColumn('contracts', 'interest_rate_snapshot')) {
                $table->decimal('interest_rate_snapshot', 5, 4)->nullable()->after('maintenance_fee_snapshot');
            }
            
            // Campo total_price inmutable (si no existe)
            if (!Schema::hasColumn('contracts', 'total_price')) {
                $table->decimal('total_price', 12, 2)->nullable()->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $columns = [
                'phone_verified',
                'verification_code',
                'verified_at',
                'maintenance_fee_snapshot',
                'interest_rate_snapshot',
                'total_price'
            ];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('contracts', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
