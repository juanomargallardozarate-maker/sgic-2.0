<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Tenant;

class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info("Iniciando SettingsSeeder...");

        // Obtener todos los tenants activos
        $tenants = Tenant::where('is_active', true)->get();

        if ($tenants->isEmpty()) {
            $this->command->warn("No se encontraron tenants activos.");
            return;
        }

        foreach ($tenants as $tenant) {
            try {
                $this->command->info("Procesando tenant: {$tenant->name} (ID: {$tenant->id})");

                // 1. Inicializar el contexto del tenant
                // Esto cambia automáticamente la conexión a la BD correcta (ej. sgic_2)
                tenancy()->initialize($tenant);

                // 2. Verificar si la tabla existe y truncarla
                // Al estar en contexto, esto afecta a la BD del tenant, no a la central
                if (Schema::hasTable('interest_rates')) {
                    $this->command->info("  -> Limpiando tabla interest_rates en " . config('database.connections.tenant.database') . "...");
                    DB::table('interest_rates')->truncate();
                    $this->command->info("  -> Tabla limpiada correctamente.");
                } else {
                    $this->command->warn("  -> La tabla interest_rates no existe en este tenant.");
                }

                // 3. Insertar nuevos datos
                // Usamos inserción directa para evitar conflictos de modelos
                $rates = [
                    ['min' => 1, 'max' => 3, 'pct' => '5.00', 'desc' => 'Interés para 1-3 meses'],
                    ['min' => 4, 'max' => 6, 'pct' => '10.00', 'desc' => 'Interés para 4-6 meses'],
                    ['min' => 7, 'max' => 12, 'pct' => '15.00', 'desc' => 'Interés para 7-12 meses'],
                ];

                foreach ($rates as $rate) {
                    DB::table('interest_rates')->insert([
                        'tenant_id' => $tenant->id,
                        'cemetery_id' => $tenant->id,
                        'min_months' => $rate['min'],
                        'max_months' => $rate['max'],
                        'percentage' => $rate['pct'],
                        'description' => $rate['desc'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $this->command->info("  -> Datos insertados correctamente.");

                // 4. Cerrar contexto del tenant
                tenancy()->end();

            } catch (\Exception $e) {
                $this->command->error("Error crítico en tenant {$tenant->id}: " . $e->getMessage());
                // Asegurar cierre incluso si hay error
                if (tenancy()->isInitialized()) {
                    tenancy()->end();
                }
                return; // Detener ejecución si hay error grave
            }
        }

        $this->command->info("SettingsSeeder finalizado con éxito.");
    }
}