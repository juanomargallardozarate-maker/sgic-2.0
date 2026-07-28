<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\ContractType;

class ContractTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $contractTypes = [
            [
                'code' => 'PERPETUAL',
                'name' => 'Contrato Perpetuo',
                'years' => null,
                'is_temporary' => false,
                'requires_renewal' => false,
                'description' => 'Contrato de vigencia perpetua, no requiere renovación.',
            ],
            [
                'code' => 'TEMPORAL_5',
                'name' => 'Contrato Temporal 5 Años',
                'years' => 5,
                'is_temporary' => true,
                'requires_renewal' => true,
                'description' => 'Contrato temporal con vigencia de 5 años, requiere renovación.',
            ],
            [
                'code' => 'TEMPORAL_10',
                'name' => 'Contrato Temporal 10 Años',
                'years' => 10,
                'is_temporary' => true,
                'requires_renewal' => true,
                'description' => 'Contrato temporal con vigencia de 10 años, requiere renovación.',
            ],
            [
                'code' => 'TEMPORAL_20',
                'name' => 'Contrato Temporal 20 Años',
                'years' => 20,
                'is_temporary' => true,
                'requires_renewal' => true,
                'description' => 'Contrato temporal con vigencia de 20 años, requiere renovación.',
            ],
        ];

        foreach ($contractTypes as $type) {
            ContractType::firstOrCreate(
                ['code' => $type['code']],
                $type
            );
        }
    }
}
