<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard SuperAdmin - SGIC 2.0</title>
    
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
        .gradient-indigo { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800" x-data="{ sidebarOpen: false }">

    <div class="flex h-screen overflow-hidden">

        <!-- Sidebar (Simplificado para el ejemplo, usa tu componente real si lo tienes) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
               class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out md:relative md:translate-x-0 flex flex-col shadow-xl">
            <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800">
                <i class="fa-solid fa-cloud text-indigo-500 text-2xl mr-3"></i>
                <span class="text-white font-bold text-xl tracking-tight">SGIC SaaS</span>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <a href="{{ route('dashboard') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg bg-indigo-600 text-white shadow-md">
                    <i class="fa-solid fa-chart-line mr-3 text-lg w-6 text-center"></i> Dashboard Global
                </a>
                <a href="{{ route('super-admin.tenants.index') }}" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-400 hover:bg-slate-800 hover:text-white transition-colors">
                    <i class="fa-solid fa-building mr-3 text-lg w-6 text-center"></i> Gestión de Tenants
                </a>
            </nav>
            <div class="border-t border-slate-800 p-4 bg-slate-950">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-slate-700 rounded-lg text-sm text-slate-300 hover:bg-slate-800 transition-colors">
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
                    <h2 class="text-xl font-bold text-slate-800">Dashboard Global</h2>
                    <p class="text-xs text-slate-500">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM YYYY') }}</p>
                </div>
                <div class="flex items-center space-x-3">
                    <div class="h-10 w-10 rounded-full bg-gradient-to-br from-indigo-500 to-purple-600 flex items-center justify-center text-white font-bold">
                        {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                    </div>
                </div>
            </header>

            <!-- Scrollable Content Area -->
            <div class="flex-1 overflow-y-auto p-6 scroll-smooth">
                
                <!-- Welcome Banner -->
                <div class="gradient-indigo rounded-2xl p-6 mb-6 text-white relative overflow-hidden">
                    <div class="absolute top-0 right-0 -mr-10 -mt-10 w-64 h-64 bg-white/10 rounded-full blur-3xl"></div>
                    <div class="absolute bottom-0 left-1/2 w-48 h-48 bg-purple-400/20 rounded-full blur-3xl"></div>
                    
                    <div class="relative z-10 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                        <div>
                            <h1 class="text-2xl font-bold mb-1">¡Buen día, {{ Auth::user()->name }}! 👋</h1>
                            <p class="text-indigo-100 text-sm">
                                Tu plataforma SaaS está operando normalmente. Tienes 
                                <span class="font-bold text-white">{{ count($stats['critical_alerts'] ?? []) }} alertas</span> y 
                                <span class="font-bold text-white">{{ $stats['subscription_expiring'] ?? 0 }} tenant(s)</span> con suscripción por vencer.
                            </p>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('super-admin.tenants.create') }}" class="bg-white text-indigo-600 hover:bg-indigo-50 px-4 py-2 rounded-lg text-sm font-bold transition-colors flex items-center">
                                <i class="fa-solid fa-plus mr-2"></i> Nuevo Tenant
                            </a>
                        </div>
                    </div>
                </div>

                <!-- KPI Cards -->
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-6">
                    <!-- Total Tenants -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 relative overflow-hidden hover:shadow-md transition-shadow">
                        <div class="absolute right-0 top-0 h-full w-1 bg-indigo-500"></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Total Tenants</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-2">{{ $stats['total_tenants'] ?? 0 }}</h3>
                            </div>
                            <div class="p-3 bg-indigo-50 rounded-lg text-indigo-600">
                                <i class="fa-solid fa-building text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Active Tenants -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 relative overflow-hidden hover:shadow-md transition-shadow">
                        <div class="absolute right-0 top-0 h-full w-1 bg-emerald-500"></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Tenants Activos</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-2">{{ $stats['active_tenants'] ?? 0 }}</h3>
                            </div>
                            <div class="p-3 bg-emerald-50 rounded-lg text-emerald-600">
                                <i class="fa-solid fa-circle-check text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- MRR -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 relative overflow-hidden hover:shadow-md transition-shadow">
                        <div class="absolute right-0 top-0 h-full w-1 bg-purple-500"></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-slate-500">MRR (Ingresos)</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-2">${{ number_format($stats['total_mrr'] ?? 0, 0) }}</h3>
                            </div>
                            <div class="p-3 bg-purple-50 rounded-lg text-purple-600">
                                <i class="fa-solid fa-sack-dollar text-xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Criptas -->
                    <div class="bg-white rounded-xl p-6 shadow-sm border border-slate-100 relative overflow-hidden hover:shadow-md transition-shadow">
                        <div class="absolute right-0 top-0 h-full w-1 bg-blue-500"></div>
                        <div class="flex justify-between items-start">
                            <div>
                                <p class="text-sm font-medium text-slate-500">Criptas Gestionadas</p>
                                <h3 class="text-3xl font-bold text-slate-800 mt-2">{{ number_format($stats['total_crypts'] ?? 0, 0) }}</h3>
                            </div>
                            <div class="p-3 bg-blue-50 rounded-lg text-blue-600">
                                <i class="fa-solid fa-layer-group text-xl"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Charts & Alerts Row -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                    <!-- Plan Distribution -->
                    <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                        <h3 class="font-bold text-slate-800 mb-4">Distribución por Plan</h3>
                        <div class="h-48 flex items-center justify-center">
                            <canvas id="planChart"></canvas>
                        </div>
                        <div class="mt-4 space-y-2">
                            @php
                                $planColors = ['basic' => 'bg-slate-400', 'professional' => 'bg-blue-500', 'enterprise' => 'bg-purple-500'];
                                $planNames = ['basic' => 'Básico', 'professional' => 'Profesional', 'enterprise' => 'Enterprise'];
                                $total = $stats['total_tenants'] ?? 1;
                            @endphp
                            @foreach(['basic', 'professional', 'enterprise'] as $plan)
                                @php $count = $stats['tenants_by_plan'][$plan] ?? 0; @endphp
                                <div class="flex items-center justify-between text-sm">
                                    <div class="flex items-center">
                                        <span class="w-3 h-3 rounded-full {{ $planColors[$plan] }} mr-2"></span>
                                        <span class="text-slate-600">{{ $planNames[$plan] }}</span>
                                    </div>
                                    <div>
                                        <span class="font-bold text-slate-800">{{ $count }}</span>
                                        <span class="text-slate-400 text-xs ml-1">({{ round(($count / $total) * 100) }}%)</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Critical Alerts -->
                    <div class="lg:col-span-2 bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                        <h3 class="font-bold text-slate-800 mb-4 flex items-center">
                            <i class="fa-solid fa-triangle-exclamation text-red-500 mr-2"></i> Alertas Críticas
                        </h3>
                        <div class="space-y-3">
                            @forelse($stats['critical_alerts'] ?? [] as $alert)
                                @php
                                    $alertColors = [
                                        'error' => ['bg' => 'bg-red-50', 'border' => 'border-red-100', 'text' => 'text-red-800', 'subtext' => 'text-red-600', 'icon' => 'text-red-500'],
                                        'warning' => ['bg' => 'bg-amber-50', 'border' => 'border-amber-100', 'text' => 'text-amber-800', 'subtext' => 'text-amber-600', 'icon' => 'text-amber-500']
                                    ];
                                    $colors = $alertColors[$alert['type']] ?? $alertColors['warning'];
                                @endphp
                                <div class="flex items-start p-3 {{ $colors['bg'] }} rounded-lg border {{ $colors['border'] }}">
                                    <div class="flex-shrink-0 mt-0.5">
                                        <i class="fa-solid fa-circle-exclamation {{ $colors['icon'] }}"></i>
                                    </div>
                                    <div class="ml-3 w-full">
                                        <p class="text-sm font-medium {{ $colors['text'] }}">{{ $alert['title'] }}</p>
                                        <p class="text-xs {{ $colors['subtext'] }} mt-0.5">{{ $alert['description'] }}</p>
                                        <div class="flex items-center justify-between mt-2">
                                            <span class="text-[10px] {{ $colors['subtext'] }}">{{ $alert['time'] }}</span>
                                            <a href="{{ route('super-admin.tenants.index') }}" class="text-xs font-semibold {{ $colors['text'] }} hover:underline">Gestionar</a>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="text-center py-8 text-slate-500">
                                    <i class="fa-solid fa-circle-check text-emerald-500 text-3xl mb-2"></i>
                                    <p>No hay alertas críticas en este momento.</p>
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>

            </div>
        </main>
    </div>

    <script>
        // Plan Distribution Chart
        const planCtx = document.getElementById('planChart').getContext('2d');
        new Chart(planCtx, {
            type: 'doughnut',
            data: {
                labels: ['Básico', 'Profesional', 'Enterprise'],
                datasets: [{
                    data: [
                        {{ $stats['tenants_by_plan']['basic'] ?? 0 }},
                        {{ $stats['tenants_by_plan']['professional'] ?? 0 }},
                        {{ $stats['tenants_by_plan']['enterprise'] ?? 0 }}
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
    </script>
</body>
</html>