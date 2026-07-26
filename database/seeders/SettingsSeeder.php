<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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
                $this->command->info("Procesando tenant: {$tenant->name} (ID: {$tenant->id})");

                // 1. Inicializar el contexto del tenant
                tenancy()->initialize($tenant);

                // 2. Limpiar la tabla interest_rates EXPLÍCITAMENTE para este tenant
                // Usamos DB::statement para asegurar que se ejecute sin modelos ni scopes
                $this->command->info("  -> Limpiando tabla interest_rates...");
                DB::statement('DELETE FROM interest_rates WHERE tenant_id = ?', [$tenant->id]);

                // 3. Definir los datos a insertar
                $rates = [
                    [
                        'min_months' => 1,
                        'max_months' => 3,
                        'percentage' => '5.00',
                        'description' => 'Interés para 1-3 meses',
                    ],
                    [
                        'min_months' => 4,
                        'max_months' => 6,
                        'percentage' => '10.00',
                        'description' => 'Interés para 4-6 meses',
                    ],
                    [
                        'min_months' => 7,
                        'max_months' => 12,
                        'percentage' => '15.00',
                        'description' => 'Interés para 7-12 meses',
                    ],
                ];

                // 4. Insertar manualmente usando DB::table para evitar conflictos de modelos
                $now = now();
                foreach ($rates as $rate) {
                    DB::table('interest_rates')->insert([
                        'tenant_id' => $tenant->id,
                        'cemetery_id' => $tenant->id, // Ajusta si cemetery_id es diferente
                        'min_months' => $rate['min_months'],
                        'max_months' => $rate['max_months'],
                        'percentage' => $rate['percentage'],
                        'description' => $rate['description'],
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $this->command->info("  -> Tasas insertadas correctamente.");

                // 5. Cerrar el contexto del tenant
                tenancy()->end();

            } catch (\Exception $e) {
                $this->command->error("Error crítico en tenant {$tenant->id}: " . $e->getMessage());
                
                // Asegurar que se cierre el contexto si hubo error
                try {
                    tenancy()->end();
                } catch (\Exception $closeEx) {
                    // Ignorar error al cerrar
                }
                
                // Lanzar la excepción para detener el proceso si algo salió muy mal
                throw $e;
            }
        }

        $this->command->info('SettingsSeeder completado exitosamente.');
    }
}