<aside 
    :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
    class="fixed inset-y-0 left-0 z-40 w-64 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out md:relative md:translate-x-0 flex flex-col shadow-xl"
>
    
    {{-- Logo Area --}}
    <div class="h-16 flex items-center px-6 bg-slate-950 border-b border-slate-800 flex-shrink-0">
        <i class="fa-solid fa-church text-emerald-500 text-2xl mr-3"></i>
        <div>
            <span class="text-white font-bold text-xl tracking-tight">SGIC 2.0</span>
            <div class="text-[10px] text-emerald-400 font-semibold tracking-widest uppercase">
                @if(auth()->user()->hasRole('super_admin'))
                    SuperAdmin
                @elseif(auth()->user()->tenant)
                    {{ auth()->user()->tenant->name }}
                @else
                    Dashboard
                @endif
            </div>
        </div>
    </div>

    {{-- User Info --}}
    <div class="p-4 border-b border-slate-800 bg-slate-900/50 flex-shrink-0">
        <div class="flex items-center">
            <div class="h-10 w-10 rounded-full bg-gradient-to-br from-emerald-500 to-cyan-600 flex items-center justify-center text-white font-bold">
                {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
            </div>
            <div class="ml-3">
                <div class="font-medium text-white truncate text-sm">{{ auth()->user()->name }}</div>
                <div class="text-xs text-emerald-400 mt-0.5 flex items-center">
                    <i class="fa-solid fa-shield-halved text-[8px] mr-1"></i>
                    {{ auth()->user()->roles->first()?->name ?? 'Usuario' }}
                </div>
            </div>
        </div>
    </div>

    {{-- Navigation --}}
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
        
        @if(auth()->user()->hasRole('super_admin'))
            {{-- ============================================ --}}
            {{-- MENÚ SUPERADMIN --}}
            {{-- ============================================ --}}
            <div class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Principal</div>
            
            <a href="{{ route('dashboard') }}" 
               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white transition-colors' }}">
                <i class="fa-solid fa-chart-line mr-3 text-lg w-6 text-center {{ request()->routeIs('dashboard') ? 'text-white' : 'group-hover:text-white' }}"></i>
                Dashboard Global
            </a>
            
            <a href="{{ route('super-admin.tenants.index') }}" 
               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('super-admin.tenants.*') ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white transition-colors' }}">
                <i class="fa-solid fa-building mr-3 text-lg w-6 text-center {{ request()->routeIs('super-admin.tenants.*') ? 'text-white' : 'group-hover:text-white' }}"></i>
                Gestión de Tenants
            </a>
            
            {{-- Enlaces Próximamente --}}
            <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-500 cursor-not-allowed opacity-60" title="Módulo en desarrollo">
                <i class="fa-solid fa-file-invoice-dollar mr-3 text-lg w-6 text-center"></i>
                <span>Facturación SaaS</span>
                <span class="ml-auto text-[9px] bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">Próximamente</span>
            </a>
            
            <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-500 cursor-not-allowed opacity-60" title="Módulo en desarrollo">
                <i class="fa-solid fa-users-gear mr-3 text-lg w-6 text-center"></i>
                <span>Soporte Técnico</span>
                <span class="ml-auto text-[9px] bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">Próximamente</span>
            </a>

            <div class="pt-4 mt-4 border-t border-slate-800">
                <div class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Sistema</div>
                
                <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-500 cursor-not-allowed opacity-60" title="Módulo en desarrollo">
                    <i class="fa-solid fa-server mr-3 text-lg w-6 text-center"></i>
                    <span>Infraestructura</span>
                    <span class="ml-auto text-[9px] bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">Próximamente</span>
                </a>
                
                <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-500 cursor-not-allowed opacity-60" title="Módulo en desarrollo">
                    <i class="fa-solid fa-shield-halved mr-3 text-lg w-6 text-center"></i>
                    <span>Auditoría Global</span>
                    <span class="ml-auto text-[9px] bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">Próximamente</span>
                </a>
                
                <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-500 cursor-not-allowed opacity-60" title="Módulo en desarrollo">
                    <i class="fa-solid fa-gear mr-3 text-lg w-6 text-center"></i>
                    <span>Configuración SaaS</span>
                    <span class="ml-auto text-[9px] bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">Próximamente</span>
                </a>
            </div>
        @else
            {{-- ============================================ --}}
            {{-- MENÚ TENANT (AdminCementerio, Administrativo, Operativo) --}}
            {{-- ============================================ --}}
            <div class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Principal</div>
            
            <a href="{{ route('dashboard') }}" 
               class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('dashboard') ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white transition-colors' }}">
                <i class="fa-solid fa-chart-line mr-3 text-lg w-6 text-center {{ request()->routeIs('dashboard') ? 'text-white' : 'group-hover:text-white' }}"></i>
                Dashboard
            </a>

            {{-- ============================================ --}}
            {{-- SECCIÓN INVENTARIO (con submenús) --}}
            {{-- ============================================ --}}
            <div class="pt-4 mt-4 border-t border-slate-800">
                <div class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Inventario</div>
                
                <a href="{{ route('inventory.crypts.index') }}" 
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('inventory.crypts.index') ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white transition-colors' }}">
                    <i class="fa-solid fa-layer-group mr-3 text-lg w-6 text-center {{ request()->routeIs('inventory.crypts.index') ? 'text-white' : 'group-hover:text-white' }}"></i>
                    Criptas
                </a>
                
                <a href="{{ route('inventory.crypts.map') }}" 
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('inventory.crypts.map') ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white transition-colors' }}">
                    <i class="fa-solid fa-map mr-3 text-lg w-6 text-center {{ request()->routeIs('inventory.crypts.map') ? 'text-white' : 'group-hover:text-white' }}"></i>
                    Mapa Visual
                </a>
                
                <a href="{{ route('inventory.crypts.import') }}" 
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('inventory.crypts.import') ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white transition-colors' }}">
                    <i class="fa-solid fa-file-csv mr-3 text-lg w-6 text-center {{ request()->routeIs('inventory.crypts.import') ? 'text-white' : 'group-hover:text-white' }}"></i>
                    Importar Masivo
                </a>
            </div>

            {{-- ============================================ --}}
            {{-- SECCIÓN COMERCIAL (Próximamente) --}}
            {{-- ============================================ --}}
            <div class="pt-4 mt-4 border-t border-slate-800">
                <div class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Comercial</div>
                
                <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-500 cursor-not-allowed opacity-60" title="Módulo en desarrollo">
                    <i class="fa-solid fa-users mr-3 text-lg w-6 text-center"></i>
                    <span>Clientes</span>
                    <span class="ml-auto text-[9px] bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">Próximamente</span>
                </a>
                
                <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-500 cursor-not-allowed opacity-60" title="Módulo en desarrollo">
                    <i class="fa-solid fa-file-contract mr-3 text-lg w-6 text-center"></i>
                    <span>Contratos</span>
                    <span class="ml-auto text-[9px] bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">Próximamente</span>
                </a>
            </div>

            {{-- ============================================ --}}
            {{-- SECCIÓN FINANZAS (Próximamente) --}}
            {{-- ============================================ --}}
            <div class="pt-4 mt-4 border-t border-slate-800">
                <div class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Finanzas</div>
                
                <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-500 cursor-not-allowed opacity-60" title="Módulo en desarrollo">
                    <i class="fa-solid fa-file-invoice-dollar mr-3 text-lg w-6 text-center"></i>
                    <span>Pagos</span>
                    <span class="ml-auto text-[9px] bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">Próximamente</span>
                </a>
                
                <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-500 cursor-not-allowed opacity-60" title="Módulo en desarrollo">
                    <i class="fa-solid fa-chart-pie mr-3 text-lg w-6 text-center"></i>
                    <span>Reportes</span>
                    <span class="ml-auto text-[9px] bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">Próximamente</span>
                </a>
            </div>

            {{-- ============================================ --}}
            {{-- SECCIÓN CONFIGURACIÓN --}}
            {{-- ============================================ --}}
            <div class="pt-4 mt-4 border-t border-slate-800">
                <div class="px-3 text-xs font-semibold text-slate-500 uppercase tracking-wider mb-2">Configuración</div>
                
                <a href="{{ route('profile.edit') }}" 
                   class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg {{ request()->routeIs('profile.*') ? 'bg-emerald-600 text-white shadow-md' : 'text-slate-400 hover:bg-slate-800 hover:text-white transition-colors' }}">
                    <i class="fa-solid fa-user mr-3 text-lg w-6 text-center {{ request()->routeIs('profile.*') ? 'text-white' : 'group-hover:text-white' }}"></i>
                    Mi Perfil
                </a>
                
                <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-500 cursor-not-allowed opacity-60" title="Módulo en desarrollo">
                    <i class="fa-solid fa-gear mr-3 text-lg w-6 text-center"></i>
                    <span>Ajustes del Cementerio</span>
                    <span class="ml-auto text-[9px] bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">Próximamente</span>
                </a>
                
                <a href="#" class="group flex items-center px-3 py-2.5 text-sm font-medium rounded-lg text-slate-500 cursor-not-allowed opacity-60" title="Módulo en desarrollo">
                    <i class="fa-solid fa-users-gear mr-3 text-lg w-6 text-center"></i>
                    <span>Usuarios</span>
                    <span class="ml-auto text-[9px] bg-slate-700 text-slate-400 px-1.5 py-0.5 rounded">Próximamente</span>
                </a>
            </div>
        @endif
    </nav>

    {{-- System Status Footer --}}
    <div class="border-t border-slate-800 p-4 bg-slate-950 flex-shrink-0">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs text-slate-400">Estado del Sistema</span>
            <span class="flex items-center text-xs text-emerald-400 font-medium">
                <span class="h-2 w-2 rounded-full bg-emerald-400 mr-1.5 animate-pulse"></span>
                Operativo
            </span>
        </div>
        <div class="text-xs text-slate-500">Uptime: 99.98% • Latencia: 142ms</div>
        
        {{-- Logout Button --}}
        <form method="POST" action="{{ route('logout') }}" class="mt-3">
            @csrf
            <button type="submit" class="w-full flex items-center justify-center px-4 py-2 border border-slate-700 rounded-lg text-sm text-slate-300 hover:bg-slate-800 transition-colors">
                <i class="fa-solid fa-right-from-bracket mr-2"></i> Cerrar Sesión
            </button>
        </form>
    </div>
</aside>