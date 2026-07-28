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
        Schema::table('contracts', function (Blueprint $table) {
            // Fecha de finalización del financiamiento (cuando se paga en parcialidades)
            $table->date('financing_end_date')->nullable()->after('end_date');
            
            // Saldo a financiar (precio total - enganche)
            $table->decimal('financed_amount', 12, 2)->nullable()->after('down_payment');
            
            // Tasa de interés aplicada (porcentaje)
            $table->decimal('interest_rate_applied', 5, 2)->default(0)->after('financed_amount');
            
            // Cuota mensual calculada (método francés)
            $table->decimal('monthly_payment', 10, 2)->nullable()->after('interest_rate_applied');
            
            // Campo para verificar contacto WhatsApp (código de verificación)
            $table->string('whatsapp_verification_code', 6)->nullable()->after('monthly_payment');
            $table->timestamp('whatsapp_verified_at')->nullable()->after('whatsapp_verification_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            $table->dropColumn([
                'financing_end_date',
                'financed_amount',
                'interest_rate_applied',
                'monthly_payment',
                'whatsapp_verification_code',
                'whatsapp_verified_at'
            ]);
        });
    }
};
