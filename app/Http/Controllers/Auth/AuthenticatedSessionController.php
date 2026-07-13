<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\AuditLog;
use App\Services\Audit\AuditService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class AuthenticatedSessionController extends Controller
{
    /**
     * Servicio de auditoría para registrar logins (RN-07)
     */
    private AuditService $auditService;

    /**
     * Constructor con inyección de dependencias
     */
    public function __construct(AuditService $auditService)
    {
        $this->auditService = $auditService;
    }

    /**
     * Muestra el formulario de login
     * US-1.2: Autenticación de usuarios internos
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Procesa la autenticación del usuario
     * 
     * Validaciones implementadas:
     * ✅ Rate limiting (5 intentos / 15 min bloqueo)
     * ✅ Verificación de usuario activo
     * ✅ Verificación de tenant activo (si aplica)
     * ✅ Verificación de suscripción vigente
     * ✅ Registro en audit_logs (RN-07)
     * ✅ Redirección según rol
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // 1. Autenticar usuario (con rate limiting automático)
        $request->authenticate();

        // 2. Obtener usuario autenticado
        $user = Auth::user();

        // 3. Validar que el usuario esté activo
        if (!$user->is_active) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return back()->withErrors([
                'email' => 'Tu cuenta ha sido desactivada. Contacta al administrador.',
            ]);
        }

        // 4. Validar tenant (solo si el usuario tiene tenant_id)
        if ($user->tenant_id) {
            $tenant = $user->tenant;

            // 4.1 Verificar que el tenant exista y esté activo
            if (!$tenant || !$tenant->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'El cementerio al que perteneces está suspendido. Contacta al administrador.',
                ]);
            }

            // 4.2 Verificar que la suscripción esté vigente
            if ($tenant->subscription_ends_at && $tenant->subscription_ends_at->isPast()) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return back()->withErrors([
                    'email' => 'La suscripción del cementerio ha vencido. Contacta al administrador.',
                ]);
            }

            // 4.3 Guardar tenant en sesión y config
            session(['tenant_id' => $tenant->id]);
            config(['app.tenant_id' => $tenant->id]);
        }

        // 5. Regenerar sesión por seguridad
        $request->session()->regenerate();

        // 6. Actualizar último login
        $user->update(['last_login_at' => now()]);

        // 7. Registrar login en audit_logs (RN-07)
        try {
            $this->auditService->log(
                action: 'login',
                model: $user,
                description: "Usuario {$user->email} inició sesión",
                tags: ['auth', 'login']
            );
        } catch (\Exception $e) {
            // No bloquear el login si falla la auditoría
            Log::warning('Error registrando login en audit_logs', [
                'user_id' => $user->id,
                'error' => $e->getMessage(),
            ]);
        }

        // 8. Redirigir según el rol del usuario
        return $this->redirectByRole($user);
    }

    /**
     * Cierra la sesión del usuario
     * US-1.2: Autenticación de usuarios internos
     */
    public function destroy(Request $request): RedirectResponse
    {
        $user = Auth::user();

        // 1. Registrar logout en audit_logs (RN-07)
        if ($user) {
            try {
                $this->auditService->log(
                    action: 'logout',
                    model: $user,
                    description: "Usuario {$user->email} cerró sesión",
                    tags: ['auth', 'logout']
                );
            } catch (\Exception $e) {
                Log::warning('Error registrando logout en audit_logs', [
                    'user_id' => $user->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        // 2. Cerrar sesión
        Auth::logout();

        // 3. Invalidar sesión y token CSRF
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 4. Redirigir al login
        return redirect('/');
    }

    /**
     * Redirige al usuario según su rol
     * 
     * Flujo de redirección:
     * - super_admin → /dashboard (Panel SaaS)
     * - admin_cemetery → /dashboard (Panel del Cementerio)
     * - admin → /dashboard (Panel Administrativo)
     * - operativo → /operations/field (PWA de Campo)
     * - consulta → /dashboard (Solo lectura)
     */
    private function redirectByRole($user): RedirectResponse
    {
        // SuperAdmin → Dashboard SaaS
        if ($user->hasRole('super_admin')) {
            return redirect()->intended(route('dashboard', absolute: false));
        }

        // Operativo → PWA de Campo (móvil)
        if ($user->hasRole('operativo')) {
            return redirect()->intended(route('operations.field.index', absolute: false));
        }

        // AdminCementerio, Administrativo, Consulta → Dashboard del Tenant
        return redirect()->intended(route('dashboard', absolute: false));
    }
}