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
        // Obtenemos todos los tenants activos
        $tenants = Tenant::where('is_active', true)->get();

        if ($tenants->isEmpty()) {
            $this->command->warn('No hay tenants activos para seedear.');
            return;
        }

        foreach ($tenants as $tenant) {
            try {
                // 1. Inicializar el contexto del tenant (CRUCIAL)
                tenancy()->initialize($tenant);

                $this->command->info("Procesando tenant: {$tenant->name} (ID: {$tenant->id})");

                // 2. LIMPIAR la tabla interest_rates para ESTE tenant específico
                // Usamos delete() con where para asegurar que borramos solo los de este tenant
                // si por alguna razón la conexión apunta a una DB compartida, aunque con tenancy() debería ser la correcta.
                DB::table('interest_rates')->where('tenant_id', $tenant->id)->delete();
                
                // Opción alternativa más agresiva si estás seguro de estar en la DB del tenant:
                // DB::table('interest_rates')->truncate(); 

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
                foreach ($rates as $rate) {
                    DB::table('interest_rates')->insert([
                        'tenant_id' => $tenant->id,
                        'cemetery_id' => $tenant->id, // Ajusta si cemetery_id es diferente
                        'min_months' => $rate['min_months'],
                        'max_months' => $rate['max_months'],
                        'percentage' => $rate['percentage'],
                        'description' => $rate['description'],
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $this->command->info("  -> Tasas de interés insertadas correctamente.");

                // 5. Cerrar el contexto del tenant
                tenancy()->end();

            } catch (\Exception $e) {
                $this->command->error("Error al procesar tenant {$tenant->id}: " . $e->getMessage());
                // Asegurarse de cerrar el contexto en caso de error
                if (tenancy()->isInitialized()) {
                    tenancy()->end();
                }
            }
        }
        
        $this->command->info('Seed completado para todos los tenants.');
    }
}