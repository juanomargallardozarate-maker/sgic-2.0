<x-app-layout>
    <x-slot name="title">Ficha de Cripta: {{ $crypt->code }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('inventory.crypts.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-arrow-left text-lg"></i>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Ficha de Cripta</h2>
                    <p class="text-sm text-slate-500 mt-1">
                        {{ $crypt->level->block->section->code }} / {{ $crypt->level->block->code }} / {{ $crypt->level->code }} / {{ $crypt->code }}
                    </p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('inventory.crypts.edit', $crypt) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-pen-to-square mr-2"></i> Editar
                </a>
                <a href="{{ route('inventory.crypts.map') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-map mr-2"></i> Ver Mapa
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 mr-3"></i>
                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 mr-3"></i>
                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
            </div>
        </div>
    @endif

    <div class="max-w-7xl mx-auto space-y-6" x-data="{ activeTab: 'info' }">
        
        {{-- ============================================ --}}
        {{-- HEADER CARD: Resumen Principal --}}
        {{-- ============================================ --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-4">
                    <div 
                        class="h-16 w-16 rounded-xl flex items-center justify-center"
                        style="background-color: {{ $crypt->cryptStatus->color }}15; border: 2px solid {{ $crypt->cryptStatus->color }};"
                    >
                        <i class="fa-solid fa-layer-group text-2xl" style="color: {{ $crypt->cryptStatus->color }};"></i>
                    </div>
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">{{ $crypt->full_code }}</h1>
                        <div class="flex items-center space-x-3 mt-1">
                            <span 
                                class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium"
                                style="background-color: {{ $crypt->cryptStatus->color }}20; color: {{ $crypt->cryptStatus->color }};"
                            >
                                <span class="h-2 w-2 rounded-full mr-2" style="background-color: {{ $crypt->cryptStatus->color }};"></span>
                                {{ $crypt->cryptStatus->name }}
                            </span>
                            <span class="text-sm text-slate-500">{{ $crypt->cryptType->name }}</span>
                            @if($crypt->is_blocked)
                                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                    <i class="fa-solid fa-lock mr-1.5"></i> Bloqueada
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="text-right">
                    <div class="text-xs text-slate-500 font-medium">PRECIO DE VENTA</div>
                    <div class="text-2xl font-bold text-emerald-600">${{ number_format($crypt->price, 2) }}</div>
                </div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- KPI CARDS: Estadísticas Rápidas --}}
        {{-- ============================================ --}}
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-500 font-medium">CAPACIDAD</span>
                    <i class="fa-solid fa-users text-slate-400"></i>
                </div>
                <div class="text-xl font-bold text-slate-800">{{ $crypt->current_occupancy }}/{{ $crypt->capacity }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ $crypt->available_capacity }} disponibles</div>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-500 font-medium">CONTRATOS</span>
                    <i class="fa-solid fa-file-contract text-slate-400"></i>
                </div>
                <div class="text-xl font-bold text-slate-800">{{ $totalContracts }}</div>
                <div class="text-xs text-emerald-600 mt-1">{{ $activeContract ? '1 activo' : 'Sin activos' }}</div>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-500 font-medium">ÓRDENES</span>
                    <i class="fa-solid fa-clipboard-list text-slate-400"></i>
                </div>
                <div class="text-xl font-bold text-slate-800">{{ $totalWorkOrders }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ $completedWorkOrders }} completadas</div>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-500 font-medium">ADEUDOS</span>
                    <i class="fa-solid fa-file-invoice-dollar text-slate-400"></i>
                </div>
                <div class="text-xl font-bold text-slate-800">{{ $totalDebts }}</div>
                <div class="text-xs {{ $pendingDebts > 0 ? 'text-red-600' : 'text-emerald-600' }} mt-1">{{ $pendingDebts }} pendientes</div>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-500 font-medium">DOCUMENTOS</span>
                    <i class="fa-solid fa-folder text-slate-400"></i>
                </div>
                <div class="text-xl font-bold text-slate-800">{{ $totalDocuments }}</div>
                <div class="text-xs text-slate-500 mt-1">Archivos adjuntos</div>
            </div>
            
            <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-500 font-medium">CREADA</span>
                    <i class="fa-solid fa-calendar text-slate-400"></i>
                </div>
                <div class="text-sm font-bold text-slate-800">{{ $crypt->created_at->format('d/m/Y') }}</div>
                <div class="text-xs text-slate-500 mt-1">{{ $crypt->created_at->diffForHumans() }}</div>
            </div>
        </div>

        {{-- ============================================ --}}
        {{-- TABS DE NAVEGACIÓN --}}
        {{-- ============================================ --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="border-b border-slate-200">
                <nav class="flex space-x-1 px-6" aria-label="Tabs">
                    <button 
                        @click="activeTab = 'info'"
                        :class="activeTab === 'info' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="py-4 px-4 border-b-2 font-medium text-sm transition-colors flex items-center"
                    >
                        <i class="fa-solid fa-circle-info mr-2"></i>
                        Información General
                    </button>
                    <button 
                        @click="activeTab = 'timeline'"
                        :class="activeTab === 'timeline' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="py-4 px-4 border-b-2 font-medium text-sm transition-colors flex items-center"
                    >
                        <i class="fa-solid fa-clock-rotate-left mr-2"></i>
                        Timeline
                    </button>
                    <button 
                        @click="activeTab = 'contracts'"
                        :class="activeTab === 'contracts' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="py-4 px-4 border-b-2 font-medium text-sm transition-colors flex items-center"
                    >
                        <i class="fa-solid fa-file-contract mr-2"></i>
                        Contratos
                        @if($totalContracts > 0)
                            <span class="ml-2 bg-slate-100 text-slate-600 text-xs px-2 py-0.5 rounded-full">{{ $totalContracts }}</span>
                        @endif
                    </button>
                    <button 
                        @click="activeTab = 'documents'"
                        :class="activeTab === 'documents' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="py-4 px-4 border-b-2 font-medium text-sm transition-colors flex items-center"
                    >
                        <i class="fa-solid fa-folder-open mr-2"></i>
                        Documentos
                    </button>
                    <button 
                        @click="activeTab = 'account'"
                        :class="activeTab === 'account' ? 'border-emerald-500 text-emerald-600' : 'border-transparent text-slate-500 hover:text-slate-700 hover:border-slate-300'"
                        class="py-4 px-4 border-b-2 font-medium text-sm transition-colors flex items-center"
                    >
                        <i class="fa-solid fa-file-invoice-dollar mr-2"></i>
                        Estado de Cuenta
                    </button>
                </nav>
            </div>

            <div class="p-6">
                
                {{-- ============================================ --}}
                {{-- TAB 1: INFORMACIÓN GENERAL --}}
                {{-- ============================================ --}}
                <div x-show="activeTab === 'info'" class="space-y-6">
                    
                    {{-- Grid de Datos Físicos --}}
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                            <i class="fa-solid fa-cube text-indigo-600 mr-2"></i>
                            Datos Físicos
                        </h3>
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="bg-slate-50 rounded-lg p-4">
                                <div class="text-xs text-slate-500 font-medium mb-1">CÓDIGO</div>
                                <div class="text-sm font-bold text-slate-800 font-mono">{{ $crypt->code }}</div>
                            </div>
                            <div class="bg-slate-50 rounded-lg p-4">
                                <div class="text-xs text-slate-500 font-medium mb-1">TIPO</div>
                                <div class="text-sm font-bold text-slate-800">{{ $crypt->cryptType->name }}</div>
                            </div>
                            <div class="bg-slate-50 rounded-lg p-4">
                                <div class="text-xs text-slate-500 font-medium mb-1">CAPACIDAD MÁXIMA</div>
                                <div class="text-sm font-bold text-slate-800">{{ $crypt->capacity }} espacios</div>
                            </div>
                            <div class="bg-slate-50 rounded-lg p-4">
                                <div class="text-xs text-slate-500 font-medium mb-1">OCUPACIÓN ACTUAL</div>
                                <div class="text-sm font-bold text-slate-800">{{ $crypt->current_occupancy }} / {{ $crypt->capacity }}</div>
                                <div class="mt-2 h-2 bg-slate-200 rounded-full overflow-hidden">
                                    <div 
                                        class="h-full bg-emerald-500 transition-all"
                                        style="width: {{ ($crypt->current_occupancy / $crypt->capacity * 100) }}%;"
                                    ></div>
                                </div>
                            </div>
                            @if($crypt->dimensions)
                                <div class="bg-slate-50 rounded-lg p-4">
                                    <div class="text-xs text-slate-500 font-medium mb-1">DIMENSIONES</div>
                                    <div class="text-sm font-bold text-slate-800">{{ $crypt->dimensions }}</div>
                                </div>
                            @endif
                            @if($crypt->door_type)
                                <div class="bg-slate-50 rounded-lg p-4">
                                    <div class="text-xs text-slate-500 font-medium mb-1">TIPO DE PUERTA</div>
                                    <div class="text-sm font-bold text-slate-800 capitalize">{{ $crypt->door_type }}</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Ubicación Jerárquica --}}
                    <div>
                        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                            <i class="fa-solid fa-map-location-dot text-emerald-600 mr-2"></i>
                            Ubicación Jerárquica
                        </h3>
                        <div class="bg-gradient-to-r from-emerald-50 to-cyan-50 rounded-lg p-6 border border-emerald-100">
                            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                                <div class="flex items-center space-x-3">
                                    <div class="h-10 w-10 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center">
                                        <i class="fa-solid fa-map"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500">Sección</div>
                                        <div class="text-sm font-bold text-slate-800">{{ $crypt->level->block->section->code }}</div>
                                        <div class="text-xs text-slate-600">{{ $crypt->level->block->section->name }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="h-10 w-10 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center">
                                        <i class="fa-solid fa-cubes"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500">Bloque</div>
                                        <div class="text-sm font-bold text-slate-800">{{ $crypt->level->block->code }}</div>
                                        <div class="text-xs text-slate-600">{{ $crypt->level->block->name }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="h-10 w-10 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center">
                                        <i class="fa-solid fa-layer-group"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500">Nivel</div>
                                        <div class="text-sm font-bold text-slate-800">{{ $crypt->level->code }}</div>
                                        <div class="text-xs text-slate-600">{{ $crypt->level->name }}</div>
                                    </div>
                                </div>
                                <div class="flex items-center space-x-3">
                                    <div class="h-10 w-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                                        <i class="fa-solid fa-hashtag"></i>
                                    </div>
                                    <div>
                                        <div class="text-xs text-slate-500">Cripta</div>
                                        <div class="text-sm font-bold text-slate-800">{{ $crypt->code }}</div>
                                        <div class="text-xs text-slate-600">Código único</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notas --}}
                    @if($crypt->notes)
                        <div>
                            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                                <i class="fa-solid fa-note-sticky text-amber-600 mr-2"></i>
                                Notas / Observaciones
                            </h3>
                            <div class="bg-amber-50 border border-amber-100 rounded-lg p-4">
                                <p class="text-sm text-slate-700">{{ $crypt->notes }}</p>
                            </div>
                        </div>
                    @endif

                    {{-- Alerta de Bloqueo --}}
                    @if($crypt->is_blocked)
                        <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                            <div class="flex items-start">
                                <i class="fa-solid fa-lock text-red-500 mt-0.5 mr-3"></i>
                                <div>
                                    <h4 class="text-sm font-semibold text-red-800">Cripta Bloqueada</h4>
                                    <p class="text-sm text-red-700 mt-1">{{ $crypt->blocked_reason }}</p>
                                    @if($crypt->blocked_at)
                                        <p class="text-xs text-red-600 mt-2">
                                            <i class="fa-solid fa-clock mr-1"></i>
                                            Bloqueada desde: {{ $crypt->blocked_at->format('d/m/Y H:i') }}
                                        </p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                {{-- ============================================ --}}
                {{-- TAB 2: TIMELINE --}}
                {{-- ============================================ --}}
                <div x-show="activeTab === 'timeline'" class="space-y-6">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center">
                        <i class="fa-solid fa-clock-rotate-left text-indigo-600 mr-2"></i>
                        Historial de Eventos
                    </h3>
                    
                    <div class="relative">
                        <div class="absolute left-4 top-0 bottom-0 w-0.5 bg-slate-200"></div>
                        
                        <div class="space-y-4">
                            @foreach($activityHistory as $event)
                                <div class="relative flex items-start space-x-4">
                                    <div class="relative z-10 h-8 w-8 rounded-full bg-{{ $event['color'] }}-100 text-{{ $event['color'] }}-600 flex items-center justify-center flex-shrink-0">
                                        <i class="fa-solid {{ $event['icon'] }} text-sm"></i>
                                    </div>
                                    <div class="flex-1 bg-slate-50 rounded-lg p-4 border border-slate-100">
                                        <div class="flex items-center justify-between mb-1">
                                            <h4 class="text-sm font-bold text-slate-800">{{ ucfirst($event['action']) }}</h4>
                                            <span class="text-xs text-slate-500">{{ $event['date']->format('d/m/Y H:i') }}</span>
                                        </div>
                                        <p class="text-sm text-slate-600">{{ $event['description'] }}</p>
                                        <p class="text-xs text-slate-500 mt-1">Por: {{ $event['user'] }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Placeholder para eventos futuros --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fa-solid fa-circle-info text-blue-600 mr-3"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-blue-800">Timeline Completo (Próximamente)</h4>
                                <p class="text-xs text-blue-700 mt-1">
                                    Cuando se implementen los módulos de Contratos (EPIC 3), Operaciones (EPIC 5) y Pagos (EPIC 4), 
                                    este timeline mostrará automáticamente: inhumaciones, exhumaciones, traslados, cambios de titularidad, 
                                    pagos recibidos y más.
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- TAB 3: CONTRATOS --}}
                {{-- ============================================ --}}
                <div x-show="activeTab === 'contracts'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center">
                            <i class="fa-solid fa-file-contract text-emerald-600 mr-2"></i>
                            Contratos Asociados
                        </h3>
                        <a href="#" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <i class="fa-solid fa-plus mr-2"></i> Nuevo Contrato
                        </a>
                    </div>
                    {{-- (Correcto y Seguro) --}}
                    @if($totalContracts > 0 && isset($crypt->contracts))
                        <div class="space-y-4">
                            @foreach($crypt->contracts ?? [] as $contract)
                                <div class="bg-white border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-center justify-between mb-3">
                                        <div class="flex items-center space-x-3">
                                            <span class="text-sm font-bold text-slate-800">{{ $contract->contract_number ?? 'N/A' }}</span>
                                            <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $contract->status === 'active' ? 'bg-emerald-100 text-emerald-800' : 'bg-slate-100 text-slate-700' }}">
                                                {{ ucfirst($contract->status) }}
                                            </span>
                                        </div>
                                        <span class="text-xs text-slate-500">{{ $contract->signed_at?->format('d/m/Y') ?? 'Sin firmar' }}</span>
                                    </div>
                                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 text-sm">
                                        <div>
                                            <span class="text-xs text-slate-500 block">Titular</span>
                                            <span class="font-medium text-slate-800">{{ $contract->customer->name ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-xs text-slate-500 block">Tipo</span>
                                            <span class="font-medium text-slate-800">{{ $contract->contractType->name ?? 'N/A' }}</span>
                                        </div>
                                        <div>
                                            <span class="text-xs text-slate-500 block">Inicio</span>
                                            <span class="font-medium text-slate-800">{{ $contract->start_date->format('d/m/Y') }}</span>
                                        </div>
                                        <div>
                                            <span class="text-xs text-slate-500 block">Vigencia</span>
                                            <span class="font-medium text-slate-800">{{ $contract->end_date?->format('d/m/Y') ?? 'Perpetuo' }}</span>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-slate-50 rounded-lg border-2 border-dashed border-slate-200">
                            <i class="fa-solid fa-file-contract text-4xl text-slate-300 mb-3"></i>
                            <h4 class="text-sm font-semibold text-slate-600 mb-1">Sin contratos registrados</h4>
                            <p class="text-xs text-slate-500 mb-4">Esta cripta aún no tiene contratos asociados</p>
                            <a href="#" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <i class="fa-solid fa-plus mr-2"></i> Crear Primer Contrato
                            </a>
                        </div>
                    @endif

                    {{-- Placeholder EPIC 3 --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fa-solid fa-circle-info text-blue-600 mr-3"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-blue-800">Módulo de Contratos (EPIC 3 - Próximamente)</h4>
                                <p class="text-xs text-blue-700 mt-1">
                                    Cuando se implemente el EPIC 3, podrás crear contratos perpetuos y temporales, 
                                    gestionar beneficiarios, herederos, y realizar traspasos y sucesiones (RN-05).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ============================================ --}}
                {{-- TAB 4: DOCUMENTOS --}}
                {{-- ============================================ --}}
                <div x-show="activeTab === 'documents'" class="space-y-6">
                    <div class="flex items-center justify-between">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center">
                            <i class="fa-solid fa-folder-open text-amber-600 mr-2"></i>
                            Documentos Adjuntos
                        </h3>
                        <button class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                            <i class="fa-solid fa-upload mr-2"></i> Subir Documento
                        </button>
                    </div>
                    {{-- (Correcto y Seguro) --}}
                    @if($totalDocuments > 0 && isset($crypt->documents))
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @foreach($crypt->documents ?? [] as $document)
                                <div class="bg-white border border-slate-200 rounded-lg p-4 hover:shadow-md transition-shadow">
                                    <div class="flex items-center space-x-3 mb-3">
                                        <div class="h-10 w-10 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </div>
                                        <div class="flex-1 min-w-0">
                                            <h4 class="text-sm font-bold text-slate-800 truncate">{{ $document->name }}</h4>
                                            <p class="text-xs text-slate-500">{{ $document->type }}</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center justify-between text-xs text-slate-500">
                                        <span>{{ $document->created_at->format('d/m/Y') }}</span>
                                        <a href="#" class="text-emerald-600 hover:text-emerald-700 font-medium">
                                            <i class="fa-solid fa-download mr-1"></i> Descargar
                                        </a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-12 bg-slate-50 rounded-lg border-2 border-dashed border-slate-200">
                            <i class="fa-solid fa-folder-open text-4xl text-slate-300 mb-3"></i>
                            <h4 class="text-sm font-semibold text-slate-600 mb-1">Sin documentos adjuntos</h4>
                            <p class="text-xs text-slate-500 mb-4">Sube contratos, actas, certificados o cualquier documento relacionado</p>
                            <button class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors">
                                <i class="fa-solid fa-upload mr-2"></i> Subir Primer Documento
                            </button>
                        </div>
                    @endif
                </div>

                {{-- ============================================ --}}
                {{-- TAB 5: ESTADO DE CUENTA --}}
                {{-- ============================================ --}}
                <div x-show="activeTab === 'account'" class="space-y-6">
                    <h3 class="text-lg font-bold text-slate-800 flex items-center">
                        <i class="fa-solid fa-file-invoice-dollar text-indigo-600 mr-2"></i>
                        Estado de Cuenta
                    </h3>

                    {{-- Resumen Financiero --}}
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="bg-emerald-50 border border-emerald-100 rounded-lg p-4">
                            <div class="text-xs text-emerald-600 font-medium mb-1">TOTAL PAGADO</div>
                            <div class="text-2xl font-bold text-emerald-700">$0.00</div>
                            <div class="text-xs text-emerald-600 mt-1">0 pagos registrados</div>
                        </div>
                        <div class="bg-red-50 border border-red-100 rounded-lg p-4">
                            <div class="text-xs text-red-600 font-medium mb-1">ADEUDO PENDIENTE</div>
                            <div class="text-2xl font-bold text-red-700">$0.00</div>
                            <div class="text-xs text-red-600 mt-1">{{ $pendingDebts }} adeudos pendientes</div>
                        </div>
                        <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4">
                            <div class="text-xs text-indigo-600 font-medium mb-1">PRÓXIMO VENCIMIENTO</div>
                            <div class="text-2xl font-bold text-indigo-700">N/A</div>
                            <div class="text-xs text-indigo-600 mt-1">Sin adeudos programados</div>
                        </div>
                    </div>
                    {{-- Lista de Adeudos --}}
                    {{-- (Correcto y Seguro) --}}
                    @if($totalDebts > 0 && isset($crypt->debts))
                        <div class="bg-white border border-slate-200 rounded-lg overflow-hidden">
                            <table class="min-w-full divide-y divide-slate-200">
                                <thead class="bg-slate-50">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Concepto</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Vencimiento</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Monto</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold text-slate-500 uppercase">Estado</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-slate-100">
                                    @foreach($crypt->debts as $debt)
                                        <tr class="hover:bg-slate-50 transition-colors">
                                            <td class="px-4 py-3 text-sm text-slate-800">{{ $debt->debt_type }}</td>
                                            <td class="px-4 py-3 text-sm text-slate-600">{{ $debt->due_date->format('d/m/Y') }}</td>
                                            <td class="px-4 py-3 text-sm font-medium text-slate-800">${{ number_format($debt->total_amount, 2) }}</td>
                                            <td class="px-4 py-3">
                                                <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $debt->status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800' }}">
                                                    {{ ucfirst($debt->status) }}
                                                </span>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="text-center py-12 bg-slate-50 rounded-lg border-2 border-dashed border-slate-200">
                            <i class="fa-solid fa-file-invoice-dollar text-4xl text-slate-300 mb-3"></i>
                            <h4 class="text-sm font-semibold text-slate-600 mb-1">Sin movimientos financieros</h4>
                            <p class="text-xs text-slate-500">Los pagos y adeudos aparecerán aquí cuando se registren</p>
                        </div>
                    @endif

                    {{-- Placeholder EPIC 4 --}}
                    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fa-solid fa-circle-info text-blue-600 mr-3"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-blue-800">Módulo Financiero (EPIC 4 - Próximamente)</h4>
                                <p class="text-xs text-blue-700 mt-1">
                                    Cuando se implemente el EPIC 4, podrás registrar pagos, emitir CFDI 4.0 (SAT), 
                                    calcular adeudos automáticos de mantenimiento, y gestionar bloqueos por morosidad (RN-04).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ============================================ --}}
        {{-- FOOTER: Acciones Rápidas --}}
        {{-- ============================================ --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <div class="flex items-center justify-between">
                <div class="text-xs text-slate-500">
                    <i class="fa-solid fa-clock mr-1"></i>
                    Última actualización: {{ $crypt->updated_at->format('d/m/Y H:i') }}
                </div>
                <div class="flex items-center space-x-3">
                    <a href="{{ route('inventory.crypts.index') }}" class="inline-flex items-center px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-50 text-sm font-medium rounded-lg transition-colors">
                        <i class="fa-solid fa-list mr-2"></i> Volver al Listado
                    </a>
                    <a href="{{ route('inventory.crypts.edit', $crypt) }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fa-solid fa-pen-to-square mr-2"></i> Editar Cripta
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>