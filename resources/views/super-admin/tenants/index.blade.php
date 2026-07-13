<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Gestión de Tenants - SGIC SuperAdmin</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
        .gradient-indigo { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
        .plan-basic { background: linear-gradient(135deg, #64748b, #475569); }
        .plan-professional { background: linear-gradient(135deg, #3b82f6, #6366f1); }
        .plan-enterprise { background: linear-gradient(135deg, #8b5cf6, #a855f7); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800" x-data="tenantsApp()">

    <!-- Mobile Header -->
    <div class="md:hidden bg-slate-900 text-white p-4 flex justify-between items-center sticky top-0 z-50">
        <div class="font-bold text-lg tracking-tight">
            <i class="fa-solid fa-cloud mr-2 text-indigo-400"></i>SGIC SuperAdmin
        </div>
        <button @click="sidebarOpen = !sidebarOpen" class="text-white focus:outline-none">
            <i class="fa-solid fa-bars text-xl"></i>
        </button>
    </div>

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out md:relative md:translate-x-0 flex flex-col shadow-xl">
            
            <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800">
                <i class="fa-solid fa-cloud text-indigo-500 text-2xl mr-3"></i>
                <div>
                    <span class="text-white font-bold text-xl tracking-tight">SGIC SaaS</span>
                    <div class="text-[10px] text-indigo-400 font-semibold tracking-widest uppercase">Control Center</div>
                </div>
            </div>

            <!-- User Info DINÁMICO -->
            <div class="p-4 border-b border-slate-800 bg-slate-900/50">
                <div class="flex items-center">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                    <div class="ml-3">
                        <div class="font-medium text-white truncate text-sm">{{ Auth::user()->name }}</div>
                        <div class="text-xs text-indigo-400 mt-0.5 flex items-center">
                            <i class="fa-solid fa-shield-halved text-[8px] mr-1"></i>Super Admin
                        </div>
                    </div>
                </div>
            </div>

            <!-- Navigation con RUTAS REALES -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <div class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Principal</div>
                
                <a href="{{ route('dashboard') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">
                    <i class="fa-solid fa-chart-line mr-3 text-lg w-6 text-center group-hover:text-white"></i>
                    Dashboard Global
                </a>
                <a href="{{ route('super-admin.tenants.index') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg bg-indigo-600 text-white shadow-md">
                    <i class="fa-solid fa-building mr-3 text-lg w-6 text-center"></i>
                    Gestión de Tenants
                    <span class="ml-auto bg-white/20 text-white py-0.5 px-2 rounded-full text-xs font-bold">{{ $totalTenants }}</span>
                </a>
                <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">
                    <i class="fa-solid fa-file-invoice-dollar mr-3 text-lg w-6 text-center group-hover:text-white"></i>
                    Facturación SaaS
                </a>
                <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">
                    <i class="fa-solid fa-users-gear mr-3 text-lg w-6 text-center group-hover:text-white"></i>
                    Soporte Técnico
                </a>

                <div class="pt-4 mt-4 border-t border-slate-800">
                    <div class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Sistema</div>
                    <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-server mr-3 text-lg w-6 text-center group-hover:text-white"></i>
                        Infraestructura
                    </a>
                    <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-shield-halved mr-3 text-lg w-6 text-center group-hover:text-white"></i>
                        Auditoría Global
                    </a>
                    <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">
                        <i class="fa-solid fa-gear mr-3 text-lg w-6 text-center group-hover:text-white"></i>
                        Configuración SaaS
                    </a>
                </div>
            </nav>

            <!-- System Status Footer -->
            <div class="border-t border-slate-800 p-4 bg-slate-950">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs text-slate-400">Estado del Sistema</span>
                    <span class="flex items-center text-xs text-emerald-400 font-medium">
                        <span class="h-2 w-2 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                        Operativo
                    </span>
                </div>
                <div class="text-xs text-slate-500">Uptime: 99.98% • Latencia: 142ms</div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full mt-3 flex items-center justify-center px-4 py-2 border border-slate-700 rounded-lg text-sm text-slate-300 hover:bg-slate-800 transition-colors">
                        <i class="fa-solid fa-right-from-bracket mr-2"></i> Cerrar Sesión
                    </button>
                </form>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 flex flex-col h-screen overflow-hidden bg-slate-50 relative">
            
            <!-- Top Bar -->
            <header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 z-10 border-b border-slate-100">
                <div>
                    <h2 class="text-xl font-bold text-slate-800">Gestión de Tenants</h2>
                    <p class="text-xs text-slate-500">Administra los cementerios clientes de la plataforma SaaS</p>
                </div>
                
                <div class="flex items-center space-x-3">
                    <div class="hidden md:flex relative">
                        <input type="text" x-model="searchQuery" @input.debounce.300ms="applyFilters()" 
                               placeholder="Buscar tenant, RFC, subdominio..." 
                               class="pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-indigo-500 focus:border-indigo-500 w-64 bg-slate-50">
                        <i class="fa-solid fa-search absolute left-3 top-2.5 text-slate-400"></i>
                    </div>

                    <button class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors hover:bg-slate-100 rounded-lg">
                        <i class="fa-regular fa-bell text-xl"></i>
                        <span class="absolute top-1 right-1 h-2 w-2 bg-red-500 rounded-full"></span>
                    </button>
                    
                    <a href="{{ route('super-admin.tenants.create') }}" 
                       class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-lg text-sm font-medium shadow-sm transition-colors flex items-center">
                        <i class="fa-solid fa-plus mr-2"></i> Nuevo Tenant
                    </a>
                </div>
            </header>

            <!-- Scrollable Content Area -->
            <div class="flex-1 overflow-y-auto p-6 scroll-smooth">
                
                <!-- KPI Cards con DATOS REALES -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Total Tenants -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 relative overflow-hidden hover:shadow-md transition-shadow">
                        <div class="absolute right-0 top-0 h-full w-1 bg-indigo-500"></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Tenants</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-2">{{ $totalTenants }}</h3>
                                <p class="text-xs text-emerald-600 mt-1 font-medium flex items-center">
                                    <i class="fa-solid fa-arrow-up mr-1"></i> Registrados en plataforma
                                </p>
                            </div>
                            <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
                                <i class="fa-solid fa-building text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between text-xs text-slate-500">
                            <span>Activos: {{ $activeTenants }}</span>
                            <span class="font-semibold text-indigo-600">{{ $totalTenants > 0 ? round(($activeTenants/$totalTenants)*100) : 0 }}% activos</span>
                        </div>
                    </div>

                    <!-- Active Tenants -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 relative overflow-hidden hover:shadow-md transition-shadow">
                        <div class="absolute right-0 top-0 h-full w-1 bg-emerald-500"></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Tenants Activos</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-2">{{ $activeTenants }}</h3>
                                <p class="text-xs text-slate-500 mt-1">Con suscripción vigente</p>
                            </div>
                            <div class="p-3 bg-emerald-50 rounded-lg text-emerald-600">
                                <i class="fa-solid fa-circle-check text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between text-xs text-slate-500">
                            <span>Suspendidos: {{ $suspendedTenants }}</span>
                            <span class="font-semibold text-emerald-600">{{ $totalTenants > 0 ? round(($activeTenants/$totalTenants)*100, 1) : 0 }}%</span>
                        </div>
                    </div>

                    <!-- MRR -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 relative overflow-hidden hover:shadow-md transition-shadow">
                        <div class="absolute right-0 top-0 h-full w-1 bg-purple-500"></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-slate-500">MRR Total</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-2">${{ number_format($totalMRR, 0) }}</h3>
                                <p class="text-xs text-emerald-600 mt-1 font-medium flex items-center">
                                    <i class="fa-solid fa-arrow-up mr-1"></i> Ingresos mensuales
                                </p>
                            </div>
                            <div class="p-3 bg-purple-50 rounded-lg text-purple-600">
                                <i class="fa-solid fa-sack-dollar text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between text-xs text-slate-500">
                            <span>ARR Proyectado</span>
                            <span class="font-semibold text-purple-600">${{ number_format($totalMRR * 12, 0) }}</span>
                        </div>
                    </div>

                    <!-- Criptas Managed -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 relative overflow-hidden hover:shadow-md transition-shadow">
                        <div class="absolute right-0 top-0 h-full w-1 bg-blue-500"></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Criptas Gestionadas</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-2">{{ number_format($totalCrypts, 0) }}</h3>
                                <p class="text-xs text-blue-600 mt-1 font-medium flex items-center">
                                    <i class="fa-solid fa-arrow-up mr-1"></i> Total en plataforma
                                </p>
                            </div>
                            <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
                                <i class="fa-solid fa-layer-group text-xl"></i>
                            </div>
                        </div>
                        <div class="mt-4 pt-3 border-t border-slate-100 flex justify-between text-xs text-slate-500">
                            <span>Por vencer</span>
                            <span class="font-semibold text-blue-600">{{ $expiringTenants }} tenants</span>
                        </div>
                    </div>
                </div>

                <!-- Charts & Alerts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Plan Distribution Chart -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="font-bold text-slate-800">Distribución por Plan</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Tenants activos</p>
                            </div>
                        </div>
                        <div class="h-48 flex items-center justify-center">
                            <canvas id="planChart"></canvas>
                        </div>
                        <div class="mt-4 space-y-2">
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full bg-slate-400 mr-2"></span>
                                    <span class="text-slate-600">Básico</span>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800">{{ $planCounts['basic'] ?? 0 }}</span>
                                    <span class="text-slate-400 text-xs ml-1">($1,500/mes)</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full bg-blue-500 mr-2"></span>
                                    <span class="text-slate-600">Profesional</span>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800">{{ $planCounts['professional'] ?? 0 }}</span>
                                    <span class="text-slate-400 text-xs ml-1">($3,500/mes)</span>
                                </div>
                            </div>
                            <div class="flex items-center justify-between text-sm">
                                <div class="flex items-center">
                                    <span class="w-3 h-3 rounded-full bg-purple-500 mr-2"></span>
                                    <span class="text-slate-600">Enterprise</span>
                                </div>
                                <div>
                                    <span class="font-bold text-slate-800">{{ $planCounts['enterprise'] ?? 0 }}</span>
                                    <span class="text-slate-400 text-xs ml-1">($8,000/mes)</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Health -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                        <div class="flex justify-between items-center mb-4">
                            <div>
                                <h3 class="font-bold text-slate-800">Salud de Suscripciones</h3>
                                <p class="text-xs text-slate-500 mt-0.5">Estado actual de todos los tenants</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <div class="flex items-center">
                                        <span class="h-2.5 w-2.5 rounded-full bg-emerald-500 mr-2"></span>
                                        <span class="text-sm font-medium text-slate-700">Activas</span>
                                    </div>
                                    <span class="text-sm font-bold text-slate-800">
                                        {{ $activeTenants }} <span class="text-slate-400 font-normal text-xs">/ {{ $totalTenants }} tenants</span>
                                    </span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $totalTenants > 0 ? ($activeTenants/$totalTenants)*100 : 0 }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <div class="flex items-center">
                                        <span class="h-2.5 w-2.5 rounded-full bg-amber-500 mr-2"></span>
                                        <span class="text-sm font-medium text-slate-700">Por Vencer (≤30 días)</span>
                                    </div>
                                    <span class="text-sm font-bold text-slate-800">
                                        {{ $expiringTenants }} <span class="text-slate-400 font-normal text-xs">tenants</span>
                                    </span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-amber-500 h-2 rounded-full" style="width: {{ $totalTenants > 0 ? ($expiringTenants/$totalTenants)*100 : 0 }}%"></div>
                                </div>
                            </div>

                            <div>
                                <div class="flex justify-between items-center mb-1.5">
                                    <div class="flex items-center">
                                        <span class="h-2.5 w-2.5 rounded-full bg-red-500 mr-2"></span>
                                        <span class="text-sm font-medium text-slate-700">Vencidas / Suspendidas</span>
                                    </div>
                                    <span class="text-sm font-bold text-slate-800">
                                        {{ $suspendedTenants }} <span class="text-slate-400 font-normal text-xs">tenants</span>
                                    </span>
                                </div>
                                <div class="w-full bg-slate-100 rounded-full h-2">
                                    <div class="bg-red-500 h-2 rounded-full" style="width: {{ $totalTenants > 0 ? ($suspendedTenants/$totalTenants)*100 : 0 }}%"></div>
                                </div>
                            </div>
                        </div>

                        <div class="mt-6 pt-4 border-t border-slate-100">
                            <h4 class="text-xs font-bold text-slate-500 uppercase tracking-wide mb-3">Requieren Acción Inmediata</h4>
                            <div class="space-y-2">
                                @forelse($tenantsData as $tenant)
                                    @if($tenant['status'] === 'expiring')
                                        <div class="flex items-center justify-between p-2 rounded-lg hover:bg-slate-50 transition-colors">
                                            <div class="flex items-center">
                                                <div class="h-8 w-8 rounded-lg flex items-center justify-center font-bold text-xs text-white plan-{{ $tenant['plan'] }}">
                                                    {{ $tenant['initials'] }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-medium text-slate-800">{{ $tenant['name'] }}</div>
                                                    <div class="text-xs text-slate-500">Vence en {{ $tenant['daysUntilExpiry'] }} días • Plan {{ ucfirst($tenant['plan']) }}</div>
                                                </div>
                                            </div>
                                            <a href="{{ route('super-admin.tenants.show', $tenant['id']) }}" 
                                               class="text-xs bg-amber-50 hover:bg-amber-100 text-amber-700 px-3 py-1.5 rounded-md font-medium transition-colors">
                                                Contactar
                                            </a>
                                        </div>
                                    @endif
                                @empty
                                    <p class="text-sm text-slate-500 text-center py-4">No hay tenants por vencer</p>
                                @endforelse
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Filters Bar -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-4 mb-6">
                    <form method="GET" action="{{ route('super-admin.tenants.index') }}" class="flex flex-wrap gap-3 items-center justify-between">
                        <div class="flex flex-wrap gap-3 items-center">
                            <span class="text-sm font-medium text-slate-500">Filtros:</span>
                            
                            <select name="plan" onchange="this.form.submit()" class="border border-slate-200 rounded-lg text-sm px-3 py-2 bg-slate-50 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Todos los Planes</option>
                                <option value="basic" {{ request('plan') === 'basic' ? 'selected' : '' }}>Básico</option>
                                <option value="professional" {{ request('plan') === 'professional' ? 'selected' : '' }}>Profesional</option>
                                <option value="enterprise" {{ request('plan') === 'enterprise' ? 'selected' : '' }}>Enterprise</option>
                            </select>

                            <select name="status" onchange="this.form.submit()" class="border border-slate-200 rounded-lg text-sm px-3 py-2 bg-slate-50 focus:ring-indigo-500 focus:border-indigo-500">
                                <option value="">Todos los Estados</option>
                                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Activos</option>
                                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendidos</option>
                            </select>
                        </div>
                    </form>
                </div>

                <!-- Tenants Table -->
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-slate-800">Listado de Cementerios (Tenants)</h3>
                            <p class="text-xs text-slate-500 mt-0.5">Mostrando {{ $tenants->count() }} de {{ $totalTenants }} tenants</p>
                        </div>
                    </div>
                    
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-slate-200">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-slate-500 uppercase tracking-wider">Tenant</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Plan</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Estado</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Configuración RN</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Métricas</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Acciones</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-slate-100">
                                @forelse($tenantsData as $tenant)
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-11 w-11 rounded-lg flex items-center justify-center text-white font-bold text-sm shadow-sm plan-{{ $tenant['plan'] }}">
                                                    {{ $tenant['initials'] }}
                                                </div>
                                                <div class="ml-3">
                                                    <div class="text-sm font-semibold text-slate-900">{{ $tenant['name'] }}</div>
                                                    <div class="text-xs text-slate-500 font-mono mt-0.5">{{ $tenant['subdomain'] }}.sgic.mx</div>
                                                    <div class="text-xs text-slate-400 mt-0.5">
                                                        <i class="fa-solid fa-id-card mr-1"></i>{{ $tenant['rfc'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col">
                                                <span class="px-2.5 py-1 text-xs font-semibold rounded-full text-white text-center plan-{{ $tenant['plan'] }}">
                                                    {{ ucfirst($tenant['plan']) }}
                                                </span>
                                                <span class="text-xs text-slate-500 mt-1 text-center">${{ number_format($tenant['mrr']) }}/mes</span>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex items-center">
                                                <div class="h-2.5 w-2.5 rounded-full mr-2
                                                    {{ $tenant['status'] === 'active' ? 'bg-emerald-500' : 
                                                       ($tenant['status'] === 'expiring' ? 'bg-amber-500' : 
                                                       ($tenant['status'] === 'expired' ? 'bg-red-500' : 'bg-slate-400')) }}">
                                                </div>
                                                <div>
                                                    <span class="text-sm font-medium text-slate-700">{{ $tenant['statusLabel'] }}</span>
                                                    <div class="text-xs text-slate-500 mt-0.5">
                                                        {{ $tenant['status'] === 'suspended' ? 'Suspendido manualmente' : 'Vence: ' . $tenant['expiresAt'] }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col space-y-1 text-xs">
                                                <div class="flex items-center text-slate-600">
                                                    <i class="fa-solid fa-clock text-indigo-400 mr-1.5 w-3"></i>
                                                    <span>Gracia: <span class="font-semibold text-slate-800">{{ $tenant['gracePeriod'] }} años</span></span>
                                                </div>
                                                <div class="flex items-center text-slate-600">
                                                    <i class="fa-solid fa-ban text-red-400 mr-1.5 w-3"></i>
                                                    <span>Bloqueo: <span class="font-semibold text-slate-800">{{ $tenant['blockMonths'] }} meses</span></span>
                                                </div>
                                                <div class="flex items-center text-slate-600">
                                                    <i class="fa-solid fa-percent text-amber-400 mr-1.5 w-3"></i>
                                                    <span>Interés: <span class="font-semibold text-slate-800">{{ number_format($tenant['interestRate'], 2) }}%</span></span>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <div class="flex flex-col space-y-1 text-xs">
                                                <div class="flex items-center text-slate-600">
                                                    <i class="fa-solid fa-layer-group text-blue-400 mr-1.5 w-3"></i>
                                                    <span><span class="font-semibold text-slate-800">{{ $tenant['crypts'] }}</span> criptas</span>
                                                </div>
                                                <div class="flex items-center text-slate-600">
                                                    <i class="fa-solid fa-users text-emerald-400 mr-1.5 w-3"></i>
                                                    <span><span class="font-semibold text-slate-800">{{ $tenant['users'] }}</span> usuarios</span>
                                                </div>
                                            </div>
                                        </td>

                                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                            <div class="flex items-center justify-end space-x-1">
                                                <a href="{{ route('super-admin.tenants.show', $tenant['id']) }}" 
                                                   class="p-2 text-indigo-600 hover:bg-indigo-50 rounded-md transition-colors" title="Ver Detalles">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>
                                                <a href="{{ route('super-admin.tenants.edit', $tenant['id']) }}" 
                                                   class="p-2 text-slate-600 hover:bg-slate-100 rounded-md transition-colors" title="Editar">
                                                    <i class="fa-solid fa-pen-to-square"></i>
                                                </a>
                                                @if($tenant['status'] === 'suspended')
                                                    <form method="POST" action="{{ route('super-admin.tenants.activate', $tenant['id']) }}" class="inline">
                                                        @csrf
                                                        <button type="submit" class="p-2 text-emerald-600 hover:bg-emerald-50 rounded-md transition-colors" title="Activar">
                                                            <i class="fa-solid fa-play"></i>
                                                        </button>
                                                    </form>
                                                @else
                                                    <form method="POST" action="{{ route('super-admin.tenants.suspend', $tenant['id']) }}" 
                                                          class="inline" onsubmit="return confirm('¿Suspender este tenant?')">
                                                        @csrf
                                                        <button type="submit" class="p-2 text-amber-600 hover:bg-amber-50 rounded-md transition-colors" title="Suspender">
                                                            <i class="fa-solid fa-pause"></i>
                                                        </button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="px-6 py-12 text-center">
                                            <i class="fa-solid fa-building text-4xl text-slate-300 mb-3"></i>
                                            <p class="text-slate-500">No hay tenants registrados</p>
                                            <a href="{{ route('super-admin.tenants.create') }}" class="text-indigo-600 hover:underline text-sm mt-2 inline-block">
                                                Crear el primer tenant
                                            </a>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    @if($tenants->hasPages())
                        <div class="bg-slate-50 px-6 py-3 border-t border-slate-200">
                            {{ $tenants->links() }}
                        </div>
                    @endif
                </div>

            </div>
        </main>
    </div>

    <!-- Toast Notification -->
    @if(session('success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             class="fixed bottom-6 right-6 z-50 max-w-md">
            <div class="bg-slate-900 text-white px-5 py-3 rounded-lg shadow-2xl flex items-center justify-between border border-slate-700">
                <div class="flex items-center">
                    <i class="fa-solid fa-circle-check text-emerald-400 mr-3"></i>
                    <span class="text-sm font-medium">{{ session('success') }}</span>
                </div>
                <button @click="show = false" class="ml-4 text-slate-400 hover:text-white">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
        </div>
    @endif

    <script>
        function tenantsApp() {
            return {
                sidebarOpen: true,
                searchQuery: '',
                
                applyFilters() {
                    const url = new URL(window.location);
                    url.searchParams.set('search', this.searchQuery);
                    window.location.href = url.toString();
                },

                init() {
                    // Plan Distribution Chart con DATOS REALES
                    const planCtx = document.getElementById('planChart').getContext('2d');
                    new Chart(planCtx, {
                        type: 'doughnut',
                        data: {
                            labels: ['Básico', 'Profesional', 'Enterprise'],
                            datasets: [{
                                data: [
                                    {{ $planCounts['basic'] ?? 0 }},
                                    {{ $planCounts['professional'] ?? 0 }},
                                    {{ $planCounts['enterprise'] ?? 0 }}
                                ],
                                backgroundColor: ['#94a3b8', '#3b82f6', '#8b5cf6'],
                                borderWidth: 0,
                                hoverOffset: 8
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            cutout: '70%',
                            plugins: { legend: { display: false } }
                        }
                    });
                }
            }
        }
    </script>
</body>
</html>