<x-app-layout>
    {{-- Título dinámico con acceso defensivo --}}
    <x-slot name="title">Tenant: {{ $tenant?->name ?? 'Sin nombre' }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('super-admin.tenants.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-arrow-left text-lg"></i>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">
                        {{ $tenant?->name ?? 'Tenant no encontrado' }}
                    </h2>
                    <p class="text-sm text-slate-500 mt-1">
                        {{ $tenant?->slug ?? 'N/A' }}.sgic.mx • Plan {{ $tenant?->subscriptionPlan?->name ?? 'Sin plan' }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('super-admin.tenants.edit', $tenant) }}" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors flex items-center">
                    <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                </a>
            </div>
        </div>
    </x-slot>

    {{-- =========================================================================
        BLOQUE DE SEGURIDAD CRÍTICA: Verificar si el tenant existe
        ========================================================================= --}}
    @if(!$tenant)
        <div class="mb-6 bg-red-50 border-l-4 border-red-600 p-6 rounded-r-lg shadow-sm">
            <div class="flex items-start">
                <i class="fa-solid fa-triangle-exclamation text-red-600 mt-0.5 mr-3 text-xl"></i>
                <div>
                    <h3 class="text-lg font-bold text-red-800">Error Crítico: Tenant No Encontrado</h3>
                    <p class="text-red-700 mt-1">No se pudo cargar la información del Tenant. El registro puede haber sido eliminado o el ID proporcionado no es válido.</p>
                    <a href="{{ route('super-admin.tenants.index') }}" class="inline-block mt-3 text-sm font-medium text-red-800 underline hover:text-red-900">
                        ← Volver al listado de tenants
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- =========================================================================
        MOSTRAR ERRORES DE VALIDACIÓN Y SESIÓN
        ========================================================================= --}}
    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 mr-3"></i>
                <div class="flex-1">
                    <h4 class="text-sm font-bold text-red-800 mb-2">Se encontraron los siguientes errores:</h4>
                    <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- Mensaje de éxito --}}
    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg shadow-sm">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 mr-3"></i>
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    {{-- Mensaje de error de sesión --}}
    @if (session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg shadow-sm">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 mr-3"></i>
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    {{-- =========================================================================
        GRID DE INFORMACIÓN PRINCIPAL
        ========================================================================= --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Información General --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-building text-emerald-600 mr-2"></i>
                Información General
            </h3>
            <div class="space-y-3">
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-sm text-slate-500">Nombre Comercial</span>
                    <span class="text-sm font-medium text-slate-800">{{ $tenant?->name ?? 'N/A' }}</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-sm text-slate-500">Subdominio/Slug</span>
                    <span class="text-sm font-medium text-indigo-600 font-mono">{{ $tenant?->slug ?? 'N/A' }}.sgic.mx</span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-sm text-slate-500">Plan Actual</span>
                    <span class="px-2 py-1 text-xs font-semibold rounded-full {{ $tenant?->subscriptionPlan?->code === 'enterprise' ? 'bg-purple-100 text-purple-800' : ($tenant?->subscriptionPlan?->code === 'professional' ? 'bg-blue-100 text-blue-800' : 'bg-slate-100 text-slate-700') }}">
                        {{ $tenant?->subscriptionPlan?->name ?? 'Sin plan asignado' }}
                    </span>
                </div>
                <div class="flex justify-between py-2 border-b border-slate-100">
                    <span class="text-sm text-slate-500">Estado</span>
                    <span class="flex items-center">
                        <span class="h-2 w-2 rounded-full mr-2 {{ $tenant?->status === 'active' ? 'bg-emerald-500' : ($tenant?->status === 'suspended' ? 'bg-red-500' : 'bg-amber-500') }}"></span>
                        <span class="text-sm font-medium {{ $tenant?->status === 'active' ? 'text-emerald-600' : ($tenant?->status === 'suspended' ? 'text-red-600' : 'text-amber-600') }}">
                            {{ match($tenant?->status) { 'active' => 'Activo', 'suspended' => 'Suspendido', default => 'Pendiente' } }}
                        </span>
                    </span>
                </div>
                <div class="flex justify-between py-2">
                    <span class="text-sm text-slate-500">Fecha de Creación</span>
                    <span class="text-sm font-medium text-slate-800">{{ $tenant?->created_at?->format('d/m/Y H:i') ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        {{-- Cementerio Físico --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-map-location-dot text-blue-600 mr-2"></i>
                Cementerio Físico
            </h3>
            @if ($tenant?->cemetery)
                <div class="space-y-3">
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-sm text-slate-500">Nombre</span>
                        <span class="text-sm font-medium text-slate-800 text-right">{{ $tenant->cemetery?->name ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-sm text-slate-500">Dirección</span>
                        <span class="text-sm font-medium text-slate-800 text-right">{{ $tenant->cemetery?->address ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-sm text-slate-500">Municipio/Estado</span>
                        <span class="text-sm font-medium text-slate-800">
                            {{ $tenant->cemetery?->municipality ?? 'N/A' }}, {{ $tenant->cemetery?->state ?? 'N/A' }}
                        </span>
                    </div>
                    <div class="flex justify-between py-2 border-b border-slate-100">
                        <span class="text-sm text-slate-500">Código Postal</span>
                        <span class="text-sm font-medium text-slate-800">{{ $tenant->cemetery?->postal_code ?? 'N/A' }}</span>
                    </div>
                    <div class="flex justify-between py-2">
                        <span class="text-sm text-slate-500">Representante Legal</span>
                        <span class="text-sm font-medium text-slate-800">{{ $tenant->cemetery?->legal_representative ?? 'N/A' }}</span>
                    </div>
                </div>
            @else
                <div class="text-center py-8">
                    <i class="fa-solid fa-map-location-dot text-4xl text-slate-300 mb-3"></i>
                    <p class="text-sm text-slate-500">Sin información de cementerio registrada</p>
                </div>
            @endif
        </div>

        {{-- Gestión de Suscripción --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-credit-card text-purple-600 mr-2"></i>
                Gestión de Suscripción
            </h3>
            <div class="space-y-4">
                {{-- Plan Actual --}}
                <div class="bg-slate-50 rounded-lg p-4">
                    <div class="text-xs text-slate-500 mb-1">Plan Actual</div>
                    <div class="text-lg font-bold text-slate-800 capitalize">
                        {{ $tenant?->subscriptionPlan?->name ?? 'Sin plan asignado' }}
                    </div>
                    @if($tenant?->subscriptionPlan?->monthly_price)
                        <div class="text-xs text-slate-500 mt-1">
                            ${{ number_format($tenant->subscriptionPlan->monthly_price, 2) }}/mes
                        </div>
                    @endif
                </div>

                {{-- Fecha de Vencimiento --}}
                <div class="bg-slate-50 rounded-lg p-4">
                    <div class="text-xs text-slate-500 mb-1">Vence el</div>
                    <div class="text-lg font-bold text-slate-800">
                        {{ $tenant?->subscription_ends_at?->format('d/m/Y') ?? 'N/A' }}
                    </div>
                    @php
                        $daysUntilExpiry = $tenant?->getDaysUntilExpiry();
                    @endphp
                    @if($daysUntilExpiry !== null && $daysUntilExpiry <= 30 && $daysUntilExpiry > 0)
                        <div class="mt-2 inline-flex items-center px-2 py-1 bg-amber-100 text-amber-800 text-xs font-semibold rounded">
                            <i class="fa-solid fa-triangle-exclamation mr-1"></i>
                            {{ $daysUntilExpiry }} días restantes
                        </div>
                    @endif
                </div>

                {{-- Estado --}}
                <div class="bg-slate-50 rounded-lg p-4">
                    <div class="text-xs text-slate-500 mb-1">Estado</div>
                    <div class="flex items-center">
                        <span class="h-2 w-2 rounded-full mr-2 {{ $tenant?->status === 'active' ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                        <span class="text-lg font-bold {{ $tenant?->status === 'active' ? 'text-emerald-600' : 'text-red-600' }}">
                            {{ $tenant?->status === 'active' ? 'Activo' : 'Inactivo' }}
                        </span>
                    </div>
                </div>

                {{-- Acciones de Suscripción --}}
                <div class="space-y-2 pt-2">
                    <form method="POST" action="{{ route('super-admin.tenants.extend', $tenant) }}" class="space-y-2">
                        @csrf
                        <div class="flex gap-2">
                            <input type="number" name="months" value="1" min="1" max="120" 
                                class="w-20 px-3 py-2 border border-slate-300 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                            <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fa-solid fa-plus mr-1"></i> Extender
                            </button>
                        </div>
                    </form>

                    @if ($tenant?->status === 'active')
                        <form method="POST" action="{{ route('super-admin.tenants.suspend', $tenant) }}" 
                            onsubmit="return confirm('¿Estás seguro de suspender este tenant? Esta acción desactivará el acceso al sistema.')">
                            @csrf
                            <button type="submit" class="w-full bg-amber-600 hover:bg-amber-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fa-solid fa-pause mr-1"></i> Suspender Tenant
                            </button>
                        </form>
                    @else
                        <form method="POST" action="{{ route('super-admin.tenants.activate', $tenant) }}">
                            @csrf
                            <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg text-sm font-medium transition-colors">
                                <i class="fa-solid fa-play mr-1"></i> Activar Tenant
                            </button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- =========================================================================
        USUARIOS DEL TENANT
        ========================================================================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden mb-6">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800 flex items-center">
                <i class="fa-solid fa-users text-indigo-600 mr-2"></i>
                Usuarios del Tenant
                <span class="ml-auto text-sm font-normal text-slate-500">
                    {{ $tenant?->users?->count() ?? 0 }} usuario(s) registrado(s)
                </span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Nombre</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Rol</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse ($tenant?->users ?? [] as $user)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="h-9 w-9 rounded-full bg-gradient-to-br from-emerald-500 to-cyan-600 flex items-center justify-center text-white font-bold text-sm mr-3">
                                        {{ strtoupper(substr($user->name ?? 'U', 0, 2)) }}
                                    </div>
                                    <div class="text-sm font-medium text-slate-900">{{ $user->name ?? 'Sin nombre' }}</div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $user->email ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full bg-indigo-100 text-indigo-800">
                                    {{ $user->roles?->first()?->name ?? 'Sin rol' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="flex items-center">
                                    <span class="h-2 w-2 rounded-full mr-2 {{ $user->is_active ? 'bg-emerald-500' : 'bg-slate-400' }}"></span>
                                    <span class="text-sm {{ $user->is_active ? 'text-emerald-600' : 'text-slate-500' }}">
                                        {{ $user->is_active ? 'Activo' : 'Inactivo' }}
                                    </span>
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-8 text-center text-slate-500">
                                <i class="fa-solid fa-users text-3xl text-slate-300 mb-2"></i>
                                <p class="text-sm">No hay usuarios registrados para este tenant</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- =========================================================================
        HISTORIAL DE SUSCRIPCIONES
        ========================================================================= --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="p-6 border-b border-slate-100">
            <h3 class="text-lg font-bold text-slate-800 flex items-center">
                <i class="fa-solid fa-clock-rotate-left text-amber-600 mr-2"></i>
                Historial de Suscripciones
                <span class="ml-auto text-sm font-normal text-slate-500">
                    {{ $tenant?->subscriptionHistory?->count() ?? 0 }} registro(s)
                </span>
            </h3>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fecha</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Acción</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Inicio</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Fin</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Monto</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse ($tenant?->subscriptionHistory ?? [] as $history)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $history->created_at?->format('d/m/Y H:i') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="px-2 py-1 text-xs font-semibold rounded-full 
                                    {{ $history->action === 'created' ? 'bg-emerald-100 text-emerald-800' : 
                                       ($history->action === 'cancelled' ? 'bg-red-100 text-red-800' : 
                                       ($history->action === 'plan_changed' ? 'bg-blue-100 text-blue-800' : 
                                       ($history->action === 'extended' ? 'bg-purple-100 text-purple-800' : 'bg-slate-100 text-slate-700'))) }}">
                                    {{ ucfirst($history->action ?? 'desconocida') }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $history->plan?->name ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $history->starts_at?->format('d/m/Y') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                {{ $history->ends_at?->format('d/m/Y') ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">
                                ${{ number_format($history->amount ?? 0, 2) }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-slate-500">
                                <i class="fa-solid fa-clock-rotate-left text-3xl text-slate-300 mb-2"></i>
                                <p class="text-sm">Sin historial de suscripciones registrado</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>