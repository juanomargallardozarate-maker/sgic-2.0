<x-app-layout>
    <x-slot name="title">Mapa de Inventario</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('inventory.crypts.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-arrow-left text-lg"></i>
                </a>
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Mapa Visual del Cementerio</h2>
                    <p class="text-sm text-slate-500 mt-1">Vista jerárquica del estado de las criptas</p>
                </div>
            </div>
            <a href="{{ route('inventory.crypts.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                <i class="fa-solid fa-plus mr-2"></i> Nueva Cripta
            </a>
        </div>
    </x-slot>

    {{-- Panel de Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-6">
        <form method="GET" action="{{ route('inventory.crypts.map') }}" class="grid grid-cols-1 md:grid-cols-5 gap-3">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Sección</label>
                <select name="section_id" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <option value="">Todas las secciones</option>
                    @foreach($sections as $section)
                        <option value="{{ $section->id }}" {{ request('section_id') == $section->id ? 'selected' : '' }}>
                            {{ $section->code }} - {{ $section->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Estado</label>
                <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->code }}" {{ request('status') == $status->code ? 'selected' : '' }}>
                            {{ $status->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Tipo</label>
                <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <option value="">Todos los tipos</option>
                    @foreach($types as $type)
                        <option value="{{ $type->code }}" {{ request('type') == $type->code ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Disponibilidad</label>
                <select name="available_only" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <option value="">Todas</option>
                    <option value="1" {{ request('available_only') ? 'selected' : '' }}>Solo disponibles</option>
                </select>
            </div>
            
            <div class="flex items-end gap-2">
                <button type="submit" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white font-medium py-2 px-4 rounded-lg transition-colors text-sm">
                    <i class="fa-solid fa-filter mr-1"></i> Filtrar
                </button>
                <a href="{{ route('inventory.crypts.map') }}" class="bg-slate-200 hover:bg-slate-300 text-slate-700 font-medium py-2 px-3 rounded-lg transition-colors text-sm flex items-center justify-center" title="Limpiar filtros">
                    <i class="fa-solid fa-xmark"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Resumen de Estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 flex items-center">
            <div class="p-3 bg-emerald-50 rounded-lg text-emerald-600 mr-4">
                <i class="fa-solid fa-layer-group text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Total Criptas</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($totalCrypts ?? 0) }}</p>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 flex items-center">
            <div class="p-3 bg-blue-50 rounded-lg text-blue-600 mr-4">
                <i class="fa-solid fa-circle-check text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Disponibles</p>
                <p class="text-2xl font-bold text-slate-800">{{ number_format($availableCrypts ?? 0) }}</p>
            </div>
        </div>
        
        <div class="bg-white rounded-xl p-4 shadow-sm border border-slate-100 flex items-center">
            <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600 mr-4">
                <i class="fa-solid fa-chart-pie text-xl"></i>
            </div>
            <div>
                <p class="text-xs text-slate-500 font-medium">Tasa de Ocupación</p>
                <p class="text-2xl font-bold text-slate-800">
                    @php
                        $occupancyRate = $totalCrypts > 0 ? round((($totalCrypts - $availableCrypts) / $totalCrypts) * 100, 1) : 0;
                    @endphp
                    {{ $occupancyRate }}%
                </p>
            </div>
        </div>
    </div>

    {{-- Leyenda de Estados --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-center">
            <span class="text-sm font-semibold text-slate-700 mr-2">Leyenda:</span>
            @foreach($statuses as $status)
                <div class="flex items-center space-x-2">
                    <span class="h-3 w-3 rounded-full" style="background-color: {{ $status->color }};"></span>
                    <span class="text-sm text-slate-600">{{ $status->name }}</span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- Mapa Jerárquico --}}
    <div class="space-y-6">
        @forelse ($sections as $section)
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                <div class="bg-slate-50 px-6 py-4 border-b border-slate-200 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-slate-800">
                        <i class="fa-solid fa-map-pin text-emerald-600 mr-2"></i>
                        Sección {{ $section->code }} - {{ $section->name }}
                    </h3>
                    <span class="text-xs font-semibold text-slate-500 bg-slate-200 px-2 py-1 rounded">
                        {{ $section->blocks->sum(fn($b) => $b->levels->sum(fn($l) => $l->crypts->count())) }} Criptas
                    </span>
                </div>

                <div class="p-6 space-y-6">
                    @forelse ($section->blocks as $block)
                        <div>
                            <h4 class="text-sm font-semibold text-slate-600 mb-3 flex items-center">
                                <i class="fa-solid fa-cubes text-indigo-500 mr-2"></i> Bloque {{ $block->code }} - {{ $block->name }}
                            </h4>
                            
                            <div class="space-y-4">
                                @foreach ($block->levels->sortBy('height_order') as $level)
                                    <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                                        <div class="flex items-center justify-between mb-3">
                                            <span class="text-xs font-bold text-slate-500 uppercase">Nivel {{ $level->code }} - {{ $level->name }}</span>
                                            <span class="text-xs text-slate-400">{{ $level->crypts->count() }} criptas</span>
                                        </div>
                                        
                                        <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 lg:grid-cols-10 gap-3">
                                            @forelse ($level->crypts as $crypt)
                                                <div 
                                                    class="relative group cursor-pointer"
                                                    @click="$dispatch('open-crypt-modal', { cryptId: {{ $crypt->id }} })"
                                                    title="{{ $crypt->code }}: {{ $crypt->cryptStatus->name }} ({{ $crypt->current_occupancy }}/{{ $crypt->capacity }})"
                                                >
                                                    <div 
                                                        class="aspect-square rounded-lg border-2 flex flex-col items-center justify-center transition-all hover:scale-110 hover:shadow-lg hover:z-10"
                                                        style="border-color: {{ $crypt->cryptStatus->color }}; background-color: {{ $crypt->cryptStatus->color }}15;"
                                                    >
                                                        <span class="text-xs font-bold" style="color: {{ $crypt->cryptStatus->color }};">{{ $crypt->code }}</span>
                                                        <span class="text-[10px] text-slate-500 mt-1">{{ $crypt->current_occupancy }}/{{ $crypt->capacity }}</span>
                                                        
                                                        @if($crypt->is_blocked)
                                                            <i class="fa-solid fa-lock absolute top-1 right-1 text-xs" style="color: {{ $crypt->cryptStatus->color }};"></i>
                                                        @endif
                                                    </div>
                                                </div>
                                            @empty
                                                <div class="col-span-full text-center py-4 text-sm text-slate-400 italic">
                                                    Sin criptas registradas en este nivel
                                                </div>
                                            @endforelse 
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-8 text-slate-500">
                            <i class="fa-solid fa-cubes text-3xl text-slate-300 mb-2"></i>
                            <p class="text-sm">No hay bloques registrados en esta sección.</p>
                        </div>
                    @endforelse
                </div>
            </div>
        @empty
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-12 text-center">
                <i class="fa-solid fa-map text-5xl text-slate-300 mb-4"></i>
                <h3 class="text-lg font-bold text-slate-800 mb-2">No hay secciones registradas</h3>
                <p class="text-slate-500 mb-6">Comienza creando la jerarquía de tu cementerio.</p>
                <a href="{{ route('inventory.crypts.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-plus mr-2"></i> Configurar Jerarquía y Criptas
                </a>
            </div>
        @endforelse
    </div>

    {{-- ============================================ --}}
    {{-- MODAL DETALLADO DE CRIPTA (Alpine.js) --}}
    {{-- ============================================ --}}
    <div 
        x-data="cryptModal()"
        x-on:open-crypt-modal.window="openModal($event.detail.cryptId)"
        x-show="isOpen"
        x-cloak
        class="fixed inset-0 z-50 overflow-y-auto"
        @keydown.escape.window="closeModal()"
    >
        {{-- Overlay --}}
        <div 
            x-show="isOpen"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            class="fixed inset-0 bg-slate-900/75 transition-opacity"
            @click="closeModal()"
        ></div>

        {{-- Modal Content --}}
        <div class="flex items-center justify-center min-h-screen p-4">
            <div 
                x-show="isOpen"
                x-transition:enter="ease-out duration-300"
                x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave="ease-in duration-200"
                x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                class="relative bg-white rounded-2xl shadow-2xl max-w-2xl w-full max-h-[90vh] overflow-y-auto"
            >
                {{-- Header --}}
                <div class="sticky top-0 bg-white border-b border-slate-200 px-6 py-4 rounded-t-2xl z-10">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-3">
                            <div 
                                class="h-12 w-12 rounded-lg flex items-center justify-center"
                                :style="`background-color: ${cryptData.status?.color}20; border: 2px solid ${cryptData.status?.color}`"
                            >
                                <i class="fa-solid fa-layer-group text-xl" :style="`color: ${cryptData.status?.color}`"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-slate-800" x-text="cryptData.full_code || 'Cargando...'"></h3>
                                <div class="flex items-center space-x-2 mt-1">
                                    <span 
                                        class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium"
                                        :style="`background-color: ${cryptData.status?.color}20; color: ${cryptData.status?.color}`"
                                    >
                                        <span class="h-1.5 w-1.5 rounded-full mr-1.5" :style="`background-color: ${cryptData.status?.color}`"></span>
                                        <span x-text="cryptData.status?.name"></span>
                                    </span>
                                    <span class="text-xs text-slate-500" x-text="cryptData.type?.name"></span>
                                </div>
                            </div>
                        </div>
                        <button 
                            @click="closeModal()"
                            class="text-slate-400 hover:text-slate-600 transition-colors p-2 rounded-lg hover:bg-slate-100"
                        >
                            <i class="fa-solid fa-xmark text-xl"></i>
                        </button>
                    </div>
                </div>

                {{-- Loading State --}}
                <div x-show="isLoading" class="p-12 text-center">
                    <i class="fa-solid fa-spinner fa-spin text-4xl text-emerald-600 mb-4"></i>
                    <p class="text-slate-500">Cargando información...</p>
                </div>

                {{-- Content --}}
                <div x-show="!isLoading" class="p-6 space-y-6">
                    
                    {{-- Alerta de Bloqueo --}}
                    <div x-show="cryptData.is_blocked" class="bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
                        <div class="flex items-start">
                            <i class="fa-solid fa-lock text-red-500 mt-0.5 mr-3"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-red-800">Cripta Bloqueada</h4>
                                <p class="text-xs text-red-700 mt-1" x-text="cryptData.blocked_reason || 'Sin razón especificada'"></p>
                            </div>
                        </div>
                    </div>

                    {{-- Grid de Información --}}
                    <div class="grid grid-cols-2 gap-4">
                        {{-- Capacidad --}}
                        <div class="bg-slate-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-slate-500 font-medium">CAPACIDAD</span>
                                <i class="fa-solid fa-users text-slate-400"></i>
                            </div>
                            <div class="text-2xl font-bold text-slate-800">
                                <span x-text="cryptData.capacity?.occupied"></span>
                                <span class="text-slate-400 text-lg">/</span>
                                <span x-text="cryptData.capacity?.total"></span>
                            </div>
                            <div class="text-xs text-slate-500 mt-1">
                                <span x-text="cryptData.capacity?.available"></span> espacios disponibles
                            </div>
                            {{-- Barra de progreso --}}
                            <div class="mt-2 h-2 bg-slate-200 rounded-full overflow-hidden">
                                <div 
                                    class="h-full bg-emerald-500 transition-all"
                                    :style="`width: ${(cryptData.capacity?.occupied / cryptData.capacity?.total * 100) || 0}%`"
                                ></div>
                            </div>
                        </div>

                        {{-- Precio --}}
                        <div class="bg-slate-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-slate-500 font-medium">PRECIO</span>
                                <i class="fa-solid fa-dollar-sign text-slate-400"></i>
                            </div>
                            <div class="text-2xl font-bold text-emerald-600" x-text="`$${formatPrice(cryptData.price)}`"></div>
                            <div class="text-xs text-slate-500 mt-1">Precio de venta</div>
                        </div>

                        {{-- Dimensiones --}}
                        <div class="bg-slate-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-slate-500 font-medium">DIMENSIONES</span>
                                <i class="fa-solid fa-ruler-combined text-slate-400"></i>
                            </div>
                            <div class="text-sm font-bold text-slate-800" x-text="cryptData.dimensions || 'No especificadas'"></div>
                            <div class="text-xs text-slate-500 mt-1" x-show="cryptData.door_type">
                                Puerta: <span class="font-medium capitalize" x-text="cryptData.door_type"></span>
                            </div>
                        </div>

                        {{-- Ubicación --}}
                        <div class="bg-slate-50 rounded-lg p-4">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs text-slate-500 font-medium">UBICACIÓN</span>
                                <i class="fa-solid fa-map-pin text-slate-400"></i>
                            </div>
                            <div class="text-xs space-y-1">
                                <div class="flex items-center">
                                    <i class="fa-solid fa-map text-emerald-600 mr-1.5 text-[10px]"></i>
                                    <span class="text-slate-700 truncate" x-text="cryptData.location?.section"></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fa-solid fa-cubes text-indigo-600 mr-1.5 text-[10px]"></i>
                                    <span class="text-slate-700 truncate" x-text="cryptData.location?.block"></span>
                                </div>
                                <div class="flex items-center">
                                    <i class="fa-solid fa-layer-group text-purple-600 mr-1.5 text-[10px]"></i>
                                    <span class="text-slate-700 truncate" x-text="cryptData.location?.level"></span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Contrato Activo --}}
                    <div x-show="cryptData.active_contract" class="bg-gradient-to-r from-indigo-50 to-purple-50 rounded-lg p-4 border border-indigo-100">
                        <div class="flex items-center justify-between mb-3">
                            <h4 class="text-sm font-bold text-indigo-900 flex items-center">
                                <i class="fa-solid fa-file-contract mr-2"></i>
                                Contrato Activo
                            </h4>
                            <span class="text-xs font-mono text-indigo-700" x-text="cryptData.active_contract?.number"></span>
                        </div>
                        
                        <div class="grid grid-cols-2 gap-3 text-sm">
                            <div>
                                <span class="text-xs text-slate-500 block">Titular</span>
                                <span class="font-semibold text-slate-800" x-text="cryptData.active_contract?.customer"></span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block">RFC</span>
                                <span class="font-mono text-slate-800" x-text="cryptData.active_contract?.customer_rfc"></span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block">Tipo de Contrato</span>
                                <span class="font-semibold text-slate-800" x-text="cryptData.active_contract?.type"></span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block">Firmado</span>
                                <span class="font-semibold text-slate-800" x-text="cryptData.active_contract?.signed_at"></span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block">Inicio</span>
                                <span class="font-semibold text-slate-800" x-text="cryptData.active_contract?.start_date"></span>
                            </div>
                            <div>
                                <span class="text-xs text-slate-500 block">Vigencia</span>
                                <span class="font-semibold text-slate-800" x-text="cryptData.active_contract?.end_date"></span>
                            </div>
                        </div>
                    </div>

                    {{-- Sin Contrato --}}
                    <div x-show="!cryptData.active_contract && !isLoading" class="bg-amber-50 border border-amber-200 rounded-lg p-4">
                        <div class="flex items-center">
                            <i class="fa-solid fa-circle-info text-amber-600 mr-3"></i>
                            <div>
                                <h4 class="text-sm font-semibold text-amber-800">Sin Contrato Activo</h4>
                                <p class="text-xs text-amber-700 mt-1">Esta cripta está disponible para venta o reserva.</p>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Footer con Acciones --}}
                <div x-show="!isLoading" class="sticky bottom-0 bg-slate-50 border-t border-slate-200 px-6 py-4 rounded-b-2xl">
                    <div class="flex items-center justify-between">
                        <div class="text-xs text-slate-500">
                            <i class="fa-solid fa-clock mr-1"></i>
                            Actualizado: <span x-text="new Date().toLocaleDateString('es-MX')"></span>
                        </div>
                        <div class="flex items-center space-x-2">
                            <button 
                                @click="closeModal()"
                                class="px-4 py-2 bg-white border border-slate-300 text-slate-700 hover:bg-slate-100 text-sm font-medium rounded-lg transition-colors"
                            >
                                Cerrar
                            </button>
                            <a 
                                :href="cryptData.actions?.show_url"
                                class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                <i class="fa-solid fa-eye mr-2"></i>
                                Ver Detalle
                            </a>
                            <a 
                                :href="cryptData.actions?.edit_url"
                                class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors"
                            >
                                <i class="fa-solid fa-pen-to-square mr-2"></i>
                                Editar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Script Alpine.js para el Modal --}}
    <script>
        function cryptModal() {
            return {
                isOpen: false,
                isLoading: false,
                cryptData: {},
                errorMessage: '',
                
                async openModal(cryptId) {
                    console.log(' Abriendo modal para cripta ID:', cryptId);
                    
                    this.isOpen = true;
                    this.isLoading = true;
                    this.cryptData = {};
                    this.errorMessage = '';
                    
                    try {
                        const url = `/inventory/crypts/${cryptId}/api`;
                        console.log('📡 Fetching:', url);
                        
                        const response = await fetch(url, {
                            method: 'GET',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            }
                        });
                        
                        console.log('📥 Response status:', response.status);
                        
                        if (!response.ok) {
                            throw new Error(`HTTP error! status: ${response.status}`);
                        }
                        
                        const result = await response.json();
                        console.log('📦 Response data:', result);
                        
                        if (result.success && result.data) {
                            this.cryptData = result.data;
                            console.log('✅ Datos cargados:', this.cryptData);
                        } else {
                            console.error('❌ Error en respuesta:', result);
                            this.errorMessage = result.message || 'Error al cargar datos';
                        }
                    } catch (error) {
                        console.error(' Error de conexión:', error);
                        this.errorMessage = 'Error de conexión: ' + error.message;
                    } finally {
                        this.isLoading = false;
                        console.log('🏁 Loading completado');
                    }
                },
                
                closeModal() {
                    console.log(' Cerrando modal');
                    this.isOpen = false;
                    this.cryptData = {};
                    this.errorMessage = '';
                },
                
                formatPrice(price) {
                    if (!price || price === 0) return '0.00';
                    return parseFloat(price).toLocaleString('es-MX', {
                        minimumFractionDigits: 2,
                        maximumFractionDigits: 2
                    });
                },
                
                // Helper para debug
                debugData() {
                    console.log('🔍 cryptData actual:', this.cryptData);
                }
            }
        }
    </script>
</x-app-layout>