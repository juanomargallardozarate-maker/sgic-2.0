<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Admin\TenantController;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
|
| Rutas accesibles sin autenticación.
|
*/

// Ruta raíz - Landing page
Route::get('/', function () {
    return view('welcome');
});

// Rutas de autenticación (Laravel Breeze)
require __DIR__.'/auth.php';

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS (Requieren Autenticación)
|--------------------------------------------------------------------------
|
| Todas las rutas dentro de este grupo requieren que el usuario
| esté autenticado. El middleware 'auth' verifica la sesión activa.
|
*/

Route::middleware(['auth'])->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | DASHBOARD (Detecta rol automáticamente)
    |--------------------------------------------------------------------------
    |
    | El DashboardController decide qué vista mostrar según el rol:
    | - super_admin → dashboard-superadmin (Panel SaaS)
    | - admin_cemetery → dashboard (Panel del Cementerio)
    | - admin → dashboard (Panel Administrativo)
    | - operativo → dashboard (Panel Operativo)
    | - consulta → dashboard (Panel de Solo Lectura)
    |
    */
    Route::get('/dashboard', [DashboardController::class, 'index'])
        ->name('dashboard');
    
    /*
    |--------------------------------------------------------------------------
    | PERFIL DE USUARIO (Breeze)
    |--------------------------------------------------------------------------
    |
    | Rutas estándar de Laravel Breeze para gestión de perfil.
    |
    */
    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| RUTAS SUPERADMIN (Gestión Multi-tenant)
|--------------------------------------------------------------------------
|
| Rutas exclusivas para el rol 'super_admin'.
| NO aplican el middleware 'tenant' porque el SuperAdmin
| no pertenece a ningún tenant específico.
|
| Middleware aplicados:
| - auth: Verifica autenticación
| - role:super_admin: Verifica que sea SuperAdmin (Spatie)
|
*/

Route::middleware(['auth', 'role:super_admin'])
    ->prefix('super-admin')
    ->name('super-admin.')
    ->group(function () {
        
        // CRUD completo de tenants
        Route::resource('tenants', TenantController::class);
        
        // Acciones específicas sobre tenants
        Route::post('tenants/{tenant}/suspend', [TenantController::class, 'suspend'])
            ->name('tenants.suspend');
        
        Route::post('tenants/{tenant}/activate', [TenantController::class, 'activate'])
            ->name('tenants.activate');
        
        Route::post('tenants/{tenant}/extend', [TenantController::class, 'extendSubscription'])
            ->name('tenants.extend');
        
        Route::post('tenants/{tenant}/change-plan', [TenantController::class, 'changePlan'])
            ->name('tenants.change-plan');


        // 🔍 RUTA TEMPORAL DE DEBUG
        Route::get('tenants/debug-logs', [TenantController::class, 'debugLogs'])->name('tenants.debug-logs');
   

    });

/*
|--------------------------------------------------------------------------
| RUTAS DEL TENANT (Cementerios Clientes)
|--------------------------------------------------------------------------
|
| Rutas para usuarios que pertenecen a un tenant específico.
| Aplican el middleware 'tenant' que:
| 1. Identifica el tenant desde el subdominio
| 2. Valida que el tenant esté activo
| 3. Valida que la suscripción esté vigente
| 4. Carga el tenant en la request y config
|
| NOTA: Estas rutas se habilitarán en el EPIC 2 (Inventario)
| Por ahora están comentadas para evitar errores.
|
*/

// Route::middleware(['auth', 'tenant', 'role:admin_cemetery|admin|operativo|consulta'])
//     ->group(function () {
//         
//         // EPIC 2: Inventario
//         Route::prefix('inventory')->name('inventory.')->group(function () {
//             Route::resource('sections', SectionController::class);
//             Route::resource('blocks', BlockController::class);
//             Route::resource('levels', LevelController::class);
//             Route::resource('crypts', CryptController::class);
//             Route::get('crypts/map', [CryptController::class, 'map'])->name('crypts.map');
//         });
//         
//         // EPIC 3: Comercial
//         Route::prefix('commercial')->name('commercial.')->group(function () {
//             Route::resource('customers', CustomerController::class);
//             Route::resource('contracts', ContractController::class);
//             Route::resource('reservations', ReservationController::class);
//         });
//         
//         // EPIC 4: Financiero
//         Route::prefix('financial')->name('financial.')->group(function () {
//             Route::resource('payments', PaymentController::class);
//             Route::resource('invoices', InvoiceController::class);
//             Route::get('debts', [DebtController::class, 'index'])->name('debts.index');
//         });
//         
//         // EPIC 5: Operaciones
//         Route::prefix('operations')->name('operations.')->group(function () {
//             Route::resource('work-orders', WorkOrderController::class);
//             Route::resource('crews', CrewController::class);
//         });
//     });