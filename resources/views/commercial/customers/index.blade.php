<x-app-layout>
    <x-slot name="title">Gestión de Clientes</x-slot>

    <div class="py-8">
        <!-- Header con estadísticas y acciones -->
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Encabezado -->
            <div class="md:flex md:items-center md:justify-between mb-6">
                <div class="flex-1 min-w-0">
                    <h2 class="text-2xl font-bold leading-7 text-slate-900 sm:text-3xl sm:truncate">
                        <i class="fa-solid fa-users mr-3 text-emerald-600"></i>
                        Gestión de Clientes
                    </h2>
                    <p class="mt-1 text-sm text-slate-500">
                        Administra el registro, altas, bajas y cambios de clientes
                    </p>
                </div>
                <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                    <a href="{{ route('inventory.commercial.customers.export') }}" 
                       class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        <i class="fa-solid fa-file-export mr-2"></i>
                        Exportar
                    </a>
                    <a href="{{ route('inventory.commercial.customers.create') }}" 
                       class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        <i class="fa-solid fa-plus mr-2"></i>
                        Nuevo Cliente
                    </a>
                </div>
            </div>

            <!-- Tarjetas de estadísticas -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4 mb-6">
                <!-- Total Clientes -->
                <div class="bg-white overflow-hidden rounded-xl shadow-sm border border-slate-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                    <i class="fa-solid fa-users text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-500 truncate">Total Clientes</dt>
                                    <dd class="text-2xl font-bold text-slate-900">{{ number_format($totalCustomers) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clientes Activos -->
                <div class="bg-white overflow-hidden rounded-xl shadow-sm border border-slate-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center">
                                    <i class="fa-solid fa-user-check text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-500 truncate">Clientes Activos</dt>
                                    <dd class="text-2xl font-bold text-emerald-600">{{ number_format($activeCustomers) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clientes Fallecidos -->
                <div class="bg-white overflow-hidden rounded-xl shadow-sm border border-slate-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-slate-500 to-slate-600 flex items-center justify-center">
                                    <i class="fa-solid fa-monument text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-500 truncate">Fallecidos</dt>
                                    <dd class="text-2xl font-bold text-slate-600">{{ number_format($deceasedCustomers) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clientes Inactivos -->
                <div class="bg-white overflow-hidden rounded-xl shadow-sm border border-slate-200">
                    <div class="p-5">
                        <div class="flex items-center">
                            <div class="flex-shrink-0">
                                <div class="h-12 w-12 rounded-lg bg-gradient-to-br from-amber-500 to-amber-600 flex items-center justify-center">
                                    <i class="fa-solid fa-user-slash text-white text-xl"></i>
                                </div>
                            </div>
                            <div class="ml-5 w-0 flex-1">
                                <dl>
                                    <dt class="text-sm font-medium text-slate-500 truncate">Inactivos</dt>
                                    <dd class="text-2xl font-bold text-amber-600">{{ number_format($inactiveCustomers) }}</dd>
                                </dl>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Filtros y Búsqueda -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 mb-6">
                <form method="GET" action="{{ route('inventory.commercial.customers.index') }}" class="p-6">
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                        <!-- Búsqueda -->
                        <div>
                            <label for="search" class="block text-sm font-medium text-slate-700 mb-1">Buscar</label>
                            <input type="text" 
                                   name="search" 
                                   id="search" 
                                   value="{{ request('search') }}" 
                                   placeholder="Nombre, email, teléfono..." 
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                        </div>

                        <!-- Tipo de Cliente -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-slate-700 mb-1">Tipo</label>
                            <select name="type" id="type" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                <option value="">Todos</option>
                                <option value="persona_fisica" {{ request('type') === 'persona_fisica' ? 'selected' : '' }}>Persona Física</option>
                                <option value="persona_moral" {{ request('type') === 'persona_moral' ? 'selected' : '' }}>Empresa</option>
                            </select>
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="status" class="block text-sm font-medium text-slate-700 mb-1">Estado</label>
                            <select name="status" id="status" class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                <option value="">Todos</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                                <option value="deceased" {{ request('status') === 'deceased' ? 'selected' : '' }}>Fallecidos</option>
                            </select>
                        </div>

                        <!-- Botones de acción -->
                        <div class="flex items-end space-x-2">
                            <button type="submit" 
                                    class="flex-1 inline-flex justify-center items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                <i class="fa-solid fa-filter mr-2"></i>
                                Filtrar
                            </button>
                            <a href="{{ route('inventory.commercial.customers.index') }}" 
                               class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Tabla de Clientes -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-900">Listado de Clientes</h3>
                </div>
                
                @if($customers->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Cliente</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Tipo</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Contacto</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Contratos</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Beneficiarios</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</th>
                                    <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-200">
                                @foreach($customers as $customer)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <!-- Cliente -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-10 w-10 flex-shrink-0">
                                                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-400 to-cyan-500 flex items-center justify-center text-white font-bold">
                                                        {{ strtoupper(substr($customer->name, 0, 2)) }}
                                                    </div>
                                                </div>
                                                <div class="ml-4">
                                                    <div class="text-sm font-medium text-slate-900">{{ $customer->name }}</div>
                                                    @if($customer->is_deceased)
                                                        <div class="text-xs text-slate-500">
                                                            <i class="fa-solid fa-monument mr-1"></i>
                                                            Fallecido: {{ $customer->deceased_at?->format('d/m/Y') }}
                                                        </div>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>

                                        <!-- Tipo -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $customer->type === 'persona_fisica' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                                {{ $customer->type === 'persona_fisica' ? 'Persona Física' : 'Empresa' }}
                                            </span>
                                        </td>

                                        <!-- Contacto -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900">
                                                @if($customer->email)
                                                    <i class="fa-solid fa-envelope text-slate-400 mr-1"></i>
                                                    {{ $customer->email }}
                                                @endif
                                            </div>
                                            @if($customer->phone || $customer->mobile)
                                                <div class="text-sm text-slate-500">
                                                    @if($customer->phone)
                                                        <i class="fa-solid fa-phone text-slate-400 mr-1"></i>
                                                        {{ $customer->phone }}
                                                    @endif
                                                    @if($customer->mobile)
                                                        <br><i class="fa-solid fa-mobile-screen text-slate-400 mr-1"></i>
                                                        {{ $customer->mobile }}
                                                    @endif
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Contratos -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900">
                                                <i class="fa-solid fa-file-contract text-slate-400 mr-1"></i>
                                                {{ $customer->contracts->count() }} contrato(s)
                                            </div>
                                            @php
                                                $activeContracts = $customer->contracts->where('status', 'active')->count();
                                            @endphp
                                            @if($activeContracts > 0)
                                                <div class="text-xs text-emerald-600 font-medium">
                                                    {{ $activeContracts }} activo(s)
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Beneficiarios -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="text-sm text-slate-900">
                                                <i class="fa-solid fa-user-group text-slate-400 mr-1"></i>
                                                {{ $customer->beneficiaries->count() }} beneficiario(s)
                                            </div>
                                            @php
                                                $primaryBeneficiary = $customer->beneficiaries->where('is_primary', true)->first();
                                            @endphp
                                            @if($primaryBeneficiary)
                                                <div class="text-xs text-emerald-600 font-medium">
                                                    <i class="fa-solid fa-star mr-1"></i>
                                                    {{ $primaryBeneficiary->customer->name ?? 'N/A' }}
                                                </div>
                                            @endif
                                        </td>

                                        <!-- Estado -->
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($customer->is_deceased)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                                    <i class="fa-solid fa-monument mr-1"></i>
                                                    Fallecido
                                                </span>
                                            @elseif($customer->is_active)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    <i class="fa-solid fa-circle-check mr-1"></i>
                                                    Activo
                                                </span>
                                            @else
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                                    <i class="fa-solid fa-circle-pause mr-1"></i>
                                                    Inactivo
                                                </span>
                                            @endif
                                        </td>

                                        <!-- Acciones -->
                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-2">
                                                <a href="{{ route('inventory.commercial.customers.show', $customer) }}" 
                                                   class="text-emerald-600 hover:text-emerald-900" 
                                                   title="Ver detalle">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="{{ route('inventory.commercial.customers.edit', $customer) }}" 
                                                   class="text-blue-600 hover:text-blue-900" 
                                                   title="Editar">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                @if(!$customer->is_deceased && $customer->is_active)
                                                    <button onclick="document.getElementById('deactivate-form-{{ $customer->id }}').submit()" 
                                                            class="text-amber-600 hover:text-amber-900" 
                                                            title="Desactivar">
                                                        <i class="fa-solid fa-user-slash"></i>
                                                    </button>
                                                    <form id="deactivate-form-{{ $customer->id }}" 
                                                          action="{{ route('inventory.commercial.customers.deactivate', $customer) }}" 
                                                          method="POST" 
                                                          class="hidden">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>
                                                @endif
                                                @if(!$customer->is_active)
                                                    <button onclick="document.getElementById('reactivate-form-{{ $customer->id }}').submit()" 
                                                            class="text-emerald-600 hover:text-emerald-900" 
                                                            title="Reactivar">
                                                        <i class="fa-solid fa-user-check"></i>
                                                    </button>
                                                    <form id="reactivate-form-{{ $customer->id }}" 
                                                          action="{{ route('inventory.commercial.customers.reactivate', $customer) }}" 
                                                          method="POST" 
                                                          class="hidden">
                                                        @csrf
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Paginación -->
                    <div class="px-6 py-4 border-t border-slate-200">
                        {{ $customers->links() }}
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <i class="fa-solid fa-users-slash text-6xl text-slate-300 mb-4"></i>
                        <h3 class="text-lg font-medium text-slate-900 mb-2">No se encontraron clientes</h3>
                        <p class="text-slate-500 mb-4">
                            @if(request()->hasAny(['search', 'type', 'status']))
                                Intenta ajustar los filtros de búsqueda.
                            @else
                                Comienza registrando tu primer cliente.
                            @endif
                        </p>
                        @if(!request()->hasAny(['search', 'type', 'status']))
                            <a href="{{ route('inventory.commercial.customers.create') }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700">
                                <i class="fa-solid fa-plus mr-2"></i>
                                Registrar Cliente
                            </a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
