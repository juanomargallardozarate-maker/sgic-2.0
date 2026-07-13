<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            // Solo agregar columnas si NO existen
            if (!Schema::hasColumn('audit_logs', 'model_type')) {
                $table->string('model_type', 100)->nullable()->after('action');
            }
            
            if (!Schema::hasColumn('audit_logs', 'model_id')) {
                $table->unsignedBigInteger('model_id')->nullable()->after('model_type');
            }
            
            if (!Schema::hasColumn('audit_logs', 'old_values')) {
                $table->json('old_values')->nullable()->after('model_id');
            }
            
            if (!Schema::hasColumn('audit_logs', 'new_values')) {
                $table->json('new_values')->nullable()->after('old_values');
            }
            
            if (!Schema::hasColumn('audit_logs', 'ip_address')) {
                $table->string('ip_address', 45)->nullable()->after('new_values');
            }
            
            if (!Schema::hasColumn('audit_logs', 'user_agent')) {
                $table->text('user_agent')->nullable()->after('ip_address');
            }
            
            if (!Schema::hasColumn('audit_logs', 'url')) {
                $table->string('url', 500)->nullable()->after('user_agent');
            }
            
            if (!Schema::hasColumn('audit_logs', 'tags')) {
                $table->json('tags')->nullable()->after('url');
            }
        });

        // Agregar índices de forma segura (try/catch para evitar duplicados)
        try {
            Schema::table('audit_logs', function (Blueprint $table) {
                $existingIndexes = collect(Schema::getIndexes('audit_logs'))
                    ->pluck('name')
                    ->toArray();
                
                if (!in_array('audit_logs_model_type_model_id_index', $existingIndexes)) {
                    $table->index(['model_type', 'model_id']);
                }
                
                if (!in_array('audit_logs_action_index', $existingIndexes)) {
                    $table->index('action');
                }
                
                if (!in_array('audit_logs_tenant_id_user_id_index', $existingIndexes)) {
                    $table->index(['tenant_id', 'user_id']);
                }
            });
        } catch (\Exception $e) {
            // Si falla algún índice, continuar (los índices son opcionales)
            \Log::warning('Error creando índices en audit_logs: ' . $e->getMessage());
        }
    }

    public function down(): void
    {
        Schema::table('audit_logs', function (Blueprint $table) {
            $columns = ['model_type', 'model_id', 'old_values', 'new_values', 
                       'ip_address', 'user_agent', 'url', 'tags'];
            
            foreach ($columns as $column) {
                if (Schema::hasColumn('audit_logs', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};