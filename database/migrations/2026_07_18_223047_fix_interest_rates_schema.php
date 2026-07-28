<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('interest_rates', function (Blueprint $table) {
            // 1. Asegurar que cemetery_id existe
            if (!Schema::hasColumn('interest_rates', 'cemetery_id')) {
                $table->foreignId('cemetery_id')->nullable()->constrained('cemeteries')->onDelete('cascade');
            }

            // 2. Asegurar que min_months y max_months existen
            if (!Schema::hasColumn('interest_rates', 'min_months')) {
                $table->integer('min_months')->default(1)->after('cemetery_id');
            }
            
            if (!Schema::hasColumn('interest_rates', 'max_months')) {
                $table->integer('max_months')->default(12)->after('min_months');
            }

            // 3. Eliminar la columna antigua 'months' si aún existe (limpieza)
            if (Schema::hasColumn('interest_rates', 'months')) {
                $table->dropColumn('months');
            }
        });
    }

    public function down(): void
    {
        Schema::table('interest_rates', function (Blueprint $table) {
            if (Schema::hasColumn('interest_rates', 'min_months')) {
                $table->dropColumn(['min_months', 'max_months']);
            }
            // No eliminamos cemetery_id aquí para no romper otras dependencias si las hubiera
        });
    }
};