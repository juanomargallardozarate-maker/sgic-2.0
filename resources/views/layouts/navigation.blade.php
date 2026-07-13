<header class="h-16 bg-white shadow-sm flex items-center justify-between px-6 border-b border-slate-100 flex-shrink-0">
    <!-- Botón Menu Mobile -->
    <button @click="sidebarOpen = !sidebarOpen" class="md:hidden text-slate-500 hover:text-slate-700 focus:outline-none">
        <i class="fa-solid fa-bars text-xl"></i>
    </button>

    <!-- Título de Página -->
    <div class="hidden md:block">
        <h2 class="text-xl font-bold text-slate-800">{{ $title ?? 'Dashboard' }}</h2>
        <p class="text-xs text-slate-500">{{ now()->locale('es')->isoFormat('dddd, D [de] MMMM YYYY') }}</p>
    </div>
    
    <div class="flex items-center space-x-3">
        <!-- Global Search -->
        <div class="hidden md:flex relative">
            <input type="text" placeholder="Buscar..." class="pl-10 pr-4 py-2 border border-slate-200 rounded-lg text-sm focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 w-64 bg-slate-50">
            <i class="fa-solid fa-search absolute left-3 top-2.5 text-slate-400"></i>
        </div>

        <!-- Notificaciones -->
        <button class="relative p-2 text-slate-400 hover:text-slate-600 transition-colors hover:bg-slate-100 rounded-lg">
            <i class="fa-regular fa-bell text-xl"></i>
            <span class="absolute top-1 right-1 h-2 w-2 bg-red-500 rounded-full"></span>
        </button>
        
        <!-- User Dropdown -->
        <div class="relative" x-data="{ open: false }">
            <button @click="open = !open" class="flex items-center space-x-2 p-2 hover:bg-slate-100 rounded-lg transition-colors">
                <div class="h-8 w-8 rounded-full bg-gradient-to-br from-emerald-500 to-cyan-600 flex items-center justify-center text-white font-bold text-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 2)) }}
                </div>
                <span class="hidden md:block text-sm font-medium text-slate-700">{{ auth()->user()->name }}</span>
                <i class="fa-solid fa-chevron-down text-xs text-slate-400"></i>
            </button>
            
            <!-- Dropdown Menu -->
            <div x-show="open" 
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-75"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.outside="open = false"
                 class="absolute right-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-slate-200 py-1 z-50">
                
                <a href="{{ route('profile.edit') }}" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-user mr-2 text-slate-400"></i> Mi Perfil
                </a>
                
                <a href="#" class="block px-4 py-2 text-sm text-slate-700 hover:bg-slate-100 transition-colors">
                    <i class="fa-solid fa-gear mr-2 text-slate-400"></i> Configuración
                </a>
                
                <div class="border-t border-slate-200 my-1"></div>
                
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-2 text-sm text-red-600 hover:bg-red-50 transition-colors">
                        <i class="fa-solid fa-right-from-bracket mr-2"></i> Cerrar Sesión
                    </button>
                </form>
            </div>
        </div>
    </div>
</header>