<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->string('colonia', 150)->nullable()->after('address');
            $table->string('ciudad', 100)->nullable()->after('colonia');
            $table->string('estado', 100)->nullable()->after('ciudad');
            $table->string('codigo_postal', 10)->nullable()->after('estado');
        });
    }

    public function down(): void
    {
        Schema::table('customers', function (Blueprint $table) {
            $table->dropColumn(['colonia', 'ciudad', 'estado', 'codigo_postal']);
        });
    }
};
