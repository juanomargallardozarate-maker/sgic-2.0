<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\TenantController;
use App\Http\Controllers\Inventory\CryptController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return view('welcome');
});

require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS (Requieren Autenticación)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Dashboard (Detecta rol automáticamente)
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Perfil de usuario (Breeze)
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| RUTAS SUPERADMIN (Gestión Multi-tenant)
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'role:super_admin'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {

        // CRUD completo de tenants
        Route::resource('tenants', TenantController::class);

        // Acciones específicas sobre tenants
        Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])->name('tenants.suspend');
        Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])->name('tenants.activate');
        Route::post('tenants/{tenant}/extend', [TenantController::class, 'extendSubscription'])->name('tenants.extend');
        Route::post('tenants/{tenant}/change-plan', [TenantController::class, 'changePlan'])->name('tenants.change-plan');

        // Ruta temporal de debug (eliminar en producción)
        Route::get('tenants/debug-logs', [TenantController::class, 'debugLogs'])->name('tenants.debug-logs');
    });

/*
|--------------------------------------------------------------------------
| RUTAS DEL TENANT (Cementerios Clientes)
|--------------------------------------------------------------------------
| NOTA: El middleware 'tenant' identifica el cementerio por subdominio.
| El middleware 'role' ahora incluye 'super_admin' para permitir auditoría.
*/
Route::middleware(['auth', 'role:super_admin|admin_cemetery|admin|operativo|consulta'])
    ->group(function () {

        // EPIC 2: Inventario y Jerarquía
        Route::prefix('inventory')->name('inventory.')->group(function () {

            // ==========================================
            // GESTIÓN DE JERARQUÍA (SOLO ADMINISTRADORES)
            // ==========================================
            Route::middleware(['role:super_admin|admin_cemetery|admin'])
                ->prefix('hierarchy')
                ->name('hierarchy.')
                ->group(function () {
                    
                    // Vista principal de jerarquía
                    Route::get('/', [\App\Http\Controllers\Inventory\HierarchyController::class, 'index'])->name('index');
                    
                    // Secciones
                    Route::get('sections/create', [\App\Http\Controllers\Inventory\HierarchyController::class, 'createSection'])->name('sections.create');
                    Route::post('sections', [\App\Http\Controllers\Inventory\HierarchyController::class, 'storeSection'])->name('sections.store');
                    Route::get('sections/{section}/edit', [\App\Http\Controllers\Inventory\HierarchyController::class, 'editSection'])->name('sections.edit');
                    Route::put('sections/{section}', [\App\Http\Controllers\Inventory\HierarchyController::class, 'updateSection'])->name('sections.update');
                    Route::delete('sections/{section}', [\App\Http\Controllers\Inventory\HierarchyController::class, 'destroySection'])->name('sections.destroy');
                    
                    // Bloques
                    Route::get('blocks/create', [\App\Http\Controllers\Inventory\HierarchyController::class, 'createBlock'])->name('blocks.create');
                    Route::post('blocks', [\App\Http\Controllers\Inventory\HierarchyController::class, 'storeBlock'])->name('blocks.store');
                    Route::get('blocks/{block}/edit', [\App\Http\Controllers\Inventory\HierarchyController::class, 'editBlock'])->name('blocks.edit');
                    Route::put('blocks/{block}', [\App\Http\Controllers\Inventory\HierarchyController::class, 'updateBlock'])->name('blocks.update');
                    Route::delete('blocks/{block}', [\App\Http\Controllers\Inventory\HierarchyController::class, 'destroyBlock'])->name('blocks.destroy');
                    
                    // Niveles
                    Route::get('levels/create', [\App\Http\Controllers\Inventory\HierarchyController::class, 'createLevel'])->name('levels.create');
                    Route::post('levels', [\App\Http\Controllers\Inventory\HierarchyController::class, 'storeLevel'])->name('levels.store');
                    Route::get('levels/{level}/edit', [\App\Http\Controllers\Inventory\HierarchyController::class, 'editLevel'])->name('levels.edit');
                    Route::put('levels/{level}', [\App\Http\Controllers\Inventory\HierarchyController::class, 'updateLevel'])->name('levels.update');
                    Route::delete('levels/{level}', [\App\Http\Controllers\Inventory\HierarchyController::class, 'destroyLevel'])->name('levels.destroy');
                });

            // ==========================================
            // 1. RUTAS ESTÁTICAS (SIN PARÁMETROS) - PRIMERO
            // ==========================================
            
            // EPIC 3: Comercial (Contratos y Clientes)
            Route::prefix('commercial')->name('commercial.')->group(function () {
                // CRUD completo de clientes
                Route::resource('customers', \App\Http\Controllers\Commercial\CustomerController::class);

                // Acciones específicas de clientes
                Route::post('customers/{customer}/mark-as-deceased', [\App\Http\Controllers\Commercial\CustomerController::class, 'markAsDeceased'])->name('customers.mark-as-deceased');
                Route::delete('customers/{customer}/deactivate', [\App\Http\Controllers\Commercial\CustomerController::class, 'deactivate'])->name('customers.deactivate');
                Route::post('customers/{customer}/reactivate', [\App\Http\Controllers\Commercial\CustomerController::class, 'reactivate'])->name('customers.reactivate');
                Route::get('customers/export', [\App\Http\Controllers\Commercial\CustomerController::class, 'export'])->name('customers.export');
                
                // Gestión de beneficiarios
                Route::post('customers/{customer}/beneficiaries', [\App\Http\Controllers\Commercial\CustomerController::class, 'addBeneficiary'])->name('customers.beneficiaries.add');
                Route::delete('customers/{customer}/beneficiaries/{beneficiary}', [\App\Http\Controllers\Commercial\CustomerController::class, 'removeBeneficiary'])->name('customers.beneficiaries.remove');

                // CRUD completo de contratos
                Route::resource('contracts', \App\Http\Controllers\Commercial\ContractController::class);

                // Acciones específicas de contratos
                Route::post('contracts/{contract}/sign', [\App\Http\Controllers\Commercial\ContractController::class, 'sign'])->name('contracts.sign');
                Route::post('contracts/{contract}/renew', [\App\Http\Controllers\Commercial\ContractController::class, 'renew'])->name('contracts.renew');
                Route::post('contracts/{contract}/succession/start', [\App\Http\Controllers\Commercial\ContractController::class, 'startSuccession'])->name('contracts.succession.start');
                Route::post('contracts/{contract}/succession/complete', [\App\Http\Controllers\Commercial\ContractController::class, 'completeSuccession'])->name('contracts.succession.complete');
                Route::get('contracts/export', [\App\Http\Controllers\Commercial\ContractController::class, 'export'])->name('contracts.export');
            });

            // Rutas rápidas para crear jerarquía (AJAX)
            Route::post('hierarchy/sections', [\App\Http\Controllers\Inventory\CryptController::class, 'storeSection'])->name('hierarchy.sections.store');
            Route::post('hierarchy/blocks', [\App\Http\Controllers\Inventory\CryptController::class, 'storeBlock'])->name('hierarchy.blocks.store');
            Route::post('hierarchy/levels', [\App\Http\Controllers\Inventory\CryptController::class, 'storeLevel'])->name('hierarchy.levels.store');

            // ==========================================
            // 2. RUTAS CON PARÁMETROS - AL FINAL
            // ==========================================
            // Listado de criptas (índice)
            Route::get('crypts', [\App\Http\Controllers\Inventory\CryptController::class, 'index'])->name('crypts.index');
            Route::get('crypts/create', [\App\Http\Controllers\Inventory\CryptController::class, 'create'])->name('crypts.create');
            Route::post('crypts', [\App\Http\Controllers\Inventory\CryptController::class, 'store'])->name('crypts.store');
            
            // Mapa visual de criptas
            Route::get('crypts/map', [\App\Http\Controllers\Inventory\CryptController::class, 'map'])->name('crypts.map');
            
            // Importación masiva de criptas
            Route::get('crypts/import', [\App\Http\Controllers\Inventory\CryptController::class, 'showImport'])->name('crypts.import');
            Route::post('crypts/import', [\App\Http\Controllers\Inventory\CryptController::class, 'import'])->name('crypts.import.store');
            Route::get('crypts/import-template', [\App\Http\Controllers\Inventory\CryptController::class, 'downloadTemplate'])->name('crypts.import-template');
            
            Route::get('crypts/{crypt}', [\App\Http\Controllers\Inventory\CryptController::class, 'show'])->name('crypts.show');
            Route::get('crypts/{crypt}/edit', [\App\Http\Controllers\Inventory\CryptController::class, 'edit'])->name('crypts.edit');
            Route::get('crypts/{crypt}/api', [\App\Http\Controllers\Inventory\CryptController::class, 'apiShow'])->name('crypts.api-show');
            Route::put('crypts/{crypt}', [\App\Http\Controllers\Inventory\CryptController::class, 'update'])->name('crypts.update');
            Route::delete('crypts/{crypt}', [\App\Http\Controllers\Inventory\CryptController::class, 'destroy'])->name('crypts.destroy');
        });
    });
