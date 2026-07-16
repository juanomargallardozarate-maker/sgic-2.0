<x-app-layout>
    <x-slot name="title">Editar Sección</x-slot>

    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('inventory.hierarchy.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Editar Sección {{ $section->code }}</h2>
                <p class="text-sm text-slate-500 mt-1">Modifica la información de la sección</p>
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

    <form method="POST" action="{{ route('inventory.hierarchy.sections.update', $section) }}" class="max-w-3xl mx-auto">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 space-y-6">
            {{-- Información Básica --}}
            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-map-pin text-emerald-600 mr-2"></i>
                    Información de la Sección
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Código <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="code" 
                               value="{{ old('code', $section->code) }}" 
                               required 
                               class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm uppercase" 
                               placeholder="Ej: A, B, SAN_PEDRO">
                        <p class="text-xs text-slate-500 mt-1">Código único para identificar la sección</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name', $section->name) }}" 
                               required 
                               class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" 
                               placeholder="Ej: Sección San Pedro">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Descripción</label>
                        <textarea name="description" 
                                  rows="3" 
                                  class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" 
                                  placeholder="Descripción opcional de la sección...">{{ old('description', $section->description) }}</textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Orden de Visualización</label>
                        <input type="number" 
                               name="order" 
                               value="{{ old('order', $section->order) }}" 
                               min="0" 
                               class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" 
                               placeholder="0">
                        <p class="text-xs text-slate-500 mt-1">Número para ordenar las secciones (menor = primero)</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estado</label>
                        <div class="flex items-center mt-2">
                            <input type="checkbox" 
                                   name="is_active" 
                                   id="is_active"
                                   value="1"
                                   {{ old('is_active', $section->is_active) ? 'checked' : '' }}
                                   class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500">
                            <label for="is_active" class="ml-2 text-sm text-slate-700">Sección activa</label>
                        </div>
                        <p class="text-xs text-slate-500 mt-1">Las secciones inactivas no estarán disponibles para nuevos bloques</p>
                    </div>
                </div>
            </div>

            {{-- Información de bloques asociados --}}
            @if($section->blocks->count() > 0)
                <div class="border-t border-slate-200 pt-6">
                    <h4 class="font-semibold text-slate-700 mb-3">Bloques Asociados ({{ $section->blocks->count() }})</h4>
                    <div class="space-y-2">
                        @foreach($section->blocks as $block)
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                        <i class="fa-solid fa-building text-indigo-600 text-sm"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-700 text-sm">{{ $block->code }} - {{ $block->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $block->levels->count() }} niveles</p>
                                    </div>
                                </div>
                                <a href="{{ route('inventory.hierarchy.blocks.edit', $block) }}" 
                                   class="text-xs text-indigo-600 hover:text-indigo-800 font-medium">
                                    Editar bloque →
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end space-x-3 mt-6">
            <a href="{{ route('inventory.hierarchy.index') }}" 
               class="inline-flex items-center px-5 py-2.5 border border-slate-300 shadow-sm text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-xmark mr-2"></i> Cancelar
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                <i class="fa-solid fa-save mr-2"></i> Actualizar Sección
            </button>
        </div>
    </form>
</x-app-layout>
