<x-app-layout>
    <x-slot name="title">Editar Bloque</x-slot>

    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('inventory.hierarchy.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Editar Bloque {{ $block->code }}</h2>
                <p class="text-sm text-slate-500 mt-1">Modifica la información del bloque</p>
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

    <form method="POST" action="{{ route('inventory.hierarchy.blocks.update', $block) }}" class="max-w-3xl mx-auto">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 space-y-6">
            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-building text-indigo-600 mr-2"></i>
                    Información del Bloque
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Sección <span class="text-red-500">*</span></label>
                        <select name="section_id" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                            @foreach($sections as $section)
                                <option value="{{ $section->id }}" {{ old('section_id', $block->section_id) == $section->id ? 'selected' : '' }}>
                                    {{ $section->code }} - {{ $section->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Código <span class="text-red-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $block->code) }}" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm uppercase">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $block->name) }}" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Descripción</label>
                        <textarea name="description" rows="3" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">{{ old('description', $block->description) }}</textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Orden</label>
                        <input type="number" name="order" value="{{ old('order', $block->order) }}" min="0" class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estado</label>
                        <div class="flex items-center mt-2">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $block->is_active) ? 'checked' : '' }} class="w-4 h-4 text-indigo-600 border-slate-300 rounded focus:ring-indigo-500">
                            <label for="is_active" class="ml-2 text-sm text-slate-700">Bloque activo</label>
                        </div>
                    </div>
                </div>
            </div>

            @if($block->levels->count() > 0)
                <div class="border-t border-slate-200 pt-6">
                    <h4 class="font-semibold text-slate-700 mb-3">Niveles Asociados ({{ $block->levels->count() }})</h4>
                    <div class="space-y-2">
                        @foreach($block->levels as $level)
                            <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                <div class="flex items-center space-x-3">
                                    <div class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center">
                                        <i class="fa-solid fa-layer-group text-purple-600 text-xs"></i>
                                    </div>
                                    <div>
                                        <p class="font-medium text-slate-700 text-sm">{{ $level->code }} - {{ $level->name }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('inventory.hierarchy.levels.edit', $level) }}" class="text-xs text-purple-600 hover:text-purple-800 font-medium">Editar nivel →</a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <div class="flex items-center justify-end space-x-3 mt-6">
            <a href="{{ route('inventory.hierarchy.index') }}" class="inline-flex items-center px-5 py-2.5 border border-slate-300 shadow-sm text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-xmark mr-2"></i> Cancelar
            </a>
            <button type="submit" class="inline-flex items-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                <i class="fa-solid fa-save mr-2"></i> Actualizar Bloque
            </button>
        </div>
    </form>
</x-app-layout>
