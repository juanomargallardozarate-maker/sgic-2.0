<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\Cemetery;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // =====================================================
        // 1. CREAR SUPERADMIN DEL SAAS (Ing. García)
        // =====================================================
        $superAdminRole = Role::where('name', 'super_admin')->first();

        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@sgic.mx'],
            [
                'name' => 'Ing. García',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
                'is_active' => true,
                'last_login_at' => null,
            ]
        );

        if ($superAdmin->wasRecentlyCreated) {
            $superAdmin->assignRole($superAdminRole);
            $this->command->info('✅ SuperAdmin creado: superadmin@sgic.mx / Password123!');
        } else {
            $this->command->info('⚠️  SuperAdmin ya existe');
        }

        // =====================================================
        // 2. CREAR TENANT DEMO: "Cementerio San José"
        // =====================================================
        $tenant = Tenant::firstOrCreate(
            ['subdomain' => 'sanjose'],
            [
                'name' => 'Cementerio San José',
                'rfc' => 'CSJ200101ABC',
                'plan' => 'professional',
                'grace_period_years' => 3,
                'debt_months_to_block' => 3,
                'moratorium_interest_rate' => 0.02, // 2% mensual
                'reservation_days' => 15,
                'reservation_deposit_percent' => 20.00,
                'maintenance_grace_days' => 30,
                'is_active' => true,
                'subscription_ends_at' => now()->addYear(),
                'settings' => [
                    'logo_url' => null,
                    'address' => 'Av. Principal #123, Col. Centro',
                    'phone' => '555-123-4567',
                    'email' => 'contacto@sanjose.com',
                ],
            ]
        );

        $this->command->info('✅ Tenant creado: Cementerio San José (sanjose)');

        // =====================================================
        // 3. CREAR CEMENTERIO ASOCIADO AL TENANT
        // =====================================================
        Cemetery::firstOrCreate(
            ['tenant_id' => $tenant->id],
            [
                'name' => 'Cementerio San José',
                'address' => 'Av. Principal #123, Col. Centro',
                'municipality' => 'Guadalajara',
                'state' => 'Jalisco',
                'postal_code' => '44100',
                'phone' => '33-1234-5678',
                'email' => 'contacto@sanjose.com',
                'legal_representative' => 'Don Roberto Martínez',
                'legal_representative_rfc' => 'MARR800101XYZ',
                'opening_time' => '08:00:00',
                'closing_time' => '18:00:00',
            ]
        );

        $this->command->info('✅ Cementerio asociado al tenant');

        // =====================================================
        // 4. CREAR USUARIO AdminCementerio (Don Roberto)
        // =====================================================
        $adminCemeteryRole = Role::where('name', 'admin_cemetery')->first();

        $adminCemetery = User::firstOrCreate(
            ['email' => 'admin@sanjose.sgic.mx'],
            [
                'name' => 'Don Roberto Martínez',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
                'is_active' => true,
                'last_login_at' => null,
            ]
        );

        if ($adminCemetery->wasRecentlyCreated) {
            $adminCemetery->assignRole($adminCemeteryRole);
            $this->command->info('✅ AdminCementerio creado: admin@sanjose.sgic.mx / Password123!');
        } else {
            $this->command->info('⚠️  AdminCementerio ya existe');
        }

        // =====================================================
        // 5. CREAR USUARIO Administrativo (María)
        // =====================================================
        $adminRole = Role::where('name', 'admin')->first();

        $adminUser = User::firstOrCreate(
            ['email' => 'maria@sanjose.sgic.mx'],
            [
                'name' => 'María López',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
                'is_active' => true,
                'last_login_at' => null,
            ]
        );

        if ($adminUser->wasRecentlyCreated) {
            $adminUser->assignRole($adminRole);
            $this->command->info('✅ Administrativo creado: maria@sanjose.sgic.mx / Password123!');
        } else {
            $this->command->info('⚠️  Administrativo ya existe');
        }

        // =====================================================
        // 6. CREAR USUARIO Operativo (Juan)
        // =====================================================
        $operativoRole = Role::where('name', 'operativo')->first();

        $operativoUser = User::firstOrCreate(
            ['email' => 'juan@sanjose.sgic.mx'],
            [
                'name' => 'Juan Pérez',
                'password' => Hash::make('Password123!'),
                'email_verified_at' => now(),
                'is_active' => true,
                'last_login_at' => null,
            ]
        );

        if ($operativoUser->wasRecentlyCreated) {
            $operativoUser->assignRole($operativoRole);
            $this->command->info('✅ Operativo creado: juan@sanjose.sgic.mx / Password123!');
        } else {
            $this->command->info('⚠️  Operativo ya existe');
        }

        $this->command->info('');
        $this->command->info(' SEEDER COMPLETADO');
        $this->command->info('═══════════════════════════════════════');
        $this->command->info('📧 superadmin@sgic.mx       / Password123!');
        $this->command->info('📧 admin@sanjose.sgic.mx    / Password123!');
        $this->command->info('📧 maria@sanjose.sgic.mx    / Password123!');
        $this->command->info('📧 juan@sanjose.sgic.mx     / Password123!');
        $this->command->info('═══════════════════════════════════════');
    }
}