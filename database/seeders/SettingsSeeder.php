<?php

namespace Database\Seeders;

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
        // Configuración de Cuota de Mantenimiento Anual
        GlobalSetting::setValue(
            'maintenance_fee', 
            1500.00, 
            'Cuota de mantenimiento anual del cementerio'
        );

        // Tasas de interés por cantidad de meses (ejemplos)
        $rates = [
            ['months' => 3, 'percentage' => 5.00, 'description' => 'Interés para 3 meses'],
            ['months' => 6, 'percentage' => 10.00, 'description' => 'Interés para 6 meses'],
            ['months' => 9, 'percentage' => 15.00, 'description' => 'Interés para 9 meses'],
            ['months' => 12, 'percentage' => 20.00, 'description' => 'Interés para 12 meses'],
            ['months' => 18, 'percentage' => 25.00, 'description' => 'Interés para 18 meses'],
            ['months' => 24, 'percentage' => 30.00, 'description' => 'Interés para 24 meses'],
            ['months' => 36, 'percentage' => 40.00, 'description' => 'Interés para 36 meses'],
        ];

        foreach ($rates as $rate) {
            InterestRate::updateOrCreate(
                ['months' => $rate['months']],
                [
                    'percentage' => $rate['percentage'],
                    'description' => $rate['description'],
                    'is_active' => true
                ]
            );
        }
    }
}
