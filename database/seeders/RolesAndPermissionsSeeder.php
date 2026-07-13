<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Resetear caché de roles y permisos
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // =====================================================
        // 1. CREAR PERMISOS GRANULARES
        // =====================================================

        // Permisos de Inventario
        $inventoryPermissions = [
            'view-crypts',
            'create-crypts',
            'edit-crypts',
            'delete-crypts',
            'view-map',
            'export-inventory',
            'view-sections',
            'create-sections',
            'edit-sections',
            'delete-sections',
        ];

        // Permisos Comercial
        $commercialPermissions = [
            'view-customers',
            'create-customers',
            'edit-customers',
            'delete-customers',
            'view-contracts',
            'create-contracts',
            'edit-contracts',
            'sign-contracts',
            'view-reservations',
            'create-reservations',
            'view-heirs',
            'create-heirs',
        ];

        // Permisos Financiero
        $financialPermissions = [
            'view-payments',
            'create-payments',
            'edit-payments',
            'view-invoices',
            'create-invoices',
            'cancel-invoices',
            'stamp-invoices',
            'view-debts',
            'manage-debts',
            'view-financial-reports',
        ];

        // Permisos Operaciones
        $operationsPermissions = [
            'view-work-orders',
            'create-work-orders',
            'edit-work-orders',
            'complete-work-orders',
            'assign-crews',
            'view-crews',
            'create-crews',
            'edit-crews',
        ];

        // Permisos Auditoría
        $auditPermissions = [
            'view-audit-logs',
            'export-audit-logs',
        ];

        // Permisos Configuración
        $configPermissions = [
            'manage-settings',
            'manage-users',
            'view-tenant-config',
        ];

        // Permisos SuperAdmin
        $superAdminPermissions = [
            'manage-tenants',
            'suspend-tenants',
            'view-all-tenants',
            'view-system-health',
        ];

        // Crear todos los permisos
        $allPermissions = array_merge(
            $inventoryPermissions,
            $commercialPermissions,
            $financialPermissions,
            $operationsPermissions,
            $auditPermissions,
            $configPermissions,
            $superAdminPermissions
        );

        foreach ($allPermissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $this->command->info("✅ " . count($allPermissions) . " permisos creados");

        // =====================================================
        // 2. CREAR ROLES
        // =====================================================

        // SuperAdmin - Acceso total al SaaS
        $superAdmin = Role::firstOrCreate(['name' => 'super_admin']);
        $superAdmin->syncPermissions($allPermissions);

        // AdminCementerio - Acceso total dentro de su tenant
        $adminCemetery = Role::firstOrCreate(['name' => 'admin_cemetery']);
        $adminCemetery->syncPermissions(array_merge(
            $inventoryPermissions,
            $commercialPermissions,
            $financialPermissions,
            $operationsPermissions,
            $auditPermissions,
            $configPermissions
        ));

        // Administrativo - Ventas y cobranza
        $admin = Role::firstOrCreate(['name' => 'admin']);
        $admin->syncPermissions(array_merge(
            $inventoryPermissions,
            $commercialPermissions,
            $financialPermissions,
            $auditPermissions
        ));

        // Operativo - Solo OT de campo
        $operativo = Role::firstOrCreate(['name' => 'operativo']);
        $operativo->syncPermissions([
            'view-work-orders',
            'complete-work-orders',
            'view-crypts',
        ]);

        // Consulta - Solo lectura
        $consulta = Role::firstOrCreate(['name' => 'consulta']);
        $consulta->syncPermissions([
            'view-crypts',
            'view-map',
            'view-customers',
            'view-contracts',
            'view-payments',
            'view-invoices',
            'view-debts',
            'view-work-orders',
            'view-audit-logs',
        ]);

        $this->command->info('✅ 5 roles creados con permisos asignados');
        $this->command->info('   - super_admin (acceso total SaaS)');
        $this->command->info('   - admin_cemetery (acceso total tenant)');
        $this->command->info('   - admin (ventas y cobranza)');
        $this->command->info('   - operativo (campo)');
        $this->command->info('   - consulta (solo lectura)');
    }
}