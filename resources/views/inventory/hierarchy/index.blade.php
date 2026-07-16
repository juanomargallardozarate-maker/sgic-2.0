<x-app-layout>
    <x-slot name="title">Gestión de Jerarquía</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Gestión de Jerarquía del Cementerio</h2>
                <p class="text-sm text-slate-500 mt-1">Administra secciones, bloques y niveles</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('inventory.hierarchy.sections.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-plus mr-2"></i> Nueva Sección
                </a>
                <a href="{{ route('inventory.hierarchy.blocks.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-layer-group mr-2"></i> Nuevo Bloque
                </a>
                <a href="{{ route('inventory.hierarchy.levels.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-layer-group mr-2"></i> Nuevo Nivel
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Mensajes de éxito/error --}}
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 mr-3"></i>
                <div>
                    <h3 class="text-sm font-semibold text-emerald-800">Operación exitosa</h3>
                    <p class="text-sm text-emerald-700 mt-1">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 mr-3"></i>
                <div>
                    <h3 class="text-sm font-semibold text-red-800">Error</h3>
                    <p class="text-sm text-red-700 mt-1">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Tarjetas de estadísticas --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Total Secciones</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $totalSections }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-map-pin text-emerald-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Total Bloques</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $totalBlocks }}</p>
                </div>
                <div class="w-12 h-12 bg-indigo-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-building text-indigo-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Total Niveles</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $totalLevels }}</p>
                </div>
                <div class="w-12 h-12 bg-purple-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-layer-group text-purple-600 text-xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-slate-500 uppercase">Secciones Activas</p>
                    <p class="text-2xl font-bold text-slate-800 mt-1">{{ $activeSections }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-100 rounded-lg flex items-center justify-center">
                    <i class="fa-solid fa-circle-check text-amber-600 text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-6">
        <form method="GET" action="{{ route('inventory.hierarchy.index') }}" class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-64">
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}" 
                       placeholder="Buscar por código o nombre..."
                       class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
            </div>
            <div>
                <select name="status" class="px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <option value="">Todos los estados</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                    <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactivos</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-slate-800 hover:bg-slate-900 text-white text-sm font-medium rounded-lg transition-colors">
                <i class="fa-solid fa-search mr-2"></i> Filtrar
            </button>
            <a href="{{ route('inventory.hierarchy.index') }}" class="px-4 py-2 bg-white border border-slate-300 hover:bg-slate-50 text-slate-700 text-sm font-medium rounded-lg transition-colors">
                <i class="fa-solid fa-rotate-left mr-2"></i> Limpiar
            </a>
        </form>
    </div>

    {{-- Árbol de jerarquía --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
            <h3 class="text-lg font-bold text-slate-800">Árbol de Jerarquía</h3>
        </div>

        @if($sections->count() > 0)
            <div class="divide-y divide-slate-100">
                @foreach($sections as $section)
                    <div class="p-6">
                        {{-- SECCIÓN --}}
                        <div class="flex items-center justify-between mb-4">
                            <div class="flex items-center space-x-3">
                                <div class="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center">
                                    <i class="fa-solid fa-map-pin text-emerald-600"></i>
                                </div>
                                <div>
                                    <h4 class="font-bold text-slate-800">{{ $section->code }} - {{ $section->name }}</h4>
                                    @if($section->description)
                                        <p class="text-sm text-slate-500">{{ Str::limit($section->description, 80) }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="px-2 py-1 text-xs font-medium rounded-full {{ $section->is_active ? 'bg-emerald-100 text-emerald-700' : 'bg-slate-100 text-slate-700' }}">
                                    {{ $section->is_active ? 'Activa' : 'Inactiva' }}
                                </span>
                                <a href="{{ route('inventory.hierarchy.sections.edit', $section) }}" 
                                   class="p-2 text-slate-400 hover:text-indigo-600 transition-colors" title="Editar">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                                <form method="POST" action="{{ route('inventory.hierarchy.sections.destroy', $section) }}" 
                                      class="inline" 
                                      onsubmit="return confirm('¿Estás seguro de eliminar esta sección? Esta acción no se puede deshacer.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-2 text-slate-400 hover:text-red-600 transition-colors" title="Eliminar">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- BLOQUES --}}
                        @if($section->blocks->count() > 0)
                            <div class="ml-8 space-y-3">
                                @foreach($section->blocks as $block)
                                    <div class="flex items-center justify-between p-3 bg-slate-50 rounded-lg">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-8 h-8 bg-indigo-100 rounded-lg flex items-center justify-center">
                                                <i class="fa-solid fa-building text-indigo-600 text-sm"></i>
                                            </div>
                                            <div>
                                                <h5 class="font-semibold text-slate-700">{{ $block->code }} - {{ $block->name }}</h5>
                                                @if($block->description)
                                                    <p class="text-xs text-slate-500">{{ Str::limit($block->description, 50) }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="flex items-center space-x-2">
                                            <span class="px-2 py-1 text-xs font-medium rounded-full {{ $block->is_active ? 'bg-indigo-100 text-indigo-700' : 'bg-slate-100 text-slate-700' }}">
                                                {{ $block->is_active ? 'Activo' : 'Inactivo' }}
                                            </span>
                                            <a href="{{ route('inventory.hierarchy.blocks.edit', $block) }}" 
                                               class="p-1.5 text-slate-400 hover:text-indigo-600 transition-colors" title="Editar">
                                                <i class="fa-solid fa-pen text-sm"></i>
                                            </a>
                                            <form method="POST" action="{{ route('inventory.hierarchy.blocks.destroy', $block) }}" 
                                                  class="inline" 
                                                  onsubmit="return confirm('¿Estás seguro de eliminar este bloque? Esta acción no se puede deshacer.')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 transition-colors" title="Eliminar">
                                                    <i class="fa-solid fa-trash text-sm"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>

                                    {{-- NIVELES --}}
                                    @if($block->levels->count() > 0)
                                        <div class="ml-8 space-y-2">
                                            @foreach($block->levels as $level)
                                                <div class="flex items-center justify-between p-2.5 bg-white border border-slate-200 rounded-lg">
                                                    <div class="flex items-center space-x-3">
                                                        <div class="w-7 h-7 bg-purple-100 rounded-lg flex items-center justify-center">
                                                            <i class="fa-solid fa-layer-group text-purple-600 text-xs"></i>
                                                        </div>
                                                        <div>
                                                            <p class="font-medium text-slate-600 text-sm">{{ $level->code }} - {{ $level->name }}</p>
                                                            <p class="text-xs text-slate-400">Orden: {{ $level->height_order }}</p>
                                                        </div>
                                                    </div>
                                                    <div class="flex items-center space-x-2">
                                                        <span class="px-2 py-0.5 text-xs font-medium rounded-full {{ $level->is_active ? 'bg-purple-100 text-purple-700' : 'bg-slate-100 text-slate-700' }}">
                                                            {{ $level->is_active ? 'Activo' : 'Inactivo' }}
                                                        </span>
                                                        <a href="{{ route('inventory.hierarchy.levels.edit', $level) }}" 
                                                           class="p-1.5 text-slate-400 hover:text-purple-600 transition-colors" title="Editar">
                                                            <i class="fa-solid fa-pen text-xs"></i>
                                                        </a>
                                                        <form method="POST" action="{{ route('inventory.hierarchy.levels.destroy', $level) }}" 
                                                              class="inline" 
                                                              onsubmit="return confirm('¿Estás seguro de eliminar este nivel? Esta acción no se puede deshacer.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="p-1.5 text-slate-400 hover:text-red-600 transition-colors" title="Eliminar">
                                                                <i class="fa-solid fa-trash text-xs"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    @else
                                        <div class="ml-8 p-2 text-sm text-slate-400 italic">
                                            <i class="fa-solid fa-circle-info mr-1"></i> Sin niveles registrados
                                        </div>
                                    @endif
                                @endforeach
                            </div>
                        @else
                            <div class="ml-8 p-3 text-sm text-slate-400 italic">
                                <i class="fa-solid fa-circle-info mr-1"></i> Sin bloques registrados
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="p-12 text-center">
                <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <i class="fa-solid fa-sitemap text-slate-400 text-2xl"></i>
                </div>
                <h3 class="text-lg font-semibold text-slate-700">No hay secciones registradas</h3>
                <p class="text-slate-500 mt-1 mb-4">Comienza creando la primera sección del cementerio</p>
                <a href="{{ route('inventory.hierarchy.sections.create') }}" 
                   class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-plus mr-2"></i> Crear Primera Sección
                </a>
            </div>
        @endif
    </div>
</x-app-layout>
