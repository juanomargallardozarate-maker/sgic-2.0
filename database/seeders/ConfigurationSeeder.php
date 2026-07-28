<?php

namespace Database\Seeders;

use App\Models\GlobalSetting;
use App\Models\InterestRate;
use Illuminate\Database\Seeder;

/**
 * Seeder de configuración para contratos
 * Inserta configuraciones globales y tasas de interés por defecto
 */
class ConfigurationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // ============================================
        // CONFIGURACIÓN GLOBAL (maintenance_fee)
        // ============================================
        // Nota: cemetery_id se asigna automáticamente via BelongsToTenant
        // o se puede especificar explícitamente
        
        GlobalSetting::updateOrCreate(
            ['key' => 'maintenance_fee'],
            [
                'value' => 1500.00,
                'description' => 'Cuota de mantenimiento mensual del cementerio',
                'is_active' => true,
            ]
        );

        // ============================================
        // TASAS DE INTERÉS POR RANGO DE MESES
        // ============================================
        // Rangos según requerimientos:
        // - 1-6 meses: 0%
        // - 7-12 meses: 5%
        // - 13-24 meses: 10%
        // - 25-60 meses: 15%
        
        $rates = [
            [
                'min_months' => 1,
                'max_months' => 6,
                'interest_rate' => 0.0000,
                'description' => 'Sin interés para 1-6 meses',
            ],
            [
                'min_months' => 7,
                'max_months' => 12,
                'interest_rate' => 5.0000,
                'description' => '5% anual para 7-12 meses',
            ],
            [
                'min_months' => 13,
                'max_months' => 24,
                'interest_rate' => 10.0000,
                'description' => '10% anual para 13-24 meses',
            ],
            [
                'min_months' => 25,
                'max_months' => 60,
                'interest_rate' => 15.0000,
                'description' => '15% anual para 25-60 meses',
            ],
        ];

        foreach ($rates as $rate) {
            InterestRate::updateOrCreate(
                [
                    'min_months' => $rate['min_months'],
                    'max_months' => $rate['max_months'],
                ],
                [
                    'interest_rate' => $rate['interest_rate'],
                    'description' => $rate['description'],
                    'is_active' => true,
                ]
            );
        }
        
        $this->command->info('Configuración de contratos sembrada exitosamente.');
        $this->command->info('- maintenance_fee: 1500.00');
        $this->command->info('- Tasas de interés: 4 rangos configurados (1-6, 7-12, 13-24, 25-60 meses)');
    }
}
