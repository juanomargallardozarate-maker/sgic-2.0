<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            // Agregar debt_type si no existe
            if (!Schema::hasColumn('debts', 'debt_type')) {
                $table->enum('debt_type', ['maintenance', 'sale_installment', 'service'])
                      ->after('crypt_id');
            }
            
            // Agregar original_amount si no existe
            if (!Schema::hasColumn('debts', 'original_amount')) {
                $table->decimal('original_amount', 12, 2)->after('debt_type');
            }
            
            // Agregar interest_amount si no existe
            if (!Schema::hasColumn('debts', 'interest_amount')) {
                $table->decimal('interest_amount', 12, 2)->default(0)->after('original_amount');
            }
            
            // Agregar total_amount si no existe
            if (!Schema::hasColumn('debts', 'total_amount')) {
                $table->decimal('total_amount', 12, 2)->after('interest_amount');
            }
            
            // Agregar paid_amount si no existe
            if (!Schema::hasColumn('debts', 'paid_amount')) {
                $table->decimal('paid_amount', 12, 2)->default(0)->after('total_amount');
            }
            
            // Agregar pending_amount si no existe
            if (!Schema::hasColumn('debts', 'pending_amount')) {
                $table->decimal('pending_amount', 12, 2)->after('paid_amount');
            }
            
            // Agregar days_overdue si no existe
            if (!Schema::hasColumn('debts', 'days_overdue')) {
                $table->integer('days_overdue')->default(0)->after('blocked_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('debts', function (Blueprint $table) {
            if (Schema::hasColumn('debts', 'days_overdue')) {
                $table->dropColumn('days_overdue');
            }
            if (Schema::hasColumn('debts', 'pending_amount')) {
                $table->dropColumn('pending_amount');
            }
            if (Schema::hasColumn('debts', 'paid_amount')) {
                $table->dropColumn('paid_amount');
            }
            if (Schema::hasColumn('debts', 'total_amount')) {
                $table->dropColumn('total_amount');
            }
            if (Schema::hasColumn('debts', 'interest_amount')) {
                $table->dropColumn('interest_amount');
            }
            if (Schema::hasColumn('debts', 'original_amount')) {
                $table->dropColumn('original_amount');
            }
            if (Schema::hasColumn('debts', 'debt_type')) {
                $table->dropColumn('debt_type');
            }
        });
    }
};