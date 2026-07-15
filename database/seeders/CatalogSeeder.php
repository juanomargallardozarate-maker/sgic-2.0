<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Estados de Cripta (RN-01, RN-03, RN-04)
        DB::table('crypt_statuses')->upsert([
            ['code' => 'available', 'name' => 'Disponible', 'color' => '#10B981', 'is_available_for_sale' => true, 'is_operational' => true, 'order' => 1],
            ['code' => 'occupied', 'name' => 'Ocupada', 'color' => '#EF4444', 'is_available_for_sale' => false, 'is_operational' => true, 'order' => 2],
            ['code' => 'reserved', 'name' => 'Reservada', 'color' => '#F59E0B', 'is_available_for_sale' => false, 'is_operational' => true, 'order' => 3],
            ['code' => 'maintenance', 'name' => 'En Mantenimiento', 'color' => '#3B82F6', 'is_available_for_sale' => false, 'is_operational' => false, 'order' => 4],
            ['code' => 'decaying', 'name' => 'En Decadencia', 'color' => '#8B5CF6', 'is_available_for_sale' => false, 'is_operational' => false, 'order' => 5],
            ['code' => 'blocked_debt', 'name' => 'Bloqueada por Morosidad', 'color' => '#6B7280', 'is_available_for_sale' => false, 'is_operational' => false, 'order' => 6],
        ], ['code'], ['name', 'color', 'is_available_for_sale', 'is_operational', 'order']);

        // 2. Tipos de Cripta
        DB::table('crypt_types')->upsert([
            ['code' => 'crypt', 'name' => 'Cripta', 'description' => 'Espacio para ataúd o urna', 'default_capacity' => 2, 'max_capacity' => 4],
            ['code' => 'niche', 'name' => 'Nicho', 'description' => 'Espacio pequeño para urnas cinerarias', 'default_capacity' => 1, 'max_capacity' => 2],
            ['code' => 'mausoleum', 'name' => 'Mausoleo', 'description' => 'Cripta de gran tamaño, familiar', 'default_capacity' => 4, 'max_capacity' => 8],
            ['code' => 'ossuary', 'name' => 'Osario', 'description' => 'Espacio común para restos de decadencia', 'default_capacity' => 1, 'max_capacity' => 1],
        ], ['code'], ['name', 'description', 'default_capacity', 'max_capacity']);

        $this->command->info('✅ Catálogos de infraestructura poblados exitosamente.');
    }
}