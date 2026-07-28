<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Actualiza la tabla global_settings para soportar multi-tenant con cemetery_id
     * y agrega campo is_active.
     */
    public function up(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            // Agregar campo is_active si no existe
            if (!Schema::hasColumn('global_settings', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('value');
            }
            
            // Renombrar tenant_id a cemetery_id para consistencia con el dominio
            if (Schema::hasColumn('global_settings', 'tenant_id')) {
                $table->renameColumn('tenant_id', 'cemetery_id');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('global_settings', function (Blueprint $table) {
            if (Schema::hasColumn('global_settings', 'cemetery_id')) {
                $table->renameColumn('cemetery_id', 'tenant_id');
            }
            
            if (Schema::hasColumn('global_settings', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }
};
