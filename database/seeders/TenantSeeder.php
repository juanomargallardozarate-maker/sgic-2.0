<?php

namespace Database\Seeders;

use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class TenantSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Crear Rol SuperAdmin si no existe
        $superAdminRole = Role::firstOrCreate(['name' => 'super_admin']);

        // 2. Crear Usuario SuperAdmin
        $superAdmin = User::firstOrCreate(
            ['email' => 'superadmin@sgic.mx'],
            [
                'name' => 'Super Administrador',
                'password' => Hash::make('Password123!'),
                'tenant_id' => null, // SuperAdmin no pertenece a un tenant específico
            ]
        );
        
        $superAdmin->assignRole($superAdminRole);
        
        $this->command->info('✅ SuperAdmin creado: superadmin@sgic.mx / Password123!');

        // 3. Crear Tenant de Ejemplo (Cementerio San José)
        // ✅ CORRECCIÓN: Usar DB::table para evitar que stancl/tenancy agregue la columna 'data'
        $tenantId = (string) \Illuminate\Support\Str::uuid();
        
        DB::table('tenants')->insert([
            'id' => $tenantId,
            'name' => 'Cementerio San José',
            'rfc' => 'CSJ200101ABC',
            'subdomain' => 'sanjose',
            'plan' => 'professional',
            'grace_period_years' => 3,
            'debt_months_to_block' => 3,
            'moratorium_interest_rate' => 0.02,
            'reservation_days' => 15,
            'reservation_deposit_percent' => 20.00,
            'maintenance_grace_days' => 30,
            'is_active' => true,
            'subscription_ends_at' => now()->addYear(),
            'settings' => json_encode([
                'address' => 'Av. Principal #123, Col. Centro',
                'phone' => '555-123-4567',
                'email' => 'contacto@sanjose.com',
                'legal_representative' => 'Juan Pérez García',
                'legal_representative_rfc' => 'PEGJ800101ABC',
            ]),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->command->info('✅ Tenant creado: Cementerio San José (sanjose.sgic.mx)');

        // 4. Crear Usuario Admin para el Tenant
        $admin = User::create([
            'tenant_id' => $tenantId,
            'name' => 'Administrador San José',
            'email' => 'admin@sanjose.sgic.mx',
            'password' => Hash::make('Password123!'),
        ]);

        $adminRole = Role::firstOrCreate(['name' => 'admin_cemetery']);
        $admin->assignRole($adminRole);

        $this->command->info('✅ Admin Tenant creado: admin@sanjose.sgic.mx / Password123!');
    }
}