<?php

/**
 * SGIC 2.0 - Rutas de Autenticación
 * ==================================
 * 
 * Este archivo maneja todas las rutas relacionadas con autenticación
 * según el PRD US-1.2 (Autenticación de usuarios internos).
 * 
 * Características implementadas:
 * ✅ Login con email + password (bcrypt)
 * ✅ Recuperación de contraseña vía email (token 15 min)
 * ✅ Verificación de email
 * ✅ Confirmación de contraseña para acciones sensibles
 * ✅ Cambio de contraseña
 * ✅ Logout con auditoría (RN-07)
 * ✅ Rate limiting en login (5 intentos / 15 min)
 * ✅ Protección CSRF automática
 * 
 * Controlador principal:
 * - AuthenticatedSessionController (refactorizado con validación de tenant)
 * 
 * @see App\Http\Controllers\Auth\AuthenticatedSessionController
 * @see PRD US-1.2
 * @see SDD §9.2 (Multi-tenancy)
 */

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS (Invitados)
|--------------------------------------------------------------------------
|
| Estas rutas están disponibles solo para usuarios NO autenticados.
| El middleware 'guest' redirige automáticamente al dashboard si el
| usuario ya está logueado.
|
| Nota: El registro de usuarios está restringido al SuperAdmin
| (ver TenantController para creación de usuarios por tenant).
|
*/

Route::middleware('guest')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Registro de Usuarios
    |--------------------------------------------------------------------------
    |
    | ⚠️ IMPORTANTE: En SGIC 2.0, el registro público está DESACTIVADO.
    | Los usuarios son creados por:
    | - SuperAdmin (al crear un nuevo tenant)
    | - AdminCementerio (para su propio tenant)
    |
    | Estas rutas se mantienen por compatibilidad con Breeze pero
    | deberían eliminarse en producción.
    |
    */
    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | Login (Autenticación Principal)
    |--------------------------------------------------------------------------
    |
    | Flujo de autenticación:
    | 1. Usuario ingresa email + password
    | 2. Se valida rate limiting (5 intentos / 15 min)
    | 3. Se autentica con bcrypt
    | 4. Se valida que el usuario esté activo
    | 5. Se valida que el tenant esté activo (si aplica)
    | 6. Se valida que la suscripción esté vigente
    | 7. Se registra en audit_logs (RN-07)
    | 8. Se redirige según rol (SuperAdmin → SaaS, otros → Tenant)
    |
    | @see AuthenticatedSessionController::store()
    |
    */
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login', [AuthenticatedSessionController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | Recuperación de Contraseña
    |--------------------------------------------------------------------------
    |
    | Flujo:
    | 1. Usuario solicita reset (email)
    | 2. Se genera token temporal (15 minutos)
    | 3. Usuario recibe email con link
    | 4. Usuario ingresa nueva contraseña
    | 5. Se valida y actualiza contraseña
    |
    */
    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

/*
|--------------------------------------------------------------------------
| RUTAS PROTEGIDAS (Autenticadas)
|--------------------------------------------------------------------------
|
| Estas rutas requieren que el usuario esté autenticado.
| El middleware 'auth' verifica la sesión activa.
|
*/

Route::middleware('auth')->group(function () {
    
    /*
    |--------------------------------------------------------------------------
    | Verificación de Email
    |--------------------------------------------------------------------------
    |
    | Opcional: Si se requiere email verificado para ciertas acciones,
    | se puede agregar el middleware 'verified' a rutas específicas.
    |
    */
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    /*
    |--------------------------------------------------------------------------
    | Confirmación de Contraseña
    |--------------------------------------------------------------------------
    |
    | Requerido antes de acciones sensibles (ej: cambiar email, eliminar cuenta).
    | La confirmación expira después de 3 horas por defecto.
    |
    */
    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    /*
    |--------------------------------------------------------------------------
    | Cambio de Contraseña
    |--------------------------------------------------------------------------
    |
    | Permite al usuario cambiar su contraseña actual.
    | Requiere confirmación de contraseña actual.
    |
    */
    Route::put('password', [PasswordController::class, 'update'])
        ->name('password.update');

    /*
    |--------------------------------------------------------------------------
    | Logout (Cierre de Sesión)
    |--------------------------------------------------------------------------
    |
    | Flujo:
    | 1. Se registra logout en audit_logs (RN-07)
    | 2. Se invalida la sesión
    | 3. Se regenera el token CSRF
    | 4. Se redirige al login
    |
    | @see AuthenticatedSessionController::destroy()
    |
    */
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});