<x-app-layout>
    <x-slot name="title">Detalle del Cliente</x-slot>

    @php
        $allCustomers = \App\Models\Customer::where('tenant_id', Auth::user()->tenant_id)
            ->where('id', '!=', $customer->id)
            ->orderBy('name')
            ->get();
    @endphp

    <!-- Notificaciones de sesión -->
    @if (session('success'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-lg shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-check-circle text-emerald-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                    </div>
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-auto text-emerald-500 hover:text-emerald-700">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-exclamation-circle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                    </div>
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-auto text-red-500 hover:text-red-700">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    @if ($errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-6">
            <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-lg shadow-sm">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="fa-solid fa-exclamation-triangle text-red-500"></i>
                    </div>
                    <div class="ml-3">
                        <h4 class="text-sm font-semibold text-red-800 mb-2">Errores de validación:</h4>
                        <ul class="list-disc list-inside text-sm text-red-700 space-y-1">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <button type="button" onclick="this.parentElement.parentElement.remove()" class="ml-auto text-red-500 hover:text-red-700">
                        <i class="fa-solid fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    @endif

    <div class="py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Encabezado -->
            <div class="mb-6">
                <a href="{{ route('commercial.customers.index') }}" 
                   class="inline-flex items-center text-sm text-slate-500 hover:text-slate-700 mb-3">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Volver al listado
                </a>
                <div class="md:flex md:items-center md:justify-between">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-2xl font-bold leading-7 text-slate-900 sm:text-3xl">
                            <i class="fa-solid fa-user-circle mr-3 text-emerald-600"></i>
                            {{ $customer->name }}
                        </h2>
                        <p class="mt-1 text-sm text-slate-500">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $customer->type === 'persona_fisica' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                {{ $customer->type === 'persona_fisica' ? 'Persona Física' : 'Empresa' }}
                            </span>
                            @if($customer->is_deceased)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                    <i class="fa-solid fa-monument mr-1"></i>
                                    Fallecido
                                </span>
                            @elseif($customer->is_active)
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-100 text-emerald-800">
                                    <i class="fa-solid fa-circle-check mr-1"></i>
                                    Activo
                                </span>
                            @else
                                <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                    <i class="fa-solid fa-circle-pause mr-1"></i>
                                    Inactivo
                                </span>
                            @endif
                        </p>
                    </div>
                    <div class="mt-4 flex md:mt-0 md:ml-4 space-x-3">
                        @if(!$customer->is_deceased)
                            <a href="{{ route('commercial.customers.edit', $customer) }}" 
                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                <i class="fa-solid fa-pen-to-square mr-2"></i>
                                Editar
                            </a>
                        @endif
                        @if(!$customer->is_deceased && $customer->is_active)
                            <button onclick="confirmDeactivate()" 
                                    class="inline-flex items-center px-4 py-2 border border-amber-300 rounded-lg shadow-sm text-sm font-medium text-amber-700 bg-amber-50 hover:bg-amber-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-amber-500">
                                <i class="fa-solid fa-user-slash mr-2"></i>
                                Desactivar
                            </button>
                            <form id="deactivate-form" 
                                  action="{{ route('commercial.customers.deactivate', $customer) }}" 
                                  method="POST" 
                                  class="hidden">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endif
                        @if(!$customer->is_active)
                            <button onclick="document.getElementById('reactivate-form').submit()" 
                                    class="inline-flex items-center px-4 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                <i class="fa-solid fa-user-check mr-2"></i>
                                Reactivar
                            </button>
                            <form id="reactivate-form" 
                                  action="{{ route('commercial.customers.reactivate', $customer) }}" 
                                  method="POST" 
                                  class="hidden">
                                @csrf
                            </form>
                        @endif
                        @if(!$customer->is_deceased)
                            <button onclick="document.getElementById('mark-deceased-modal').classList.remove('hidden')" 
                                    class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                                <i class="fa-solid fa-monument mr-2"></i>
                                Registrar Fallecimiento
                            </button>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Estadísticas rápidas -->
            <div class="grid grid-cols-1 gap-5 sm:grid-cols-3 mb-6">
                <div class="bg-white overflow-hidden rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-blue-500 to-blue-600 flex items-center justify-center">
                                <i class="fa-solid fa-file-contract text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-slate-500 truncate">Total Contratos</dt>
                            <dd class="text-2xl font-bold text-slate-900">{{ $totalContracts }}</dd>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-emerald-500 to-emerald-600 flex items-center justify-center">
                                <i class="fa-solid fa-check-circle text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-slate-500 truncate">Contratos Activos</dt>
                            <dd class="text-2xl font-bold text-emerald-600">{{ $activeContracts }}</dd>
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden rounded-xl shadow-sm border border-slate-200 p-5">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <div class="h-10 w-10 rounded-lg bg-gradient-to-br from-cyan-500 to-cyan-600 flex items-center justify-center">
                                <i class="fa-solid fa-dollar-sign text-white"></i>
                            </div>
                        </div>
                        <div class="ml-4">
                            <dt class="text-sm font-medium text-slate-500 truncate">Total Pagado</dt>
                            <dd class="text-2xl font-bold text-cyan-600">${{ number_format($totalPaid, 2) }}</dd>
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Información Principal -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Datos Fiscales -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                            <h3 class="text-lg font-semibold text-slate-900">
                                <i class="fa-solid fa-id-card mr-2 text-emerald-600"></i>
                                Datos Fiscales
                            </h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">RFC</label>
                                <p class="text-sm font-medium text-slate-900">
                                    @if($rfc)
                                        {{ $rfc }}
                                        <i class="fa-solid fa-lock text-slate-400 ml-2" title="Dato encriptado"></i>
                                    @else
                                        <span class="text-slate-400">No disponible</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">CURP</label>
                                <p class="text-sm font-medium text-slate-900">
                                    @if($curp)
                                        {{ $curp }}
                                        <i class="fa-solid fa-lock text-slate-400 ml-2" title="Dato encriptado"></i>
                                    @else
                                        <span class="text-slate-400">No disponible</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Información de Contacto -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                            <h3 class="text-lg font-semibold text-slate-900">
                                <i class="fa-solid fa-address-book mr-2 text-emerald-600"></i>
                                Información de Contacto
                            </h3>
                        </div>
                        <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Correo Electrónico</label>
                                <p class="text-sm text-slate-900">
                                    @if($customer->email)
                                        <i class="fa-solid fa-envelope text-slate-400 mr-2"></i>
                                        {{ $customer->email }}
                                    @else
                                        <span class="text-slate-400">No disponible</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Teléfono</label>
                                <p class="text-sm text-slate-900">
                                    @if($customer->phone)
                                        <i class="fa-solid fa-phone text-slate-400 mr-2"></i>
                                        {{ $customer->phone }}
                                    @else
                                        <span class="text-slate-400">No disponible</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Celular</label>
                                <p class="text-sm text-slate-900">
                                    @if($customer->mobile)
                                        <i class="fa-solid fa-mobile-screen text-slate-400 mr-2"></i>
                                        {{ $customer->mobile }}
                                    @else
                                        <span class="text-slate-400">No disponible</span>
                                    @endif
                                </p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-slate-500 uppercase tracking-wider mb-1">Dirección</label>
                                <p class="text-sm text-slate-900">
                                    @if($customer->address)
                                        <i class="fa-solid fa-location-dot text-slate-400 mr-2"></i>
                                        {{ $customer->address }}
                                    @else
                                        <span class="text-slate-400">No disponible</span>
                                    @endif
                                </p>
                            </div>
                        </div>
                    </div>

                    <!-- Contratos -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                            <h3 class="text-lg font-semibold text-slate-900">
                                <i class="fa-solid fa-file-contract mr-2 text-emerald-600"></i>
                                Contratos Asociados
                            </h3>
                        </div>
                        <div class="p-6">
                            @if($customer->contracts->count() > 0)
                                <div class="space-y-4">
                                    @foreach($customer->contracts as $contract)
                                        <div class="border border-slate-200 rounded-lg p-4 hover:bg-slate-50 transition-colors">
                                            <div class="flex items-center justify-between">
                                                <div>
                                                    <p class="text-sm font-semibold text-slate-900">
                                                        {{ $contract->contract_number }}
                                                    </p>
                                                    <p class="text-xs text-slate-500 mt-1">
                                                        <i class="fa-solid fa-layer-group mr-1"></i>
                                                        Cripta: {{ $contract->crypt->code ?? 'N/A' }}
                                                        @if($contract->crypt->level)
                                                            - Nivel {{ $contract->crypt->level->name ?? '' }}
                                                        @endif
                                                    </p>
                                                    <p class="text-xs text-slate-500 mt-1">
                                                        <i class="fa-solid fa-calendar mr-1"></i>
                                                        Inicio: {{ $contract->start_date?->format('d/m/Y') }}
                                                        @if($contract->end_date)
                                                            | Vence: {{ $contract->end_date->format('d/m/Y') }}
                                                        @endif
                                                    </p>
                                                </div>
                                                <div class="text-right">
                                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                        @if($contract->status === 'active') bg-emerald-100 text-emerald-800
                                                        @elseif($contract->status === 'draft') bg-slate-100 text-slate-800
                                                        @elseif($contract->status === 'expired') bg-red-100 text-red-800
                                                        @else bg-amber-100 text-amber-800
                                                        @endif">
                                                        {{ ucfirst($contract->status) }}
                                                    </span>
                                                    <p class="text-sm font-bold text-slate-900 mt-2">
                                                        ${{ number_format($contract->price, 2) }}
                                                    </p>
                                                    <a href="{{ route('commercial.contracts.show', $contract) }}" 
                                                       class="text-xs text-emerald-600 hover:text-emerald-900 mt-2 inline-block">
                                                        Ver detalle <i class="fa-solid fa-arrow-right ml-1"></i>
                                                    </a>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-slate-500 text-sm text-center py-4">
                                    <i class="fa-solid fa-file-contract text-3xl text-slate-300 mb-2 block"></i>
                                    No tiene contratos registrados
                                </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Beneficiarios -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50 flex justify-between items-center">
                            <h3 class="text-lg font-semibold text-slate-900">
                                <i class="fa-solid fa-user-group mr-2 text-emerald-600"></i>
                                Beneficiarios
                            </h3>
                            <button type="button" 
                                    onclick="document.getElementById('add-beneficiary-modal').classList.remove('hidden')"
                                    class="inline-flex items-center px-3 py-1.5 text-sm font-medium text-emerald-700 bg-emerald-50 rounded-lg hover:bg-emerald-100 focus:outline-none focus:ring-2 focus:ring-emerald-500">
                                <i class="fa-solid fa-plus mr-1.5"></i>
                                Agregar
                            </button>
                        </div>
                        <div class="p-6">
                            @if($customer->beneficiaries->count() > 0)
                                <ul class="space-y-3">
                                    @foreach($customer->beneficiaries as $beneficiary)
                                        <li class="flex items-start justify-between group">
                                            <div class="flex items-start">
                                                <div class="flex-shrink-0">
                                                    @if($beneficiary->is_primary)
                                                        <i class="fa-solid fa-star text-amber-500 mt-0.5"></i>
                                                    @else
                                                        <i class="fa-solid fa-user text-slate-400 mt-0.5"></i>
                                                    @endif
                                                </div>
                                                <div class="ml-3">
                                                    <p class="text-sm font-medium text-slate-900">
                                                        {{ $beneficiary->beneficiaryCustomer->name ?? 'N/A' }}
                                                    </p>
                                                    <p class="text-xs text-slate-500">
                                                        {{ $beneficiary->relationship }}
                                                        @if($beneficiary->is_primary)
                                                            <span class="ml-1 text-amber-600 font-medium">(Principal)</span>
                                                        @endif
                                                    </p>
                                                </div>
                                            </div>
                                            <form id="remove-beneficiary-form-{{ $beneficiary->id }}" 
                                                  action="{{ route('commercial.customers.beneficiaries.remove', [$customer, $beneficiary]) }}" 
                                                  method="POST" 
                                                  class="hidden">
                                                @csrf
                                                @method('DELETE')
                                            </form>
                                            <button type="button" 
                                                    onclick="confirmRemoveBeneficiary({{ $beneficiary->id }})"
                                                    class="opacity-0 group-hover:opacity-100 transition-opacity p-1.5 text-red-600 hover:bg-red-50 rounded-lg"
                                                    title="Eliminar beneficiario">
                                                <i class="fa-solid fa-trash-alt text-sm"></i>
                                            </button>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-slate-500 text-sm text-center py-4">
                                    <i class="fa-solid fa-user-group text-3xl text-slate-300 mb-2 block"></i>
                                    Sin beneficiarios
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Herederos -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                            <h3 class="text-lg font-semibold text-slate-900">
                                <i class="fa-solid fa-users mr-2 text-emerald-600"></i>
                                Herederos
                            </h3>
                        </div>
                        <div class="p-6">
                            @if($customer->heirs->count() > 0)
                                <ul class="space-y-3">
                                    @foreach($customer->heirs as $heir)
                                        <li class="flex items-start">
                                            <div class="flex-shrink-0">
                                                <i class="fa-solid fa-user text-slate-400 mt-0.5"></i>
                                            </div>
                                            <div class="ml-3">
                                                <p class="text-sm font-medium text-slate-900">
                                                    {{ $heir->customer->name ?? 'N/A' }}
                                                </p>
                                                <p class="text-xs text-slate-500">
                                                    {{ $heir->inheritance_percent }}% herencia
                                                </p>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            @else
                                <p class="text-slate-500 text-sm text-center py-4">
                                    <i class="fa-solid fa-users text-3xl text-slate-300 mb-2 block"></i>
                                    Sin herederos
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Documentación -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                        <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                            <h3 class="text-lg font-semibold text-slate-900">
                                <i class="fa-solid fa-folder-open mr-2 text-emerald-600"></i>
                                Documentación
                            </h3>
                        </div>
                        <div class="p-6 space-y-3">
                            @if($customer->ine_url)
                                <a href="{{ $customer->ine_url }}" target="_blank" 
                                   class="flex items-center text-sm text-emerald-600 hover:text-emerald-900">
                                    <i class="fa-solid fa-id-card mr-2"></i>
                                    Ver INE
                                </a>
                            @endif
                            @if($customer->proof_of_address_url)
                                <a href="{{ $customer->proof_of_address_url }}" target="_blank" 
                                   class="flex items-center text-sm text-emerald-600 hover:text-emerald-900">
                                    <i class="fa-solid fa-file-invoice mr-2"></i>
                                    Ver Comprobante de Domicilio
                                </a>
                            @endif
                            @if($customer->death_certificate_url)
                                <a href="{{ $customer->death_certificate_url }}" target="_blank" 
                                   class="flex items-center text-sm text-emerald-600 hover:text-emerald-900">
                                    <i class="fa-solid fa-file-medical mr-2"></i>
                                    Ver Acta de Defunción
                                </a>
                            @endif
                            @if($customer->heir_declaration_url)
                                <a href="{{ $customer->heir_declaration_url }}" target="_blank" 
                                   class="flex items-center text-sm text-emerald-600 hover:text-emerald-900">
                                    <i class="fa-solid fa-scroll mr-2"></i>
                                    Ver Declaratoria de Herederos
                                </a>
                            @endif
                            @if(!$customer->ine_url && !$customer->proof_of_address_url && !$customer->death_certificate_url && !$customer->heir_declaration_url)
                                <p class="text-slate-500 text-sm text-center py-4">
                                    <i class="fa-solid fa-folder-open text-3xl text-slate-300 mb-2 block"></i>
                                    Sin documentación
                                </p>
                            @endif
                        </div>
                    </div>

                    <!-- Notas -->
                    @if($customer->notes)
                        <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                            <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                                <h3 class="text-lg font-semibold text-slate-900">
                                    <i class="fa-solid fa-sticky-note mr-2 text-emerald-600"></i>
                                    Notas
                                </h3>
                            </div>
                            <div class="p-6">
                                <p class="text-sm text-slate-700 whitespace-pre-line">{{ $customer->notes }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para registrar fallecimiento -->
    <div id="mark-deceased-modal" 
         class="hidden fixed inset-0 bg-slate-900/50 z-50 overflow-y-auto" 
         aria-labelledby="modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('commercial.customers.mark-as-deceased', $customer) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-slate-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fa-solid fa-monument text-slate-500"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-slate-900" id="modal-title">
                                    Registrar Fallecimiento
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="deceased_at" class="block text-sm font-medium text-slate-700 mb-1">
                                            Fecha de Fallecimiento <span class="text-red-500">*</span>
                                        </label>
                                        <input type="date" 
                                               name="deceased_at" 
                                               id="deceased_at" 
                                               required 
                                               max="{{ date('Y-m-d') }}"
                                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    </div>
                                    <div>
                                        <label for="death_certificate_url" class="block text-sm font-medium text-slate-700 mb-1">
                                            URL del Acta de Defunción <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               name="death_certificate_url" 
                                               id="death_certificate_url" 
                                               required 
                                               maxlength="500"
                                               placeholder="https://..."
                                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    </div>
                                    <div>
                                        <label for="heir_declaration_url" class="block text-sm font-medium text-slate-700 mb-1">
                                            URL de Declaratoria de Herederos <span class="text-slate-400">(Opcional)</span>
                                        </label>
                                        <input type="text" 
                                               name="heir_declaration_url" 
                                               id="heir_declaration_url" 
                                               maxlength="500"
                                               placeholder="https://..."
                                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Registrar
                        </button>
                        <button type="button" 
                                onclick="document.getElementById('mark-deceased-modal').classList.add('hidden')"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para agregar beneficiario -->
    <div id="add-beneficiary-modal" 
         class="hidden fixed inset-0 bg-slate-900/50 z-50 overflow-y-auto" 
         aria-labelledby="beneficiary-modal-title" 
         role="dialog" 
         aria-modal="true">
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="inline-block align-bottom bg-white rounded-xl text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <form action="{{ route('commercial.customers.beneficiaries.add', $customer) }}" method="POST">
                    @csrf
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-emerald-100 sm:mx-0 sm:h-10 sm:w-10">
                                <i class="fa-solid fa-user-plus text-emerald-600"></i>
                            </div>
                            <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-slate-900" id="beneficiary-modal-title">
                                    Agregar Beneficiario
                                </h3>
                                <div class="mt-4 space-y-4">
                                    <div>
                                        <label for="beneficiary_customer_id" class="block text-sm font-medium text-slate-700 mb-1">
                                            Cliente <span class="text-red-500">*</span>
                                        </label>
                                        <select name="beneficiary_customer_id" 
                                                id="beneficiary_customer_id" 
                                                required
                                                class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                            <option value="">Seleccionar cliente...</option>
                                            @foreach($allCustomers as $c)
                                                <option value="{{ $c->id }}">{{ $c->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label for="relationship" class="block text-sm font-medium text-slate-700 mb-1">
                                            Parentesco <span class="text-red-500">*</span>
                                        </label>
                                        <input type="text" 
                                               name="relationship" 
                                               id="relationship" 
                                               required 
                                               maxlength="100"
                                               placeholder="Ej: Esposo, Hijo, Padre..."
                                               class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm">
                                    </div>
                                    <div class="flex items-center">
                                        <input type="checkbox" 
                                               name="is_primary" 
                                               id="is_primary" 
                                               value="1"
                                               class="h-4 w-4 text-emerald-600 focus:ring-emerald-500 border-slate-300 rounded">
                                        <label for="is_primary" class="ml-2 block text-sm text-slate-700">
                                            Marcar como beneficiario principal
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-slate-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button type="submit" 
                                class="w-full inline-flex justify-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:ml-3 sm:w-auto sm:text-sm">
                            Agregar
                        </button>
                        <button type="button" 
                                onclick="document.getElementById('add-beneficiary-modal').classList.add('hidden')"
                                class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmDeactivate() {
            if (confirm('¿Está seguro de que desea desactivar este cliente? Esta acción no se puede deshacer fácilmente.')) {
                document.getElementById('deactivate-form').submit();
            }
        }
        
        function confirmRemoveBeneficiary(beneficiaryId) {
            if (confirm('¿Está seguro de que desea eliminar este beneficiario?')) {
                document.getElementById('remove-beneficiary-form-' + beneficiaryId).submit();
            }
        }
    </script>
</x-app-layout>
