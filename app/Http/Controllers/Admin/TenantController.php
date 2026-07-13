<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Tenant;
use App\Models\Cemetery;
use App\Models\User;
use App\Models\SubscriptionPlan;
use App\Models\SubscriptionHistory;
use App\Services\RfcValidator;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TenantController extends Controller
{
    public function index(Request $request)
    {
        // ✅ CORRECCIÓN: Usar withCount para evitar N+1 y no cargar users.tenant_id
        $query = Tenant::with(['cemetery'])
            ->withCount(['crypts', 'users']);
        
        // Filtros
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('subdomain', 'like', "%{$search}%")
                  ->orWhere('rfc', 'like', "%{$search}%");
            });
        }
        
        if ($request->filled('plan')) {
            $query->where('plan', $request->plan);
        }
        
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'active');
        }
        
        $tenants = $query->orderBy('created_at', 'desc')->paginate(15);
        
        // Calcular KPIs reales
        $totalTenants = Tenant::count();
        $activeTenants = Tenant::where('is_active', true)->count();
        $suspendedTenants = Tenant::where('is_active', false)->count();
        
        // Tenants por vencer (próximos 30 días)
        $expiringTenants = Tenant::whereNotNull('subscription_ends_at')
            ->where('subscription_ends_at', '<=', now()->addDays(30))
            ->where('subscription_ends_at', '>', now())
            ->count();
        
        // Distribución por plan
        $planCounts = Tenant::selectRaw('plan, COUNT(*) as count')
            ->groupBy('plan')
            ->pluck('count', 'plan')
            ->toArray();
        
        // MRR total (basado en precios de planes)
        $planPrices = ['basic' => 1500, 'professional' => 3500, 'enterprise' => 8000];
        $totalMRR = Tenant::where('is_active', true)
            ->get()
            ->sum(fn($t) => $planPrices[$t->plan] ?? 0);
        
        // Total criptas gestionadas
        $totalCrypts = \App\Models\Crypt::count();
        
        // Transformar tenants para Alpine.js
        $tenantsData = $tenants->map(function ($tenant) use ($planPrices) {
            $daysUntilExpiry = $tenant->subscription_ends_at 
                ? now()->diffInDays($tenant->subscription_ends_at, false) 
                : 0;
            
            // Determinar status
            $status = 'active';
            $statusLabel = 'Activo';
            if (!$tenant->is_active) {
                $status = 'suspended';
                $statusLabel = 'Suspendido';
            } elseif ($daysUntilExpiry <= 30 && $daysUntilExpiry > 0) {
                $status = 'expiring';
                $statusLabel = 'Por Vencer';
            } elseif ($daysUntilExpiry <= 0) {
                $status = 'expired';
                $statusLabel = 'Vencido';
            }
            
            return [
                'id' => $tenant->id,
                'name' => $tenant->name,
                'initials' => strtoupper(substr($tenant->name, 0, 2)),
                'rfc' => $tenant->rfc,
                'subdomain' => $tenant->subdomain,
                'plan' => $tenant->plan,
                'mrr' => $planPrices[$tenant->plan] ?? 0,
                'status' => $status,
                'statusLabel' => $statusLabel,
                'expiresAt' => $tenant->subscription_ends_at?->format('Y-m-d') ?? 'N/A',
                'daysUntilExpiry' => max(0, $daysUntilExpiry),
                'gracePeriod' => $tenant->grace_period_years,
                'blockMonths' => $tenant->debt_months_to_block,
                'interestRate' => $tenant->moratorium_interest_rate * 100,
                'reservationDays' => $tenant->reservation_days,
                'reservationDeposit' => $tenant->reservation_deposit_percent,
                // ✅ CORRECCIÓN: Usar withCount
                'crypts' => $tenant->crypts_count ?? 0,
                'users' => $tenant->users_count ?? 0,
                'occupancy' => 0,
                'address' => $tenant->cemetery?->address ?? 'N/A',
                'municipality' => $tenant->cemetery?->municipality ?? 'N/A',
                'legalRep' => $tenant->cemetery?->legal_representative ?? 'N/A',
                'adminName' => 'N/A',
                'adminEmail' => 'N/A',
                'createdAt' => $tenant->created_at->format('d M Y'),
                'usersList' => [],
                'auditLogs' => [],
            ];
        })->toArray();
        
        return view('super-admin.tenants.index', compact(
            'tenantsData',
            'totalTenants',
            'activeTenants',
            'suspendedTenants',
            'expiringTenants',
            'planCounts',
            'totalMRR',
            'totalCrypts',
            'tenants'
        ));
    }
    
    public function create()
    {
        $plans = SubscriptionPlan::active()->ordered()->get();
        return view('super-admin.tenants.create', compact('plans'));
    }
    
    public function store(Request $request)
    {
        // ✅ CORRECCIÓN: Validación RFC flexible (12 o 13 caracteres)
        $validated = $request->validate([
            'name' => 'required|string|max:150',
            'rfc' => ['required', 'string', 'min:12', 'max:13', 'unique:tenants,rfc'],
            'subdomain' => ['required', 'string', 'max:63', 'alpha_dash', 'unique:tenants,subdomain'],
            'plan' => 'required|exists:subscription_plans,code',
            'subscription_months' => 'required|integer|min:1|max:120',
            'grace_period_years' => 'nullable|integer|min:1|max:10',
            'debt_months_to_block' => 'nullable|integer|min:1|max:12',
            'moratorium_interest_rate' => 'nullable|numeric|min:0|max:0.10',
            'reservation_days' => 'nullable|integer|min:1|max:90',
            'reservation_deposit_percent' => 'nullable|numeric|min:0|max:100',
            'maintenance_grace_days' => 'nullable|integer|min:0|max:180',
            'cemetery_name' => 'required|string|max:150',
            'cemetery_address' => 'required|string|max:255',
            'cemetery_municipality' => 'required|string|max:100',
            'cemetery_state' => 'required|string|max:50',
            'cemetery_postal_code' => 'required|string|size:5',
            'cemetery_phone' => 'nullable|string|max:20',
            'cemetery_email' => 'nullable|email|max:100',
            'legal_representative' => 'required|string|max:150',
            'legal_representative_rfc' => 'required|string|min:12|max:13',
            'admin_name' => 'required|string|max:150',
            'admin_email' => 'required|email|unique:users,email',
            'admin_password' => 'required|string|min:8',
        ]);

        $isDevelopment = app()->environment('local', 'development');

        if (!RfcValidator::isValid($validated['rfc'], !$isDevelopment)) {
            return back()->withInput()->withErrors([
                'rfc' => 'El RFC no es válido.'
            ]);
        }

        if (!RfcValidator::isValid($validated['legal_representative_rfc'], !$isDevelopment)) {
            return back()->withInput()->withErrors([
                'legal_representative_rfc' => 'El RFC del representante no es válido.'
            ]);
        }

        DB::beginTransaction();
        
        try {
            // ✅ CORRECCIÓN: Cast explícito a int para Carbon
            $subscriptionMonths = (int) $validated['subscription_months'];
            
            $tenant = Tenant::create([
                'name' => $validated['name'],
                'rfc' => $validated['rfc'],
                'subdomain' => $validated['subdomain'],
                'plan' => $validated['plan'],
                'grace_period_years' => $validated['grace_period_years'] ?? 3,
                'debt_months_to_block' => $validated['debt_months_to_block'] ?? 3,
                'moratorium_interest_rate' => $validated['moratorium_interest_rate'] ?? 0.02,
                'reservation_days' => $validated['reservation_days'] ?? 15,
                'reservation_deposit_percent' => $validated['reservation_deposit_percent'] ?? 20.00,
                'maintenance_grace_days' => $validated['maintenance_grace_days'] ?? 30,
                'is_active' => true,
                'subscription_ends_at' => now()->addMonths($subscriptionMonths),
            ]);
            
            Cemetery::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['cemetery_name'],
                'address' => $validated['cemetery_address'],
                'municipality' => $validated['cemetery_municipality'],
                'state' => $validated['cemetery_state'],
                'postal_code' => $validated['cemetery_postal_code'],
                'phone' => $validated['cemetery_phone'],
                'email' => $validated['cemetery_email'],
                'legal_representative' => $validated['legal_representative'],
                'legal_representative_rfc' => $validated['legal_representative_rfc'],
                'opening_time' => '08:00:00',
                'closing_time' => '18:00:00',
            ]);
            
            $admin = User::create([
                'tenant_id' => $tenant->id,
                'name' => $validated['admin_name'],
                'email' => $validated['admin_email'],
                'password' => Hash::make($validated['admin_password']),
                'email_verified_at' => now(),
                'is_active' => true,
            ]);
            $admin->assignRole('admin_cemetery');
            
            $plan = SubscriptionPlan::where('code', $validated['plan'])->first();
            SubscriptionHistory::create([
                'tenant_id' => $tenant->id,
                'subscription_plan_id' => $plan->id,
                'action' => 'created',
                'amount' => $plan->annual_price,
                'starts_at' => now(),
                'ends_at' => now()->addMonths($subscriptionMonths),
                'notes' => 'Suscripción inicial al crear tenant',
                'changed_by_user_id' => auth()->id(),
            ]);
            
            DB::commit();
            
            return redirect()
                ->route('super-admin.tenants.show', $tenant)
                ->with('success', "Tenant '{$tenant->name}' creado exitosamente. Admin: {$admin->email}");
                
        } catch (\Exception $e) {
            DB::rollBack();
            return back()
                ->withInput()
                ->with('error', 'Error al crear tenant: ' . $e->getMessage());
        }
    }
    
    public function show(Tenant $tenant)
    {
        $tenant->load(['cemetery', 'subscriptionPlan', 'subscriptionHistory.plan']);
        
        $stats = [
            'total_crypts' => $tenant->crypts()->count(),
            'total_users' => $tenant->users()->count(),
        ];
        
        return view('super-admin.tenants.show', compact('tenant', 'stats'));
    }
    
    public function edit(Tenant $tenant)
    {
        $tenant->load('cemetery');
        $plans = SubscriptionPlan::active()->ordered()->get();
        return view('super-admin.tenants.edit', compact('tenant', 'plans'));
    }
    
