<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================
        // 1. ESTADOS DE CRIPTA (RN-01)
        // =====================================================
        $cryptStatuses = [
            [
                'code' => 'available',
                'name' => 'Disponible',
                'color' => '#10B981', // Verde
                'icon' => 'check-circle',
                'is_available_for_sale' => true,
                'is_operational' => true,
                'order' => 1,
            ],
            [
                'code' => 'occupied',
                'name' => 'Ocupada',
                'color' => '#EF4444', // Rojo
                'icon' => 'user',
                'is_available_for_sale' => false,
                'is_operational' => true,
                'order' => 2,
            ],
            [
                'code' => 'reserved',
                'name' => 'Reservada',
                'color' => '#F59E0B', // Amarillo
                'icon' => 'clock',
                'is_available_for_sale' => false,
                'is_operational' => true,
                'order' => 3,
            ],
            [
                'code' => 'maintenance',
                'name' => 'En Mantenimiento',
                'color' => '#3B82F6', // Azul
                'icon' => 'wrench',
                'is_available_for_sale' => false,
                'is_operational' => false,
                'order' => 4,
            ],
            [
                'code' => 'decaying',
                'name' => 'En Decadencia',
                'color' => '#8B5CF6', // Morado
                'icon' => 'alert-triangle',
                'is_available_for_sale' => false,
                'is_operational' => false,
                'order' => 5,
            ],
            [
                'code' => 'blocked_debt',
                'name' => 'Bloqueada por Morosidad',
                'color' => '#6B7280', // Gris
                'icon' => 'lock',
                'is_available_for_sale' => false,
                'is_operational' => false,
                'order' => 6,
            ],
        ];

        foreach ($cryptStatuses as $status) {
            DB::table('crypt_statuses')->updateOrInsert(
                ['code' => $status['code']],
                $status
            );
        }

        $this->command->info('✅ Estados de cripta creados/actualizados');

        // =====================================================
        // 2. TIPOS DE CRIPTA
        // =====================================================
        $cryptTypes = [
            [
                'code' => 'crypt',
                'name' => 'Cripta',
                'description' => 'Espacio para depósito de ataúdes',
                'default_capacity' => 2,
                'max_capacity' => 4,
            ],
            [
                'code' => 'niche',
                'name' => 'Nicho',
                'description' => 'Espacio pequeño para urnas cinerarias',
                'default_capacity' => 1,
                'max_capacity' => 2,
            ],
            [
                'code' => 'mausoleum',
                'name' => 'Mausoleo',
                'description' => 'Cripta de gran tamaño, familiar, con acceso interior',
                'default_capacity' => 4,
                'max_capacity' => 6,
            ],
            [
                'code' => 'ossuary',
                'name' => 'Osario',
                'description' => 'Espacio común para restos provenientes de decadencia',
                'default_capacity' => 1,
                'max_capacity' => 1,
            ],
        ];

        foreach ($cryptTypes as $type) {
            DB::table('crypt_types')->updateOrInsert(
                ['code' => $type['code']],
                $type
            );
        }

        $this->command->info('✅ Tipos de cripta creados/actualizados');

        // =====================================================
        // 3. TIPOS DE CONTRATO (RN-02)
        // =====================================================
        $contractTypes = [
            [
                'code' => 'perpetual',
                'name' => 'Perpetuidad',
                'years' => null,
                'is_temporary' => false,
                'requires_renewal' => false,
                'description' => 'Derecho de uso indefinido. Genera cobros anuales de mantenimiento.',
            ],
            [
                'code' => 'temporary_10',
                'name' => 'Temporal 10 años',
                'years' => 10,
                'is_temporary' => true,
                'requires_renewal' => true,
                'description' => 'Concesión por 10 años. Requiere renovación al vencimiento.',
            ],
            [
                'code' => 'temporary_25',
                'name' => 'Temporal 25 años',
                'years' => 25,
                'is_temporary' => true,
                'requires_renewal' => true,
                'description' => 'Concesión por 25 años. Requiere renovación al vencimiento.',
            ],
            [
                'code' => 'temporary_50',
                'name' => 'Temporal 50 años',
                'years' => 50,
                'is_temporary' => true,
                'requires_renewal' => true,
                'description' => 'Concesión por 50 años. Requiere renovación al vencimiento.',
            ],
        ];

        foreach ($contractTypes as $type) {
            DB::table('contract_types')->updateOrInsert(
                ['code' => $type['code']],
                $type
            );
        }

        $this->command->info('✅ Tipos de contrato creados/actualizados');

        // =====================================================
        // 4. TIPOS DE ÓRDENES DE TRABAJO (RN-06)
        // =====================================================
        $workOrderTypes = [
            [
                'code' => 'inhumation',
                'name' => 'Inhumación',
                'requires_sanitary_validation' => true,
                'requires_death_certificate' => true,
                'requires_family_signature' => true,
                'min_photos' => 2,
                'max_photos' => 10,
                'description' => 'Depósito de restos en cripta',
            ],
            [
                'code' => 'exhumation',
                'name' => 'Exhumación',
                'requires_sanitary_validation' => true,
                'requires_death_certificate' => false,
                'requires_family_signature' => true,
                'min_photos' => 2,
                'max_photos' => 10,
                'description' => 'Retiro de restos de cripta',
            ],
            [
                'code' => 'transfer',
                'name' => 'Traslado',
                'requires_sanitary_validation' => true,
                'requires_death_certificate' => false,
                'requires_family_signature' => true,
                'min_photos' => 1,
                'max_photos' => 5,
                'description' => 'Traslado de restos a osario común',
            ],
            [
                'code' => 'cleaning',
                'name' => 'Limpieza',
                'requires_sanitary_validation' => false,
                'requires_death_certificate' => false,
                'requires_family_signature' => false,
                'min_photos' => 2,
                'max_photos' => 5,
                'description' => 'Limpieza de cripta',
            ],
            [
                'code' => 'maintenance',
                'name' => 'Mantenimiento',
                'requires_sanitary_validation' => false,
                'requires_death_certificate' => false,
                'requires_family_signature' => false,
                'min_photos' => 1,
                'max_photos' => 5,
                'description' => 'Mantenimiento preventivo de cripta',
            ],
        ];

        foreach ($workOrderTypes as $type) {
            DB::table('work_order_types')->updateOrInsert(
                ['code' => $type['code']],
                $type
            );
        }

        $this->command->info('✅ Tipos de órdenes de trabajo creados/actualizados');

        $this->command->info('');
        $this->command->info('🎉 Catálogos poblados correctamente');
    }
}