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

        // Obtener el tenant específico
        $tenant = Tenant::find(1);
        
        if (!$tenant) {
            $this->command->error("No se encontró el tenant con ID 1");
            return;
        }

        $this->command->info("Procesando tenant: {$tenant->name} (ID: {$tenant->id})");
        
        // 1. Obtener el nombre de la base de datos del tenant
        $dbName = $tenant->db_name ?? 'sgic_' . $tenant->id; // Ajusta según tu lógica de nombres
        $this->command->info("  -> Base de datos objetivo: {$dbName}");

        // 2. Forzar el cambio de conexión a la BD del tenant manualmente
        // Configuramos una conexión temporal llamada 'tenant_temp'
        config(['database.connections.tenant_temp' => [
            'driver' => 'mysql',
            'host' => env('DB_HOST', '127.0.0.1'),
            'port' => env('DB_PORT', '3306'),
            'database' => $dbName,
            'username' => env('DB_USERNAME', 'root'),
            'password' => env('DB_PASSWORD', ''),
            'charset' => 'utf8mb4',
            'collation' => 'utf8mb4_unicode_ci',
            'prefix' => '',
            'strict' => true,
            'engine' => null,
        ]]);

        // 3. Usar EXPLÍCITAMENTE esta conexión para limpiar
        $this->command->info("  -> Ejecutando limpieza forzada en {$dbName}...");
        
        try {
            // Limpiar tabla usando la conexión directa
            DB::connection('tenant_temp')->table('interest_rates')->truncate();
            $this->command->info("  -> Tabla truncada exitosamente en {$dbName}.");
            
            // 4. Insertar datos usando la misma conexión directa
            $rates = [
                ['min' => 1, 'max' => 3, 'pct' => '5.00', 'desc' => 'Interés para 1-3 meses'],
                ['min' => 4, 'max' => 6, 'pct' => '10.00', 'desc' => 'Interés para 4-6 meses'],
                ['min' => 7, 'max' => 12, 'pct' => '15.00', 'desc' => 'Interés para 7-12 meses'],
            ];

            foreach ($rates as $rate) {
                DB::connection('tenant_temp')->table('interest_rates')->insert([
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
            
            $this->command->info("  -> Datos insertados correctamente en {$dbName}.");

        } catch (\Exception $e) {
            $this->command->error("Error crítico: " . $e->getMessage());
            // Opcional: Imprimir trace para depuración
            // $this->command->info($e->getTraceAsString());
        } finally {
            // Limpiar configuración temporal
            config(['database.connections.tenant_temp' => null]);
        }
        
        $this->command->info("SettingsSeeder finalizado.");
    }
}