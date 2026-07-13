<div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
    <div class="p-6">
        <h3 class="text-lg font-semibold text-gray-900 mb-4"> Gestión de Suscripción</h3>
        
        <!-- Info actual -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-500">Plan Actual</div>
                <div class="text-xl font-bold text-indigo-600">
                    {{ ucfirst($tenant->plan) }}
                </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-500">Vence el</div>
                <div class="text-xl font-bold {{ $tenant->subscription_ends_at && $tenant->subscription_ends_at->diffInDays(now()) < 30 ? 'text-red-600' : 'text-gray-900' }}">
                    {{ $tenant->subscription_ends_at ? $tenant->subscription_ends_at->format('d/m/Y') : 'N/A' }}
                </div>
            </div>
            <div class="bg-gray-50 p-4 rounded-lg">
                <div class="text-sm text-gray-500">Estado</div>
                <div class="text-xl font-bold {{ $tenant->is_active ? 'text-green-600' : 'text-red-600' }}">
                    {{ $tenant->is_active ? 'Activo' : 'Suspendido' }}
                </div>
            </div>
        </div>

        <!-- Acciones -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Extender Suscripción -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">Extender Suscripción</h4>
                <form method="POST" action="{{ route('super-admin.tenants.extend', $tenant) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm text-gray-700 mb-1">Meses a agregar</label>
                        <select name="months" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach([1, 3, 6, 12, 24, 36] as $m)
                                <option value="{{ $m }}">{{ $m }} {{ $m === 1 ? 'mes' : 'meses' }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm text-gray-700 mb-1">Notas (opcional)</label>
                        <textarea name="notes" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                        Extender
                    </button>
                </form>
            </div>

            <!-- Cambiar Plan -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">Cambiar Plan</h4>
                <form method="POST" action="{{ route('super-admin.tenants.change-plan', $tenant) }}">
                    @csrf
                    <div class="mb-3">
                        <label class="block text-sm text-gray-700 mb-1">Nuevo Plan</label>
                        <select name="new_plan" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                            @foreach($plans as $p)
                                @if($p->code !== $tenant->plan)
                                    <option value="{{ $p->code }}">{{ $p->name }} (${{ number_format($p->monthly_price, 2) }}/mes)</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="block text-sm text-gray-700 mb-1">Notas (opcional)</label>
                        <textarea name="notes" rows="2" class="w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                    </div>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                        Cambiar Plan
                    </button>
                </form>
            </div>

            <!-- Suspender/Activar -->
            <div class="border border-gray-200 rounded-lg p-4">
                <h4 class="font-semibold text-gray-900 mb-2">Estado del Tenant</h4>
                @if($tenant->is_active)
                    <form method="POST" action="{{ route('super-admin.tenants.suspend', $tenant) }}">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-red-600 text-white rounded-md hover:bg-red-700" onclick="return confirm('¿Suspender este tenant?')">
                            Suspender Tenant
                        </button>
                    </form>
                @else
                    <form method="POST" action="{{ route('super-admin.tenants.activate', $tenant) }}">
                        @csrf
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700">
                            Activar Tenant
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>