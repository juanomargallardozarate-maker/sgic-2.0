<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Tenant;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->command->info('Iniciando SettingsSeeder...');

        // Obtener todos los tenants activos
        $tenants = Tenant::where('is_active', true)->get();

        if ($tenants->isEmpty()) {
            $this->command->warn('No se encontraron tenants activos.');
            return;
        }

        foreach ($tenants as $tenant) {
            try {
                // Inicializar el contexto del tenant
                tenancy()->initialize($tenant);
                
                $this->command->info("Procesando tenant: {$tenant->name} (ID: {$tenant->id})");

                // Datos a insertar/actualizar
                $rates = [
                    [
                        'tenant_id' => $tenant->id,
                        'cemetery_id' => $tenant->id,
                        'min_months' => 1,
                        'max_months' => 3,
                        'percentage' => '5.00',
                        'description' => 'Interés para 1-3 meses',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'tenant_id' => $tenant->id,
                        'cemetery_id' => $tenant->id,
                        'min_months' => 4,
                        'max_months' => 6,
                        'percentage' => '10.00',
                        'description' => 'Interés para 4-6 meses',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                    [
                        'tenant_id' => $tenant->id,
                        'cemetery_id' => $tenant->id,
                        'min_months' => 7,
                        'max_months' => 12,
                        'percentage' => '15.00',
                        'description' => 'Interés para 7-12 meses',
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ],
                ];

                // Usar upsert para evitar errores de duplicidad
                // Si existe (por tenant_id, min_months, max_months), lo actualiza. Si no, lo inserta.
                DB::table('interest_rates')->upsert(
                    $rates,
                    ['tenant_id', 'min_months', 'max_months'], // Claves únicas para verificar conflicto
                    ['percentage', 'description', 'is_active', 'updated_at'] // Campos a actualizar si existe
                );

                $this->command->info("  -> Tasas procesadas correctamente (Insertadas o Actualizadas).");

                // Cerrar contexto
                tenancy()->end();

            } catch (\Exception $e) {
                $this->command->error("Error crítico en tenant {$tenant->id}: " . $e->getMessage());
                // Asegurar cierre incluso en error
                try { tenancy()->end(); } catch (\Exception $ex) {}
            }
        }

        $this->command->info('SettingsSeeder finalizado.');
    }
}