<x-app-layout>
    <x-slot name="title">Crear Nueva Cripta</x-slot>

    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('inventory.crypts.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Crear Nueva Cripta</h2>
                <p class="text-sm text-slate-500 mt-1">Define la ubicación y características del espacio</p>
            </div>
        </div>
    </x-slot>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 mr-3"></i>
                <div>
                    <h3 class="text-sm font-semibold text-red-800">Se encontraron errores</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    {{-- ✅ TODO debe estar dentro del MISMO x-data --}}
    <div x-data="hierarchyManager()">
        
        <form method="POST" action="{{ route('inventory.crypts.store') }}" class="max-w-4xl mx-auto space-y-6">
            @csrf

            {{-- Selección de Jerarquía con Modales Rápidos --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-sitemap text-indigo-600 mr-2"></i>
                    Ubicación en la Jerarquía
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    
                    {{-- Sección --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Sección</label>
                        <div class="flex gap-2">
                            <select name="section_select" x-model="selectedSection" @change="updateBlocks" class="flex-1 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                                <option value="">Seleccionar Sección...</option>
                                <template x-for="section in sectionsData" :key="section.id">
                                    <option :value="section.id" x-text="section.code + ' - ' + section.name"></option>
                                </template>
                            </select>
                            <button type="button" @click="openModal('section')" class="px-3 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors" title="Crear nueva sección">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Bloque --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Bloque</label>
                        <div class="flex gap-2">
                            <select name="block_select" x-model="selectedBlock" @change="updateLevels" :disabled="!selectedSection" class="flex-1 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm disabled:bg-slate-100 disabled:cursor-not-allowed">
                                <option value="">Seleccionar Bloque...</option>
                                <template x-for="block in availableBlocks" :key="block.id">
                                    <option :value="block.id" x-text="block.code + ' - ' + block.name"></option>
                                </template>
                            </select>
                            <button type="button" @click="openModal('block')" :disabled="!selectedSection" class="px-3 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors disabled:bg-slate-300 disabled:cursor-not-allowed" title="Crear nuevo bloque">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    {{-- Nivel --}}
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nivel</label>
                        <div class="flex gap-2">
                            <select name="level_id" :disabled="!selectedBlock" required class="flex-1 px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm disabled:bg-slate-100 disabled:cursor-not-allowed">
                                <option value="">Seleccionar Nivel...</option>
                                <template x-for="level in availableLevels" :key="level.id">
                                    <option :value="level.id" x-text="level.code + ' - ' + level.name"></option>
                                </template>
                            </select>
                            <button type="button" @click="openModal('level')" :disabled="!selectedBlock" class="px-3 py-2.5 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg transition-colors disabled:bg-slate-300 disabled:cursor-not-allowed" title="Crear nuevo nivel">
                                <i class="fa-solid fa-plus"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Datos de la Cripta --}}
            <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-layer-group text-emerald-600 mr-2"></i>
                    Características de la Cripta
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Código Único <span class="text-red-500">*</span></label>
                        <input type="text" name="code" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm uppercase" placeholder="Ej: 01">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipo de Cripta <span class="text-red-500">*</span></label>
                        <select name="crypt_type_id" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                            @foreach($types as $type)
                                <option value="{{ $type->id }}">{{ $type->name }} (Cap. {{ $type->default_capacity }}-{{ $type->max_capacity }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estado Inicial <span class="text-red-500">*</span></label>
                        <select name="crypt_status_id" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                            @foreach($statuses as $status)
                                <option value="{{ $status->id }}" {{ $status->code === 'available' ? 'selected' : '' }}>{{ $status->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Capacidad Máxima <span class="text-red-500">*</span></label>
                        <input type="number" name="capacity" required min="1" max="10" value="2" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Precio de Venta <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 text-sm">$</span>
                            <input type="number" name="price" required step="0.01" min="0" class="w-full pl-8 pr-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="0.00">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Dimensiones (opcional)</label>
                        <input type="text" name="dimensions" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Ej: 2.0 x 1.0 x 1.5 m">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Notas / Observaciones</label>
                        <textarea name="notes" rows="3" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Detalles adicionales sobre la cripta..."></textarea>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-end space-x-3 pb-6">
                <a href="{{ route('inventory.crypts.index') }}" class="inline-flex items-center px-5 py-2.5 border border-slate-300 shadow-sm text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                    <i class="fa-solid fa-xmark mr-2"></i> Cancelar
                </a>
                <button type="submit" class="inline-flex items-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                    <i class="fa-solid fa-save mr-2"></i> Guardar Cripta
                </button>
            </div>
        </form>

        {{-- ============================================ --}}
        {{-- MODALES DE CREACIÓN RÁPIDA (DENTRO del mismo x-data) --}}
        {{-- ============================================ --}}
        
        {{-- Modal Sección --}}
        <div x-show="modals.section" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="closeModal('section')">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modals.section" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/75 transition-opacity" @click="closeModal('section')"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="modals.section" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center">
                            <i class="fa-solid fa-map-pin text-emerald-600 mr-2"></i>
                            Crear Nueva Sección
                        </h3>
                    </div>
                    <form @submit.prevent="createSection" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Código <span class="text-red-500">*</span></label>
                            <input type="text" x-model="forms.section.code" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm uppercase" placeholder="Ej: A, B, SAN_PEDRO">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" x-model="forms.section.name" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Ej: Sección San Pedro">
                        </div>
                        <div x-show="forms.section.error" class="bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg">
                            <p class="text-sm text-red-700" x-text="forms.section.error"></p>
                        </div>
                    </form>
                    <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="createSection" :disabled="forms.section.loading" class="w-full inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <i x-show="forms.section.loading" class="fa-solid fa-spinner fa-spin mr-2"></i>
                            <span x-text="forms.section.loading ? 'Creando...' : 'Crear Sección'"></span>
                        </button>
                        <button type="button" @click="closeModal('section')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Bloque --}}
        <div x-show="modals.block" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="closeModal('block')">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modals.block" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/75 transition-opacity" @click="closeModal('block')"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="modals.block" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center">
                            <i class="fa-solid fa-cubes text-indigo-600 mr-2"></i>
                            Crear Nuevo Bloque
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Sección: <span class="font-semibold" x-text="getSelectedSectionName()"></span></p>
                    </div>
                    <form @submit.prevent="createBlock" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Código <span class="text-red-500">*</span></label>
                            <input type="text" x-model="forms.block.code" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm uppercase" placeholder="Ej: 1, B1, MANZANA_1">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" x-model="forms.block.name" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Ej: Bloque 1">
                        </div>
                        <div x-show="forms.block.error" class="bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg">
                            <p class="text-sm text-red-700" x-text="forms.block.error"></p>
                        </div>
                    </form>
                    <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="createBlock" :disabled="forms.block.loading" class="w-full inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <i x-show="forms.block.loading" class="fa-solid fa-spinner fa-spin mr-2"></i>
                            <span x-text="forms.block.loading ? 'Creando...' : 'Crear Bloque'"></span>
                        </button>
                        <button type="button" @click="closeModal('block')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Nivel --}}
        <div x-show="modals.level" x-cloak class="fixed inset-0 z-50 overflow-y-auto" @keydown.escape.window="closeModal('level')">
            <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                <div x-show="modals.level" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-slate-900/75 transition-opacity" @click="closeModal('level')"></div>
                <span class="hidden sm:inline-block sm:align-middle sm:h-screen">&#8203;</span>
                <div x-show="modals.level" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg w-full">
                    <div class="bg-white px-6 py-4 border-b border-slate-200">
                        <h3 class="text-lg font-bold text-slate-800 flex items-center">
                            <i class="fa-solid fa-layer-group text-purple-600 mr-2"></i>
                            Crear Nuevo Nivel
                        </h3>
                        <p class="text-xs text-slate-500 mt-1">Bloque: <span class="font-semibold" x-text="getSelectedBlockName()"></span></p>
                    </div>
                    <form @submit.prevent="createLevel" class="p-6 space-y-4">
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Código <span class="text-red-500">*</span></label>
                            <input type="text" x-model="forms.level.code" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm uppercase" placeholder="Ej: 1, N1, PISO_1">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                            <input type="text" x-model="forms.level.name" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="Ej: Nivel 1">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-1.5">Orden de Altura <span class="text-red-500">*</span></label>
                            <input type="number" x-model="forms.level.height_order" required min="1" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" placeholder="1 = más abajo">
                            <p class="text-xs text-slate-500 mt-1">1 = nivel inferior, 2 = segundo nivel, etc.</p>
                        </div>
                        <div x-show="forms.level.error" class="bg-red-50 border-l-4 border-red-500 p-3 rounded-r-lg">
                            <p class="text-sm text-red-700" x-text="forms.level.error"></p>
                        </div>
                    </form>
                    <div class="bg-slate-50 px-6 py-4 sm:flex sm:flex-row-reverse">
                        <button type="button" @click="createLevel" :disabled="forms.level.loading" class="w-full inline-flex justify-center items-center rounded-lg border border-transparent shadow-sm px-4 py-2 bg-emerald-600 text-base font-medium text-white hover:bg-emerald-700 focus:outline-none sm:ml-3 sm:w-auto sm:text-sm disabled:opacity-50 disabled:cursor-not-allowed">
                            <i x-show="forms.level.loading" class="fa-solid fa-spinner fa-spin mr-2"></i>
                            <span x-text="forms.level.loading ? 'Creando...' : 'Crear Nivel'"></span>
                        </button>
                        <button type="button" @click="closeModal('level')" class="mt-3 w-full inline-flex justify-center rounded-lg border border-slate-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-slate-700 hover:bg-slate-50 focus:outline-none sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                            Cancelar
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Toast de Éxito --}}
        <div x-show="toast.show" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" class="fixed bottom-6 right-6 z-50 max-w-md">
            <div class="bg-emerald-600 text-white px-5 py-3 rounded-lg shadow-2xl flex items-center justify-between">
                <div class="flex items-center">
                    <i class="fa-solid fa-circle-check mr-3"></i>
                    <span class="text-sm font-medium" x-text="toast.message"></span>
                </div>
                <button @click="toast.show = false" class="ml-4 text-white hover:text-emerald-200">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>

        {{-- SCRIPT ALPINE.JS --}}
        <script>
            function hierarchyManager() {
                return {
                    // Datos de jerarquía
                    sectionsData: @json($sections),
                    selectedSection: '',
                    selectedBlock: '',
                    availableBlocks: [],
                    availableLevels: [],

                    // Estado de modales
                    modals: {
                        section: false,
                        block: false,
                        level: false
                    },

                    // Formularios de modales
                    forms: {
                        section: { code: '', name: '', loading: false, error: '' },
                        block: { code: '', name: '', loading: false, error: '' },
                        level: { code: '', name: '', height_order: 1, loading: false, error: '' }
                    },

                    // Toast
                    toast: { show: false, message: '' },

                    // Inicialización
                    init() {
                        if (this.selectedSection) {
                            this.updateBlocks();
                        }
                    },

                    // Actualizar bloques cuando cambia la sección
                    updateBlocks() {
                        this.selectedBlock = '';
                        this.availableLevels = [];
                        const section = this.sectionsData.find(s => s.id == this.selectedSection);
                        this.availableBlocks = section ? section.blocks : [];
                    },

                    // Actualizar niveles cuando cambia el bloque
                    updateLevels() {
                        const block = this.availableBlocks.find(b => b.id == this.selectedBlock);
                        this.availableLevels = block ? block.levels : [];
                    },

                    // Abrir modal
                    openModal(type) {
                        this.modals[type] = true;
                        this.forms[type].error = '';
                        if (type === 'section') {
                            this.forms.section = { code: '', name: '', loading: false, error: '' };
                        } else if (type === 'block') {
                            this.forms.block = { code: '', name: '', loading: false, error: '' };
                        } else if (type === 'level') {
                            this.forms.level = { code: '', name: '', height_order: 1, loading: false, error: '' };
                        }
                    },

                    // Cerrar modal
                    closeModal(type) {
                        this.modals[type] = false;
                    },

                    // Obtener nombre de sección seleccionada
                    getSelectedSectionName() {
                        const section = this.sectionsData.find(s => s.id == this.selectedSection);
                        return section ? section.code + ' - ' + section.name : 'N/A';
                    },

                    // Obtener nombre de bloque seleccionado
                    getSelectedBlockName() {
                        const block = this.availableBlocks.find(b => b.id == this.selectedBlock);
                        return block ? block.code + ' - ' + block.name : 'N/A';
                    },

                   
// Crear Sección
async createSection() {
    this.forms.section.loading = true;
    this.forms.section.error = '';

    try {
        // Verificar que el CSRF token exista
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            throw new Error('CSRF token no encontrado. Recarga la página.');
        }

        const response = await fetch('{{ route("inventory.hierarchy.sections.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                code: this.forms.section.code,
                name: this.forms.section.name
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // Agregar nueva sección al array
            this.sectionsData.push(data.data);
            // Auto-seleccionar
            this.selectedSection = data.data.id;
            this.updateBlocks();
            // Cerrar modal y mostrar toast
            this.closeModal('section');
            this.showToast('Sección creada exitosamente');
        } else {
            // ✅ CORRECCIÓN: Mostrar error real del servidor
            this.forms.section.error = data.message || 'Error al crear sección';
            console.error('Error del servidor:', data);
        }
    } catch (error) {
        // ✅ CORRECCIÓN: Mostrar error detallado
        this.forms.section.error = `Error: ${error.message}`;
        console.error('Error de conexión:', error);
    } finally {
        this.forms.section.loading = false;
    }
},

// Crear Bloque
async createBlock() {
    if (!this.selectedSection) {
        this.forms.block.error = 'Debes seleccionar una sección primero';
        return;
    }

    this.forms.block.loading = true;
    this.forms.block.error = '';

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            throw new Error('CSRF token no encontrado. Recarga la página.');
        }

        const response = await fetch('{{ route("inventory.hierarchy.blocks.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                section_id: this.selectedSection,
                code: this.forms.block.code,
                name: this.forms.block.name
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // ✅ CORRECCIÓN: Buscar la sección y asegurar que tenga array blocks
            const section = this.sectionsData.find(s => s.id == this.selectedSection);
            if (section) {
                // Inicializar blocks si es undefined
                if (!section.blocks) {
                    section.blocks = [];
                }
                section.blocks.push(data.data);
                this.availableBlocks = section.blocks;
            }
            this.selectedBlock = data.data.id;
            this.updateLevels();
            this.closeModal('block');
            this.showToast('Bloque creado exitosamente');
        } else {
            this.forms.block.error = data.message || 'Error al crear bloque';
            console.error('Error del servidor:', data);
        }
    } catch (error) {
        this.forms.block.error = `Error: ${error.message}`;
        console.error('Error de conexión:', error);
    } finally {
        this.forms.block.loading = false;
    }
},

// Crear Nivel
async createLevel() {
    if (!this.selectedBlock) {
        this.forms.level.error = 'Debes seleccionar un bloque primero';
        return;
    }

    this.forms.level.loading = true;
    this.forms.level.error = '';

    try {
        const csrfToken = document.querySelector('meta[name="csrf-token"]');
        if (!csrfToken) {
            throw new Error('CSRF token no encontrado. Recarga la página.');
        }

        const response = await fetch('{{ route("inventory.hierarchy.levels.store") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken.content,
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                block_id: this.selectedBlock,
                code: this.forms.level.code,
                name: this.forms.level.name,
                height_order: this.forms.level.height_order
            })
        });

        const data = await response.json();

        if (response.ok && data.success) {
            // ✅ CORRECCIÓN: Buscar el bloque y asegurar que tenga array levels
            const block = this.availableBlocks.find(b => b.id == this.selectedBlock);
            if (block) {
                // Inicializar levels si es undefined
                if (!block.levels) {
                    block.levels = [];
                }
                block.levels.push(data.data);
                this.availableLevels = block.levels;
            }
            // Auto-seleccionar el nuevo nivel
            setTimeout(() => {
                const select = document.querySelector('select[name="level_id"]');
                if (select) {
                    select.value = data.data.id;
                    // Disparar evento change para que Alpine.js lo detecte
                    select.dispatchEvent(new Event('change'));
                }
            }, 100);
            // Cerrar modal y mostrar toast
            this.closeModal('level');
            this.showToast('Nivel creado exitosamente');
        } else {
            this.forms.level.error = data.message || 'Error al crear nivel';
            console.error('Error del servidor:', data);
        }
    } catch (error) {
        this.forms.level.error = `Error: ${error.message}`;
        console.error('Error de conexión:', error);
    } finally {
        this.forms.level.loading = false;
    }
},



                    // Mostrar toast
                    showToast(message) {
                        this.toast.message = message;
                        this.toast.show = true;
                        setTimeout(() => {
                            this.toast.show = false;
                        }, 3000);
                    }
                }
            }
        </script>
    </div>
</x-app-layout>