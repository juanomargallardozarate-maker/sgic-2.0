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
            $this->command->warn('No se encontraron tenants activos.');
            return;
        }

        foreach ($tenants as $tenant) {
            try {
                $this->command->info("Procesando tenant: {$tenant->name} (ID: {$tenant->id})");

                // 1. Inicializar el contexto del tenant EXPLÍCITAMENTE
                // Esto cambia la conexión de DB a la base de datos del tenant (ej. sgic_tenant_1)
                tenancy()->initialize($tenant);

                // 2. Verificar conexión actual (Solo para debug, puedes quitarlo luego)
                $dbName = DB::connection()->getDatabaseName();
                $this->command->info("  -> Conectado a BD: {$dbName}");

                // 3. LIMPIEZA FORZADA CON SQL CRUDO
                // Usamos statement() para ejecutar SQL directo, evitando cualquier caché o scope de Eloquent
                $this->command->info("  -> Ejecutando limpieza forzada...");
                DB::statement('DELETE FROM interest_rates WHERE tenant_id = ?', [$tenant->id]);
                
                // Opcional: Resetear auto-incremento si es necesario
                // DB::statement('ALTER TABLE interest_rates AUTO_INCREMENT = 1');

                // 4. Verificar que esté vacía
                $count = DB::table('interest_rates')->where('tenant_id', $tenant->id)->count();
                if ($count > 0) {
                    $this->command->error("  -> ADVERTENCIA: La tabla aún tiene {$count} registros después de borrar.");
                    // Si sigue teniendo registros, algo muy raro pasa con la conexión, detenemos para no duplicar
                    tenancy()->end();
                    continue; 
                }
                $this->command->info("  -> Tabla limpia correctamente.");

                // 5. Insertar nuevos datos
                $rates = [
                    ['min' => 1, 'max' => 3, 'pct' => '5.00', 'desc' => 'Interés para 1-3 meses'],
                    ['min' => 4, 'max' => 6, 'pct' => '10.00', 'desc' => 'Interés para 4-6 meses'],
                    ['min' => 7, 'max' => 12, 'pct' => '15.00', 'desc' => 'Interés para 7-12 meses'],
                ];

                $now = now();
                foreach ($rates as $rate) {
                    DB::table('interest_rates')->insert([
                        'tenant_id' => $tenant->id,
                        'cemetery_id' => $tenant->id,
                        'min_months' => $rate['min'],
                        'max_months' => $rate['max'],
                        'percentage' => $rate['pct'],
                        'description' => $rate['desc'],
                        'is_active' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]);
                }

                $this->command->info("  -> Tasas insertadas correctamente en {$dbName}.");

                // 6. Cerrar contexto del tenant
                tenancy()->end();

            } catch (\Exception $e) {
                $this->command->error("Error crítico en tenant {$tenant->id}: " . $e->getMessage());
                // Asegurar cierre en caso de error
                try { tenancy()->end(); } catch (\Exception $ex) {}
                throw $e; // Relanzar para detener el proceso y ver el error
            }
        }

        $this->command->info("SettingsSeeder finalizado.");
    }
}