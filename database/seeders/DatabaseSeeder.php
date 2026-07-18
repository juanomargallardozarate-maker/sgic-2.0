<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info(' Iniciando seeders del SGIC 2.0...');
        $this->command->info('');

        // Orden CRÍTICO: primero roles/permisos, luego catálogos, luego tenant
        $this->call([
            RolesAndPermissionsSeeder::class,
            CatalogSeeder::class,
            ContractTypeSeeder::class,
            TenantSeeder::class,
        ]);

        $this->command->info('');
        $this->command->info('✅ Todos los seeders completados exitosamente');
    }
}