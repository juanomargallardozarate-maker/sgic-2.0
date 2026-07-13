<x-app-layout>
    <x-slot name="title">Dashboard - {{ $stats['tenant']?->name ?? 'SGIC 2.0' }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    Dashboard del Cementerio
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    {{ now()->locale('es')->isoFormat('dddd, D [de] MMMM YYYY') }}
                </p>
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-sm text-slate-600">
                    <i class="fa-solid fa-building mr-1 text-emerald-600"></i>
                    {{ $stats['tenant']?->name ?? 'Sin tenant' }}
                </span>
            </div>
        </div>
    </x-slot>

    {{-- Flash Messages --}}
    @if (session('success'))
        <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 mr-3"></i>
                <div>
                    <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                </div>
            </div>
        </div>
    @endif

    @if (session('error'))
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 mr-3"></i>
                <div>
                    <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                </div>
            </div>
        </div>
    @endif

    {{-- KPI Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
        {{-- Total Criptas --}}
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 relative overflow-hidden hover:shadow-md transition-shadow">
            <div class="absolute right-0 top-0 h-full w-1 bg-emerald-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-500">Total Criptas</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-2">
                        {{ number_format($stats['total_crypts'] ?? 0) }}
                    </h3>
                    <p class="text-xs text-emerald-600 mt-1 font-medium flex items-center">
                        <i class="fa-solid fa-layer-group mr-1"></i>
                        Inventario total
                    </p>
                </div>
                <div class="p-3 bg-emerald-50 rounded-lg text-emerald-600">
                    <i class="fa-solid fa-layer-group text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Ocupadas --}}
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 relative overflow-hidden hover:shadow-md transition-shadow">
            <div class="absolute right-0 top-0 h-full w-1 bg-red-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-500">Ocupadas</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-2">
                        {{ number_format($stats['occupied_crypts'] ?? 0) }}
                    </h3>
                    <p class="text-xs text-red-600 mt-1 font-medium flex items-center">
                        <i class="fa-solid fa-user mr-1"></i>
                        Con contrato activo
                    </p>
                </div>
                <div class="p-3 bg-red-50 rounded-lg text-red-600">
                    <i class="fa-solid fa-user text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Disponibles --}}
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 relative overflow-hidden hover:shadow-md transition-shadow">
            <div class="absolute right-0 top-0 h-full w-1 bg-blue-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-500">Disponibles</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-2">
                        {{ number_format($stats['available_crypts'] ?? 0) }}
                    </h3>
                    <p class="text-xs text-blue-600 mt-1 font-medium flex items-center">
                        <i class="fa-solid fa-check-circle mr-1"></i>
                        Para venta
                    </p>
                </div>
                <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
                    <i class="fa-solid fa-check-circle text-xl"></i>
                </div>
            </div>
        </div>

        {{-- Tasa de Ocupación --}}
        <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 relative overflow-hidden hover:shadow-md transition-shadow">
            <div class="absolute right-0 top-0 h-full w-1 bg-purple-500"></div>
            <div class="flex justify-between items-start">
                <div>
                    <p class="text-sm font-medium text-slate-500">Tasa de Ocupación</p>
                    <h3 class="text-3xl font-bold text-slate-800 mt-2">
                        {{ $stats['occupancy_rate'] ?? 0 }}%
                    </h3>
                    <p class="text-xs text-purple-600 mt-1 font-medium flex items-center">
                        <i class="fa-solid fa-chart-pie mr-1"></i>
                        Del inventario
                    </p>
                </div>
                <div class="p-3 bg-purple-50 rounded-lg text-purple-600">
                    <i class="fa-solid fa-chart-pie text-xl"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- Accesos Rápidos --}}
    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
        <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
            <i class="fa-solid fa-bolt text-amber-500 mr-2"></i>
            Accesos Rápidos
        </h3>
        <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
            <a href="#" class="flex flex-col items-center p-4 bg-slate-50 rounded-lg hover:bg-emerald-50 transition-colors group">
                <div class="h-12 w-12 rounded-lg bg-emerald-100 text-emerald-600 flex items-center justify-center mb-2 group-hover:bg-emerald-200 transition-colors">
                    <i class="fa-solid fa-map-location-dot text-xl"></i>
                </div>
                <span class="text-sm font-medium text-slate-700 text-center">Mapa de Criptas</span>
            </a>

            <a href="#" class="flex flex-col items-center p-4 bg-slate-50 rounded-lg hover:bg-blue-50 transition-colors group">
                <div class="h-12 w-12 rounded-lg bg-blue-100 text-blue-600 flex items-center justify-center mb-2 group-hover:bg-blue-200 transition-colors">
                    <i class="fa-solid fa-users text-xl"></i>
                </div>
                <span class="text-sm font-medium text-slate-700 text-center">Clientes</span>
            </a>

            <a href="#" class="flex flex-col items-center p-4 bg-slate-50 rounded-lg hover:bg-indigo-50 transition-colors group">
                <div class="h-12 w-12 rounded-lg bg-indigo-100 text-indigo-600 flex items-center justify-center mb-2 group-hover:bg-indigo-200 transition-colors">
                    <i class="fa-solid fa-file-contract text-xl"></i>
                </div>
                <span class="text-sm font-medium text-slate-700 text-center">Contratos</span>
            </a>

            <a href="#" class="flex flex-col items-center p-4 bg-slate-50 rounded-lg hover:bg-purple-50 transition-colors group">
                <div class="h-12 w-12 rounded-lg bg-purple-100 text-purple-600 flex items-center justify-center mb-2 group-hover:bg-purple-200 transition-colors">
                    <i class="fa-solid fa-file-invoice-dollar text-xl"></i>
                </div>
                <span class="text-sm font-medium text-slate-700 text-center">Pagos</span>
            </a>

            <a href="#" class="flex flex-col items-center p-4 bg-slate-50 rounded-lg hover:bg-amber-50 transition-colors group">
                <div class="h-12 w-12 rounded-lg bg-amber-100 text-amber-600 flex items-center justify-center mb-2 group-hover:bg-amber-200 transition-colors">
                    <i class="fa-solid fa-clipboard-list text-xl"></i>
                </div>
                <span class="text-sm font-medium text-slate-700 text-center">Órdenes de Trabajo</span>
            </a>

            <a href="#" class="flex flex-col items-center p-4 bg-slate-50 rounded-lg hover:bg-red-50 transition-colors group">
                <div class="h-12 w-12 rounded-lg bg-red-100 text-red-600 flex items-center justify-center mb-2 group-hover:bg-red-200 transition-colors">
                    <i class="fa-solid fa-chart-line text-xl"></i>
                </div>
                <span class="text-sm font-medium text-slate-700 text-center">Reportes</span>
            </a>
        </div>
    </div>

    {{-- Bienvenida --}}
    <div class="mt-6 bg-gradient-to-r from-emerald-600 to-cyan-600 rounded-xl p-6 text-white relative overflow-hidden">
        <div class="absolute top-0 right-0 -mr-10 -mt-10 w-48 h-48 bg-white/10 rounded-full blur-3xl"></div>
        <div class="relative z-10">
            <h3 class="text-xl font-bold mb-2">
                ¡Bienvenido, {{ Auth::user()->name }}! 👋
            </h3>
            <p class="text-emerald-100 text-sm">
                Tu panel de control está listo. Desde aquí puedes gestionar todas las operaciones de tu cementerio.
            </p>
        </div>
    </div>
</x-app-layout>