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
        Schema::table('interest_rates', function (Blueprint $table) {
            $table->foreignId('tenant_id')->after('id')->constrained()->onDelete('cascade');
            $table->dropUnique(['months']); // Quitamos el unique simple
            $table->unique(['tenant_id', 'months']); // Cada tenant tiene sus propias tasas por meses
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('interest_rates', function (Blueprint $table) {
            $table->dropUnique(['tenant_id', 'months']);
            $table->dropForeign(['tenant_id']);
            $table->dropColumn('tenant_id');
            $table->unique(['months']); // Restauramos el unique original
        });
    }
};
