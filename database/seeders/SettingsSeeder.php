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

                // 1. Obtener el nombre REAL de la base de datos del tenant
                // Stancl guarda esto en la columna 'tenancy_db_name' o similar, 
                // pero si usas la estructura por defecto, a veces es necesario construirla.
                // Intentamos leerla directamente de los atributos del tenant.
                $dbName = $tenant->tenancy_db_name ?? null;

                // Si no existe el atributo directo, intentamos inferirlo o lanzar error para depurar
                if (!$dbName) {
                    // Fallback común si no hay columna explícita: a veces es 'sgic_' . $tenant->id
                    // Pero lo ideal es que el modelo tenga el dato. Si falla aquí, revisa tu migración de tenants.
                    // Para este caso, asumiremos que el tenant tiene el método getDatabaseName() o el atributo.
                    // Si usas la implementación estándar de Stancl, el nombre está en $tenant->tenancy_db_name.
                     throw new \Exception("No se pudo determinar el nombre de la BD para el tenant {$tenant->id}. Verifica la columna 'tenancy_db_name'.");
                }

                $this->command->info("  -> Base de datos objetivo confirmada: {$dbName}");

                // 2. FORZAR CONEXIÓN DIRECTA A LA BD DEL TENANT
                // Configuramos una conexión temporal llamada 'tenant_direct'
                config([
                    'database.connections.tenant_direct' => [
                        'driver' => 'mysql',
                        'host' => config('database.connections.mysql.host'),
                        'port' => config('database.connections.mysql.port'),
                        'database' => $dbName,
                        'username' => config('database.connections.mysql.username'),
                        'password' => config('database.connections.mysql.password'),
                        'charset' => 'utf8mb4',
                        'collation' => 'utf8mb4_unicode_ci',
                        'prefix' => '',
                        'strict' => true,
                        'engine' => null,
                    ],
                ]);

                // 3. LIMPIEZA FORZADA EN ESA CONEXIÓN ESPECÍFICA
                $this->command->info("  -> Ejecutando TRUNCATE en {$dbName}...");
                
                // Usamos DB::connection('tenant_direct') para asegurar que borramos en la BD correcta
                DB::connection('tenant_direct')->statement('SET FOREIGN_KEY_CHECKS=0;');
                DB::connection('tenant_direct')->table('interest_rates')->truncate();
                DB::connection('tenant_direct')->statement('SET FOREIGN_KEY_CHECKS=1;');

                $this->command->info("  -> Tabla truncada exitosamente en {$dbName}.");

                // 4. INSERCIÓN DIRECTA EN ESA MISMA CONEXIÓN
                $now = now();
                $rates = [
                    ['min' => 1, 'max' => 3, 'pct' => '5.00', 'desc' => 'Interés para 1-3 meses'],
                    ['min' => 4, 'max' => 6, 'pct' => '10.00', 'desc' => 'Interés para 4-6 meses'],
                    ['min' => 7, 'max' => 12, 'pct' => '15.00', 'desc' => 'Interés para 7-12 meses'],
                ];

                foreach ($rates as $rate) {
                    DB::connection('tenant_direct')->table('interest_rates')->insert([
                        'tenant_id' => $tenant->id,
                        'cemetery_id' => $tenant->id,
                        'min_months' => $rate['min'],
                        'max_months' => $rate['max'],
                        'percentage' => $rate['pct'],
                        'description' => $rate['desc'],
                        'is_active' => 1,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $this->command->info("  -> Datos insertados correctamente en {$dbName}.");

            } catch (\Exception $e) {
                $this->command->error("Error crítico en tenant {$tenant->id}: " . $e->getMessage());
                // No detenemos el proceso para ver errores en otros tenants si hubiera más
            }
        }

        $this->command->info("SettingsSeeder finalizado.");
    }
}