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
            // Eliminar campos de verificación que ahora están en customers
            $table->dropColumn([
                'whatsapp_verification_code',
                'whatsapp_verified_at',
                'phone_verified',
                'verification_code',
                'verified_at',
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('contracts', function (Blueprint $table) {
            // Revertir los campos eliminados por si se necesita rollback
            $table->string('whatsapp_verification_code', 6)->nullable()->after('monthly_payment');
            $table->timestamp('whatsapp_verified_at')->nullable()->after('whatsapp_verification_code');
            $table->boolean('phone_verified')->default(false)->after('whatsapp_verified_at');
            $table->string('verification_code', 6)->nullable()->after('phone_verified');
            $table->timestamp('verified_at')->nullable()->after('verification_code');
        });
    }
};
