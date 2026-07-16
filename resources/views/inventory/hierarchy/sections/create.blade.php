<x-app-layout>
    <x-slot name="title">Crear Sección</x-slot>

    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('inventory.hierarchy.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-800">Crear Nueva Sección</h2>
                <p class="text-sm text-slate-500 mt-1">Define una nueva sección del cementerio</p>
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

    <form method="POST" action="{{ route('inventory.hierarchy.sections.store') }}" class="max-w-3xl mx-auto">
        @csrf
        
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
                               value="{{ old('code') }}" 
                               required 
                               class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm uppercase" 
                               placeholder="Ej: A, B, SAN_PEDRO">
                        <p class="text-xs text-slate-500 mt-1">Código único para identificar la sección</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre <span class="text-red-500">*</span></label>
                        <input type="text" 
                               name="name" 
                               value="{{ old('name') }}" 
                               required 
                               class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" 
                               placeholder="Ej: Sección San Pedro">
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Descripción</label>
                        <textarea name="description" 
                                  rows="3" 
                                  class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" 
                                  placeholder="Descripción opcional de la sección...">{{ old('description') }}</textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-semibold text-slate-700 mb-1.5">Orden de Visualización</label>
                        <input type="number" 
                               name="order" 
                               value="{{ old('order', 0) }}" 
                               min="0" 
                               class="w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm" 
                               placeholder="0">
                        <p class="text-xs text-slate-500 mt-1">Número para ordenar las secciones (menor = primero)</p>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center justify-end space-x-3 mt-6">
            <a href="{{ route('inventory.hierarchy.index') }}" 
               class="inline-flex items-center px-5 py-2.5 border border-slate-300 shadow-sm text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-xmark mr-2"></i> Cancelar
            </a>
            <button type="submit" 
                    class="inline-flex items-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 transition-colors">
                <i class="fa-solid fa-save mr-2"></i> Guardar Sección
            </button>
        </div>
    </form>
</x-app-layout>
