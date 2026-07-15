<x-app-layout>
    <x-slot name="title">Editar Cripta: {{ $crypt->code }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('inventory.crypts.show', $crypt) }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Editar Cripta</h2>
                <p class="text-sm text-slate-500 mt-1">{{ $crypt->full_code }}</p>
            </div>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto">
        <form method="POST" action="{{ route('inventory.crypts.update', $crypt) }}" class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 space-y-6">
            @csrf
            {{-- ✅ CRÍTICO: Método PUT para actualizar (no DELETE) --}}
            @method('PUT')

            {{-- Ubicación (solo lectura) --}}
            <div class="bg-slate-50 rounded-lg p-4 border border-slate-200">
                <h3 class="text-sm font-bold text-slate-700 mb-3">Ubicación</h3>
                <div class="grid grid-cols-3 gap-4 text-sm">
                    <div>
                        <span class="text-slate-500 block">Sección</span>
                        <span class="font-semibold">{{ $crypt->level->block->section->code }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Bloque</span>
                        <span class="font-semibold">{{ $crypt->level->block->code }}</span>
                    </div>
                    <div>
                        <span class="text-slate-500 block">Nivel</span>
                        <span class="font-semibold">{{ $crypt->level->code }}</span>
                    </div>
                </div>
            </div>

            {{-- Campos editables --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipo de Cripta <span class="text-red-500">*</span></label>
                    <select name="crypt_type_id" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                        @foreach($types as $type)
                            <option value="{{ $type->id }}" {{ $crypt->crypt_type_id == $type->id ? 'selected' : '' }}>
                                {{ $type->name }} (Cap. {{ $type->default_capacity }}-{{ $type->max_capacity }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estado <span class="text-red-500">*</span></label>
                    <select name="crypt_status_id" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                        @foreach($statuses as $status)
                            <option value="{{ $status->id }}" {{ $crypt->crypt_status_id == $status->id ? 'selected' : '' }}>
                                {{ $status->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Capacidad Máxima <span class="text-red-500">*</span></label>
                    <input type="number" name="capacity" required min="1" max="10" value="{{ $crypt->capacity }}" 
                           class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Precio de Venta <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 pl-3 flex items-center text-slate-500 text-sm">$</span>
                        <input type="number" name="price" required step="0.01" min="0" value="{{ $crypt->price }}" 
                               class="w-full pl-8 pr-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    </div>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Dimensiones</label>
                    <input type="text" name="dimensions" value="{{ $crypt->dimensions }}" 
                           class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" 
                           placeholder="Ej: 2.0 x 1.0 x 1.5 m">
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Tipo de Puerta</label>
                    <select name="door_type" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                        <option value="">Sin especificar</option>
                        <option value="marble" {{ $crypt->door_type == 'marble' ? 'selected' : '' }}>Mármol</option>
                        <option value="bronze" {{ $crypt->door_type == 'bronze' ? 'selected' : '' }}>Bronce</option>
                        <option value="glass" {{ $crypt->door_type == 'glass' ? 'selected' : '' }}>Vidrio</option>
                        <option value="stone" {{ $crypt->door_type == 'stone' ? 'selected' : '' }}>Piedra</option>
                        <option value="other" {{ $crypt->door_type == 'other' ? 'selected' : '' }}>Otro</option>
                    </select>
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-semibold text-slate-700 mb-1.5">Notas / Observaciones</label>
                    <textarea name="notes" rows="3" 
                              class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm"
                              placeholder="Detalles adicionales...">{{ $crypt->notes }}</textarea>
                </div>
            </div>

            {{-- Botones de acción --}}
            <div class="flex items-center justify-end space-x-3 pt-6 border-t border-slate-200">
                <a href="{{ route('inventory.crypts.show', $crypt) }}" 
                   class="px-5 py-2.5 border border-slate-300 text-slate-700 hover:bg-slate-50 text-sm font-medium rounded-lg transition-colors">
                    Cancelar
                </a>
                
                {{-- ✅ BOTÓN CORRECTO: Actualizar (no eliminar) --}}
                <button type="submit" 
                        class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-save mr-2"></i> Guardar Cambios
                </button>
            </div>
        </form>

        {{-- Botón de eliminar (separado, con confirmación) --}}
        <div class="mt-6 bg-red-50 border border-red-100 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div class="flex items-center space-x-3">
                    <i class="fa-solid fa-triangle-exclamation text-red-600 text-xl"></i>
                    <div>
                        <h4 class="text-sm font-semibold text-red-800">Zona de Peligro</h4>
                        <p class="text-xs text-red-700">Esta acción no se puede deshacer</p>
                    </div>
                </div>
                <form method="POST" action="{{ route('inventory.crypts.destroy', $crypt) }}" 
                      onsubmit="return confirm('¿Estás SEGURO de eliminar esta cripta? Esta acción no se puede deshacer.');">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="px-4 py-2 bg-red-600 hover:bg-red-700 text-white text-sm font-medium rounded-lg transition-colors">
                        <i class="fa-solid fa-trash mr-2"></i> Eliminar Cripta
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>