public function update(Request $request, Tenant $tenant)
{
    // ✅ CORRECCIÓN: Validación flexible (12 o 13 chars) y campos consistentes con el formulario
    $validated = $request->validate([
        'name' => 'required|string|max:150',
        'rfc' => ['required', 'string', 'min:12', 'max:13', 'unique:tenants,rfc,' . $tenant->id],
        'subdomain' => ['required', 'string', 'max:63', 'alpha_dash', 'unique:tenants,subdomain,' . $tenant->id],
        'plan' => 'required|exists:subscription_plans,code',
        'subscription_months' => 'nullable|integer|min:1|max:120',
        'grace_period_years' => 'nullable|integer|min:1|max:10',
        'debt_months_to_block' => 'nullable|integer|min:1|max:12',
        'moratorium_interest_rate' => 'nullable|numeric|min:0|max:0.10',
        'reservation_days' => 'nullable|integer|min:1|max:90',
        'reservation_deposit_percent' => 'nullable|numeric|min:0|max:100',
        'maintenance_grace_days' => 'nullable|integer|min:0|max:180',
        'cemetery_name' => 'required|string|max:150',
        'cemetery_address' => 'required|string|max:255',
        'cemetery_municipality' => 'required|string|max:100',
        'cemetery_state' => 'required|string|max:50',
        'cemetery_postal_code' => 'required|string|size:5',
        'cemetery_phone' => 'nullable|string|max:20',
        'cemetery_email' => 'nullable|email|max:100',
        'legal_representative' => 'required|string|max:150',
        'legal_representative_rfc' => 'required|string|min:12|max:13',
    ], [
        // ✅ Mensajes personalizados en español
        'name.required' => 'El nombre comercial es obligatorio.',
        'rfc.required' => 'El RFC es obligatorio.',
        'rfc.min' => 'El RFC debe tener al menos 12 caracteres.',
        'rfc.max' => 'El RFC no debe exceder 13 caracteres.',
        'subdomain.required' => 'El subdominio es obligatorio.',
        'plan.required' => 'Debes seleccionar un plan.',
        'cemetery_name.required' => 'El nombre del cementerio es obligatorio.',
        'cemetery_address.required' => 'La dirección es obligatoria.',
        'cemetery_municipality.required' => 'El municipio es obligatorio.',
        'cemetery_state.required' => 'El estado es obligatorio.',
        'cemetery_postal_code.required' => 'El código postal es obligatorio.',
        'cemetery_postal_code.size' => 'El código postal debe tener 5 dígitos.',
        'legal_representative.required' => 'El representante legal es obligatorio.',
        'legal_representative_rfc.required' => 'El RFC del representante es obligatorio.',
        'legal_representative_rfc.min' => 'El RFC del representante debe tener al menos 12 caracteres.',
        'legal_representative_rfc.max' => 'El RFC del representante no debe exceder 13 caracteres.',
    ]);

    // ✅ CORRECCIÓN: Aplicar modo relajado igual que en store()
    $isDevelopment = app()->environment('local', 'development');
    
    if (!RfcValidator::isValid($validated['rfc'], !$isDevelopment)) {
        return back()->withInput()->withErrors([
            'rfc' => 'El RFC no es válido. Debe tener 12 o 13 caracteres.'
        ]);
    }

    if (!RfcValidator::isValid($validated['legal_representative_rfc'], !$isDevelopment)) {
        return back()->withInput()->withErrors([
            'legal_representative_rfc' => 'El RFC del representante no es válido.'
        ]);
    }
    
    DB::beginTransaction();
    
    try {
        $oldPlan = $tenant->plan;
        $newPlan = $validated['plan'];
        $planChanged = $oldPlan !== $newPlan;
        
        // ✅ CORRECCIÓN: Cast explícito a int para Carbon
        $subscriptionMonths = isset($validated['subscription_months']) 
            ? (int) $validated['subscription_months'] 
            : null;
        
        $tenant->update([
            'name' => $validated['name'],
            'rfc' => $validated['rfc'],
            'subdomain' => $validated['subdomain'],
            'plan' => $newPlan,
            'grace_period_years' => $validated['grace_period_years'] ?? 3,
            'debt_months_to_block' => $validated['debt_months_to_block'] ?? 3,
            'moratorium_interest_rate' => $validated['moratorium_interest_rate'] ?? 0.02,
            'reservation_days' => $validated['reservation_days'] ?? 15,
            'reservation_deposit_percent' => $validated['reservation_deposit_percent'] ?? 20.00,
            'maintenance_grace_days' => $validated['maintenance_grace_days'] ?? 30,
            'subscription_ends_at' => $subscriptionMonths 
                ? now()->addMonths($subscriptionMonths) 
                : $tenant->subscription_ends_at,
        ]);
        
        if ($tenant->cemetery) {
            $tenant->cemetery->update([
                'name' => $validated['cemetery_name'],
                'address' => $validated['cemetery_address'],
                'municipality' => $validated['cemetery_municipality'],
                'state' => $validated['cemetery_state'],
                'postal_code' => $validated['cemetery_postal_code'],
                'phone' => $validated['cemetery_phone'],
                'email' => $validated['cemetery_email'],
                'legal_representative' => $validated['legal_representative'],
                'legal_representative_rfc' => $validated['legal_representative_rfc'],
            ]);
        }
        
        if ($planChanged) {
            $plan = SubscriptionPlan::where('code', $newPlan)->first();
            $action = $this->getPlanChangeAction($oldPlan, $newPlan);
            
            SubscriptionHistory::create([
                'tenant_id' => $tenant->id,
                'subscription_plan_id' => $plan->id,
                'action' => $action,
                'amount' => $plan->annual_price ?? 0,
                'starts_at' => now(),
                'ends_at' => $tenant->subscription_ends_at,
                'notes' => "Cambio de plan: {$oldPlan} → {$newPlan}",
                'changed_by_user_id' => auth()->id(),
            ]);
        }
        
        DB::commit();
        
        return redirect()
            ->route('super-admin.tenants.show', $tenant)
            ->with('success', "Tenant '{$tenant->name}' actualizado exitosamente.");
            
    } catch (\Exception $e) {
        DB::rollBack();
        \Log::error('Error al actualizar tenant: ' . $e->getMessage(), [
            'tenant_id' => $tenant->id,
            'exception' => $e
        ]);
        return back()
            ->withInput()
            ->with('error', 'Error al actualizar: ' . $e->getMessage());
    }
}    
    public function suspend(Tenant $tenant)
    {
        if ($tenant->is_active) {
            $tenant->update(['is_active' => false]);
            
            SubscriptionHistory::create([
                'tenant_id' => $tenant->id,
                'subscription_plan_id' => SubscriptionPlan::where('code', $tenant->plan)->first()->id,
                'action' => 'cancelled',
                'starts_at' => now(),
                'ends_at' => now(),
                'notes' => 'Tenant suspendido por SuperAdmin',
                'changed_by_user_id' => auth()->id(),
            ]);
            
            return back()->with('success', "Tenant '{$tenant->name}' suspendido.");
        }
        return back()->with('warning', 'El tenant ya está suspendido.');
    }
    
    public function activate(Tenant $tenant)
    {
        if (!$tenant->is_active) {
            $tenant->update(['is_active' => true]);
            return back()->with('success', "Tenant '{$tenant->name}' activado.");
        }
        return back()->with('warning', 'El tenant ya está activo.');
    }
    
    public function extendSubscription(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'months' => 'required|integer|min:1|max:120',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $newEndDate = $tenant->subscription_ends_at->copy()->addMonths((int) $validated['months']);
        
        $tenant->update(['subscription_ends_at' => $newEndDate]);
        
        SubscriptionHistory::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => SubscriptionPlan::where('code', $tenant->plan)->first()->id,
            'action' => 'renewed',
            'amount' => 0,
            'starts_at' => $tenant->subscription_ends_at->copy()->subMonths((int) $validated['months']),
            'ends_at' => $newEndDate,
            'notes' => $validated['notes'] ?? "Extensión por {$validated['months']} meses",
            'changed_by_user_id' => auth()->id(),
        ]);
        
        return back()->with('success', "Suscripción extendida hasta {$newEndDate->format('d/m/Y')}");
    }
    
    public function changePlan(Request $request, Tenant $tenant)
    {
        $validated = $request->validate([
            'new_plan' => 'required|exists:subscription_plans,code|different:plan',
            'notes' => 'nullable|string|max:500',
        ]);
        
        $oldPlan = $tenant->plan;
        $newPlan = $validated['new_plan'];
        
        $tenant->update(['plan' => $newPlan]);
        
        $plan = SubscriptionPlan::where('code', $newPlan)->first();
        $action = $this->getPlanChangeAction($oldPlan, $newPlan);
        
        SubscriptionHistory::create([
            'tenant_id' => $tenant->id,
            'subscription_plan_id' => $plan->id,
            'action' => $action,
            'amount' => $plan->annual_price,
            'starts_at' => now(),
            'ends_at' => $tenant->subscription_ends_at,
            'notes' => $validated['notes'] ?? "Cambio de plan: {$oldPlan} → {$newPlan}",
            'changed_by_user_id' => auth()->id(),
        ]);
        
        return back()->with('success', "Plan cambiado de '{$oldPlan}' a '{$newPlan}'");
    }
    
    public function destroy(Tenant $tenant)
    {
        $hasContracts = false;
        if (class_exists(\App\Models\Contract::class)) {
            $hasContracts = $tenant->contracts()->count() > 0;
        }
        
        if ($hasContracts) {
            return back()->with('error', 'No se puede eliminar un tenant con contratos activos.');
        }
        
        $tenant->delete();
        
        return redirect()
            ->route('super-admin.tenants.index')
            ->with('success', "Tenant '{$tenant->name}' eliminado.");
    }
    
    private function getPlanChangeAction(string $oldPlan, string $newPlan): string
    {
        $planOrder = ['basic' => 1, 'professional' => 2, 'enterprise' => 3];
        return $planOrder[$newPlan] > $planOrder[$oldPlan] ? 'upgraded' : 'downgraded';
    }
}