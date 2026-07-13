<?php

namespace App\Http\Controllers;

use App\Models\Crypt;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    /**
     * Muestra el dashboard según el rol del usuario
     * US-1.2, US-6.1
     */
    public function index()
    {
        $user = Auth::user();

        // SuperAdmin → Dashboard SaaS
        if ($user->hasRole('super_admin')) {
            return $this->superAdminDashboard();
        }

        // AdminCementerio, Administrativo, Operativo, Consulta → Dashboard del Tenant
        return $this->tenantDashboard($user);
    }

    /**
     * Dashboard para SuperAdmin (Panel SaaS)
     */
 private function superAdminDashboard()
{
    $totalTenants = Tenant::count();
    $activeTenants = Tenant::where('is_active', true)->count();
    $suspendedTenants = Tenant::where('is_active', false)->count();
    
    $expiringTenantsCount = Tenant::whereNotNull('subscription_ends_at')
        ->where('subscription_ends_at', '<=', now()->addDays(30))
        ->where('subscription_ends_at', '>', now())
        ->count();
    
    $tenantsByPlan = Tenant::selectRaw('plan, COUNT(*) as count')
        ->groupBy('plan')
        ->pluck('count', 'plan')
        ->toArray();
    
    $planPrices = ['basic' => 1500, 'professional' => 3500, 'enterprise' => 8000];
    $totalMRR = Tenant::where('is_active', true)
        ->get()
        ->sum(fn($t) => $planPrices[$t->plan] ?? 0);
    
    $totalCrypts = Crypt::count();
    
    $recentTenants = Tenant::with(['cemetery', 'users'])
        ->orderBy('created_at', 'desc')
        ->limit(5)
        ->get();

    // ✅ CORRECCIÓN: Estructura exacta que espera la vista
    $stats = [
        'total_tenants' => $totalTenants,
        'active_tenants' => $activeTenants,
        'suspended_tenants' => $suspendedTenants,
        'subscription_expiring' => $expiringTenantsCount, // Coincide con la vista
        'tenants_by_plan' => $tenantsByPlan,
        'total_mrr' => $totalMRR,
        'total_crypts' => $totalCrypts,
        'recent_tenants' => $recentTenants,
        'critical_alerts' => [ // ✅ CORRECCIÓN: Agregado para evitar el error
            [
                'type' => 'warning',
                'title' => 'Suscripciones por Vencer',
                'description' => "{$expiringTenantsCount} tenant(s) vencen en los próximos 30 días.",
                'time' => 'Hace 1 hora',
            ],
            [
                'type' => 'error',
                'title' => 'Tenants Suspendidos',
                'description' => "{$suspendedTenants} tenant(s) suspendidos por falta de pago.",
                'time' => 'Hace 2 horas',
            ]
        ],
    ];
    
    return view('dashboard-superadmin', compact('stats'));
}

    /**
     * Dashboard para usuarios de Tenant
     * US-6.1: KPIs del cementerio
     */
    private function tenantDashboard($user)
    {
        // Si el usuario no tiene tenant_id, redirigir al login
        if (!$user->tenant_id) {
            return redirect()->route('login')
                ->with('error', 'No tienes un cementerio asignado. Contacta al administrador.');
        }

        $tenant = $user->tenant;

        // Verificar que el tenant esté activo
        if (!$tenant || !$tenant->is_active) {
            return redirect()->route('login')
                ->with('error', 'Tu cuenta está suspendida. Contacta al administrador.');
        }

        // KPIs del tenant
        $totalCrypts = Crypt::where('tenant_id', $tenant->id)->count();
        $occupiedCrypts = Crypt::where('tenant_id', $tenant->id)
            ->whereHas('cryptStatus', fn($q) => $q->where('code', 'occupied'))
            ->count();
        $availableCrypts = Crypt::where('tenant_id', $tenant->id)
            ->whereHas('cryptStatus', fn($q) => $q->where('code', 'available'))
            ->count();
        $occupancyRate = $totalCrypts > 0 ? round(($occupiedCrypts / $totalCrypts) * 100, 1) : 0;

        // Usuarios del tenant
        $totalUsers = User::where('tenant_id', $tenant->id)->count();

        // Contratos activos (si existe el modelo)
        $totalContracts = 0;
        if (class_exists(\App\Models\Contract::class)) {
            $totalContracts = \App\Models\Contract::where('tenant_id', $tenant->id)
                ->where('status', 'active')
                ->count();
        }

        $stats = [
            'tenant' => $tenant,
            'total_crypts' => $totalCrypts,
            'occupied_crypts' => $occupiedCrypts,
            'available_crypts' => $availableCrypts,
            'occupancy_rate' => $occupancyRate,
            'total_users' => $totalUsers,
            'total_contracts' => $totalContracts,
        ];

        return view('dashboard', compact('stats'));
    }
}