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
        Schema::table('customers', function (Blueprint $table) {
            // Campos para verificación de WhatsApp
            $table->string('whatsapp_verification_code', 6)->nullable()->after('phone');
            $table->timestamp('whatsapp_verified_at')->nullable()->after('whatsapp_verification_code');
            $table->boolean('phone_verified')->default(false)->after('whatsapp_verified_at');
            
            // Índice para búsquedas rápidas de clientes verificados
            $table->index('phone_verified');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropIndex(['phone_verified']);
            $table->dropColumn([
                'whatsapp_verification_code',
                'whatsapp_verified_at',
                'phone_verified',
            ]);
        });
    }
};
