<x-app-layout>
    <x-slot name="title">Editar Nivel</x-slot>

    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('inventory.hierarchy.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Editar Nivel {{ $level->code }}</h2>
                <p class="text-sm text-slate-500 mt-1">Modifica la información del nivel</p>
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

    <form method="POST" action="{{ route('inventory.hierarchy.levels.update', $level) }}" class="max-w-3xl mx-auto">
        @csrf
        @method('PUT')
        
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 space-y-6">
            <div>
                <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                    <i class="fa-solid fa-layer-group text-purple-600 mr-2"></i>
                    Información del Nivel
                </h3>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Bloque <span class="text-red-500">*</span></label>
                        <select name="block_id" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                            @foreach($blocks as $block)
                                <option value="{{ $block->id }}" {{ old('block_id', $level->block_id) == $block->id ? 'selected' : '' }}>
                                    {{ $block->section->code }} - {{ $block->code }} - {{ $block->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Código <span class="text-red-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $level->code) }}" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm uppercase">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $level->name) }}" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Orden de Altura <span class="text-red-500">*</span></label>
                        <input type="number" name="height_order" value="{{ old('height_order', $level->height_order) }}" min="1" required class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-purple-500 text-sm">
                        <p class="text-xs text-slate-500 mt-1">1 = nivel más bajo, números mayores = niveles superiores</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Estado</label>
                        <div class="flex items-center mt-2">
                            <input type="checkbox" name="is_active" id="is_active" value="1" {{ old('is_active', $level->is_active) ? 'checked' : '' }} class="w-4 h-4 text-purple-600 border-slate-300 rounded focus:ring-purple-500">
                            <label for="is_active" class="ml-2 text-sm text-slate-700">Nivel activo</label>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 mt-6">
            <a href="{{ route('inventory.hierarchy.index') }}" class="inline-flex items-center px-5 py-2.5 border border-slate-300 shadow-sm text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-xmark mr-2"></i> Cancelar
            </a>
            <button type="submit" class="inline-flex items-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-purple-600 hover:bg-purple-700 transition-colors">
                <i class="fa-solid fa-save mr-2"></i> Actualizar Nivel
            </button>
        </div>
    </form>
</x-app-layout>
