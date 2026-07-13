<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'code' => 'basic',
                'name' => 'Plan Básico',
                'description' => 'Ideal para cementerios pequeños con hasta 500 criptas',
                'monthly_price' => 1500.00,
                'annual_price' => 15000.00,
                'max_users' => 3,
                'max_crypts' => 500,
                'max_contracts' => 1000,
                'has_pwa' => false,
                'has_bi_reports' => false,
                'has_api_access' => false,
                'has_priority_support' => false,
                'has_custom_branding' => false,
                'is_active' => true,
                'order' => 1,
            ],
            [
                'code' => 'professional',
                'name' => 'Plan Profesional',
                'description' => 'Para cementerios medianos con operaciones completas',
                'monthly_price' => 3500.00,
                'annual_price' => 35000.00,
                'max_users' => 10,
                'max_crypts' => 2000,
                'max_contracts' => 5000,
                'has_pwa' => true,
                'has_bi_reports' => true,
                'has_api_access' => false,
                'has_priority_support' => true,
                'has_custom_branding' => false,
                'is_active' => true,
                'order' => 2,
            ],
            [
                'code' => 'enterprise',
                'name' => 'Plan Empresarial',
                'description' => 'Para grandes complejos funerarios con múltiples cementerios',
                'monthly_price' => 8000.00,
                'annual_price' => 80000.00,
                'max_users' => 50,
                'max_crypts' => 10000,
                'max_contracts' => 50000,
                'has_pwa' => true,
                'has_bi_reports' => true,
                'has_api_access' => true,
                'has_priority_support' => true,
                'has_custom_branding' => true,
                'is_active' => true,
                'order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            DB::table('subscription_plans')->updateOrInsert(
                ['code' => $plan['code']],
                $plan
            );
        }

        $this->command->info('✅ 3 planes de suscripción creados: Básico, Profesional, Empresarial');
    }
}