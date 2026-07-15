<x-app-layout>
    <x-slot name="title">Inventario de Criptas</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Inventario de Criptas</h2>
                <p class="text-sm text-slate-500 mt-1">Gestión de la jerarquía y estados del cementerio</p>
            </div>
            <div class="flex items-center space-x-3">
                {{-- ✅ NUEVO: Botón Importar Masivo --}}
                <a href="{{ route('inventory.crypts.import') }}" class="inline-flex items-center px-4 py-2 bg-amber-600 hover:bg-amber-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-file-csv mr-2"></i> Importar Masivo
                </a>
                
                <a href="{{ route('inventory.crypts.map') }}" class="inline-flex items-center px-4 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-sm font-medium rounded-lg transition-colors">
                    <i class="fa-solid fa-map mr-2"></i> Ver Mapa Visual
                </a>
                <a href="{{ route('inventory.crypts.create') }}" class="inline-flex items-center px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors shadow-sm">
                    <i class="fa-solid fa-plus mr-2"></i> Nueva Cripta
                </a>
            </div>
        </div>
    </x-slot>

    {{-- Filtros --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-6">
        <form method="GET" action="{{ route('inventory.crypts.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Buscar Código</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Ej: 01" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Estado</label>
                <select name="status" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <option value="">Todos los estados</option>
                    @foreach($statuses as $status)
                        <option value="{{ $status->id }}" {{ request('status') == $status->id ? 'selected' : '' }}>{{ $status->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-500 uppercase mb-1">Tipo</label>
                <select name="type" class="w-full px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 text-sm">
                    <option value="">Todos los tipos</option>
                    @foreach($types as $type)
                        <option value="{{ $type->id }}" {{ request('type') == $type->id ? 'selected' : '' }}>{{ $type->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-slate-800 hover:bg-slate-900 text-white font-medium py-2 px-4 rounded-lg transition-colors text-sm">
                    <i class="fa-solid fa-filter mr-2"></i> Filtrar
                </button>
            </div>
        </form>
    </div>

    {{-- Tabla de Criptas --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-slate-200">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Código</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Ubicación</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tipo</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Estado</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Capacidad</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Precio</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-slate-500 uppercase tracking-wider">Acciones</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-100">
                    @forelse ($crypts as $crypt)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-bold text-slate-800">{{ $crypt->code }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">
                                Sec. {{ $crypt->level->block->section->code ?? 'N/A' }} / Blq. {{ $crypt->level->block->code ?? 'N/A' }} / Nv. {{ $crypt->level->code ?? 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $crypt->cryptType->name ?? 'N/A' }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium" style="background-color: {{ $crypt->cryptStatus->color ?? '#cbd5e1' }}20; color: {{ $crypt->cryptStatus->color ?? '#64748b' }};">
                                    <span class="h-2 w-2 rounded-full mr-1.5" style="background-color: {{ $crypt->cryptStatus->color ?? '#64748b' }};"></span>
                                    {{ $crypt->cryptStatus->name ?? 'N/A' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-600">{{ $crypt->current_occupancy }} / {{ $crypt->capacity }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-slate-800">${{ number_format($crypt->price, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('inventory.crypts.show', $crypt) }}" class="text-indigo-600 hover:text-indigo-900 mr-3" title="Ver"><i class="fa-solid fa-eye"></i></a>
                                <a href="{{ route('inventory.crypts.edit', $crypt) }}" class="text-slate-600 hover:text-slate-900 mr-3" title="Editar"><i class="fa-solid fa-pen-to-square"></i></a>
                                <form action="{{ route('inventory.crypts.destroy', $crypt) }}" method="POST" class="inline" onsubmit="return confirm('¿Estás seguro de eliminar esta cripta?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-600 hover:text-red-900" title="Eliminar"><i class="fa-solid fa-trash"></i></button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-slate-500">
                                <i class="fa-solid fa-layer-group text-4xl text-slate-300 mb-3"></i>
                                <p class="text-sm">No se encontraron criptas. Comienza creando la jerarquía.</p>
                                <a href="{{ route('inventory.crypts.create') }}" class="text-emerald-600 hover:underline text-sm mt-2 inline-block">Crear la primera cripta</a>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-slate-50 px-6 py-4 border-t border-slate-200">
            {{ $crypts->links() }}
        </div>
    </div>
</x-app-layout>