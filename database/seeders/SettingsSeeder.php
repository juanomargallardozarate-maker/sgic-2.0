<?php

namespace Database\Seeders;

use App\Models\Cemetery;
use App\Models\GlobalSetting;
use App\Models\InterestRate;
use Illuminate\Database\Seeder;

class SettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Obtener el primer cementerio para asociar las configuraciones
        $cemetery = Cemetery::first();
        
        if (!$cemetery) {
            $this->command->warn('⚠️ No se encontró ningún cementerio. Las configuraciones no se pueden crear sin un cementerio.');
            return;
        }

        // Configuración de Cuota de Mantenimiento Anual
        GlobalSetting::setValue(
            'maintenance_fee', 
            1500.00, 
            'Cuota de mantenimiento anual del cementerio',
            $cemetery->id
        );

        // Tasas de interés por rango de meses (ejemplos)
        $rates = [
            ['min_months' => 1, 'max_months' => 3, 'percentage' => 5.00, 'description' => 'Interés para 1-3 meses'],
            ['min_months' => 4, 'max_months' => 6, 'percentage' => 10.00, 'description' => 'Interés para 4-6 meses'],
            ['min_months' => 7, 'max_months' => 9, 'percentage' => 15.00, 'description' => 'Interés para 7-9 meses'],
            ['min_months' => 10, 'max_months' => 12, 'percentage' => 20.00, 'description' => 'Interés para 10-12 meses'],
            ['min_months' => 13, 'max_months' => 18, 'percentage' => 25.00, 'description' => 'Interés para 13-18 meses'],
            ['min_months' => 19, 'max_months' => 24, 'percentage' => 30.00, 'description' => 'Interés para 19-24 meses'],
            ['min_months' => 25, 'max_months' => 36, 'percentage' => 40.00, 'description' => 'Interés para 25-36 meses'],
        ];

        foreach ($rates as $rate) {
            InterestRate::firstOrCreate(
                [
                    'cemetery_id' => $cemetery->id,
                    'min_months' => $rate['min_months'],
                    'max_months' => $rate['max_months']
                ],
                [
                    'percentage' => $rate['percentage'],
                    'description' => $rate['description'],
                    'is_active' => true
                ]
            );
        }
        
        $this->command->info('✅ Configuraciones y tasas de interés creadas exitosamente.');
    }
}
