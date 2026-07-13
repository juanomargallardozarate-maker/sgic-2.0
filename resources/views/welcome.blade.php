<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="SGIC 2.0 - Sistema de Gestión Integral de Criptas para Cementerios Modernos">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>Bienvenido a SGIC 2.0 - Sistema de Gestión Integral de Criptas</title>
    
    <!-- Tailwind CSS (CDN para desarrollo, en producción usar Vite) -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        body { 
            font-family: 'Inter', sans-serif; 
        }
        
        .hero-pattern {
            background-color: #0f172a;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%231e293b' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        
        .animate-blob {
            animation: blob 7s infinite;
        }
        
        .animation-delay-2000 {
            animation-delay: 2s;
        }
        
        .animation-delay-4000 {
            animation-delay: 4s;
        }
    </style>
    
    @if (file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-50 text-slate-800 flex flex-col min-h-screen">

    <!-- Navbar -->
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between h-16">
                <!-- Logo -->
                <div class="flex items-center">
                    <i class="fa-solid fa-church text-emerald-600 text-2xl mr-3"></i>
                    <span class="font-bold text-xl tracking-tight text-slate-900">SGIC 2.0</span>
                </div>
                
                <!-- Navigation Links -->
                <div class="flex items-center space-x-4">
                    @auth
                        <a href="{{ url('/dashboard') }}" class="text-sm font-medium text-slate-700 hover:text-emerald-600 transition-colors">
                            Dashboard
                        </a>
                    @else
                        <a href="#" class="text-sm font-medium text-slate-500 hover:text-emerald-600 transition-colors">Soporte</a>
                        <a href="#" class="text-sm font-medium text-slate-500 hover:text-emerald-600 transition-colors">Documentación</a>
                        <div class="h-6 w-px bg-slate-200 mx-2"></div>
                        <a href="{{ route('login') }}" class="text-sm font-medium text-slate-700 hover:text-emerald-600 transition-colors">
                            Staff Login
                        </a>
                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="text-sm font-medium bg-emerald-600 hover:bg-emerald-500 text-white px-4 py-2 rounded-lg transition-colors">
                                Registrarse
                            </a>
                        @endif
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-pattern text-white py-20 lg:py-28 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10 text-center">
            <!-- Badge -->
            <span class="inline-block py-1 px-3 rounded-full bg-emerald-500/20 text-emerald-300 text-xs font-semibold tracking-wide uppercase mb-6 border border-emerald-500/30">
                Plataforma SaaS v2.0
            </span>
            
            <!-- Main Heading -->
            <h1 class="text-4xl md:text-6xl font-bold tracking-tight mb-6">
                Gestión Integral para <br class="hidden md:block" />
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400">
                    Cementerios Modernos
                </span>
            </h1>
            
            <!-- Subtitle -->
            <p class="mt-4 max-w-2xl mx-auto text-xl text-slate-300 mb-10">
                Digitaliza tu inventario, automatiza la cobranza y garantiza el cumplimiento normativo 
                (NOM-013, CFDI 4.0) en una sola plataforma segura y multi-tenant.
            </p>
            
            <!-- Access Box -->
            <div class="max-w-md mx-auto bg-white/10 backdrop-blur-md border border-white/20 rounded-2xl p-6 shadow-2xl">
                <h3 class="text-lg font-semibold mb-4 text-left">Acceso al Sistema</h3>
                
                <!-- Tenant Selector -->
                <div class="space-y-4">
                    <div>
                        <label for="tenantSelect" class="block text-xs font-medium text-slate-300 mb-1">
                            Selecciona tu Cementerio
                        </label>
                        <select 
                            id="tenantSelect" 
                            class="w-full bg-slate-800 border border-slate-600 text-white text-sm rounded-lg focus:ring-emerald-500 focus:border-emerald-500 block p-2.5"
                        >
                            <option value="" disabled selected>-- Seleccionar --</option>
                            <option value="san-jose">Panteón San José</option>
                            <option value="eternidad">Jardines de la Eternidad</option>
                            <option value="municipal">Cementerio Municipal Norte</option>
                        </select>
                    </div>
                    
                    <!-- Login Button -->
                    <button 
                        onclick="redirectToTenant()" 
                        class="w-full bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-4 rounded-lg transition-all transform active:scale-95 flex items-center justify-center"
                    >
                        Ingresar al Portal 
                        <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>
                    
                    <!-- Divider -->
                    <div class="relative flex py-2 items-center">
                        <div class="flex-grow border-t border-slate-600"></div>
                        <span class="flex-shrink-0 mx-4 text-slate-400 text-xs">O ingresa tu subdominio</span>
                        <div class="flex-grow border-t border-slate-600"></div>
                    </div>

                    <!-- Subdomain Input -->
                    <div class="flex rounded-md shadow-sm">
                        <input 
                            type="text" 
                            id="subdomainInput"
                            placeholder="tucementerio" 
                            class="flex-1 min-w-0 block w-full px-3 py-2 rounded-l-lg border-slate-600 bg-slate-800 text-white placeholder-slate-400 focus:ring-emerald-500 focus:border-emerald-500 sm:text-sm border"
                        >
                        <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-slate-600 bg-slate-700 text-slate-300 sm:text-sm">
                            .sgic.mx
                        </span>
                    </div>
                    
                    <!-- Direct Login Link -->
                    <div class="text-center pt-2">
                        <a href="{{ route('login') }}" class="text-sm text-emerald-400 hover:text-emerald-300 transition-colors">
                            <i class="fa-solid fa-right-to-bracket mr-1"></i>
                            Acceso directo al login
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Decorative Blobs -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-emerald-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-96 h-96 bg-cyan-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-96 h-96 bg-purple-500 rounded-full mix-blend-multiply filter blur-3xl opacity-10 animate-blob animation-delay-4000"></div>
    </div>

    <!-- Features Grid (Based on PRD Epics) -->
    <div class="py-16 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center mb-12">
                <h2 class="text-base text-emerald-600 font-semibold tracking-wide uppercase">
                    Funcionalidades Clave
                </h2>
                <p class="mt-2 text-3xl leading-8 font-extrabold tracking-tight text-slate-900 sm:text-4xl">
                    Todo lo que necesitas para operar
                </p>
                <p class="mt-4 max-w-2xl mx-auto text-lg text-slate-600">
                    Basado en las 7 Reglas de Negocio críticas y cumplimiento normativo mexicano
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                
                <!-- Feature 1: Mapa Digital -->
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-100 hover:shadow-lg transition-shadow group">
                    <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-map-location-dot"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Mapa Digital Interactivo</h3>
                    <p class="text-slate-600 text-sm">
                        Visualiza la ocupación en tiempo real por secciones, bloques y niveles. 
                        Código de colores intuitivo para estados disponibles, ocupados y bloqueados.
                    </p>
                </div>

                <!-- Feature 2: Finanzas -->
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-100 hover:shadow-lg transition-shadow group">
                    <div class="w-12 h-12 bg-emerald-100 text-emerald-600 rounded-lg flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-file-invoice-dollar"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Finanzas & CFDI 4.0</h3>
                    <p class="text-slate-600 text-sm">
                        Gestión automática de adeudos, cálculo de intereses moratorios y timbrado 
                        de facturas electrónicas integrado directamente con el SAT.
                    </p>
                </div>

                <!-- Feature 3: App de Campo -->
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-100 hover:shadow-lg transition-shadow group">
                    <div class="w-12 h-12 bg-amber-100 text-amber-600 rounded-lg flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">App de Campo Offline</h3>
                    <p class="text-slate-600 text-sm">
                        PWA para operativos. Recibe órdenes de trabajo, toma evidencia fotográfica 
                        y captura firmas digitales incluso sin conexión a internet.
                    </p>
                </div>

                <!-- Feature 4: Cumplimiento -->
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-100 hover:shadow-lg transition-shadow group">
                    <div class="w-12 h-12 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Cumplimiento Normativo</h3>
                    <p class="text-slate-600 text-sm">
                        Auditoría inmutable de todas las acciones (RN-07), gestión de sucesiones 
                        legales y control sanitario de inhumaciones/exhumaciones.
                    </p>
                </div>

                <!-- Feature 5: Dashboard -->
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-100 hover:shadow-lg transition-shadow group">
                    <div class="w-12 h-12 bg-red-100 text-red-600 rounded-lg flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Dashboard Ejecutivo</h3>
                    <p class="text-slate-600 text-sm">
                        KPIs en tiempo real: tasa de ocupación, cartera vencida, ingresos mensuales 
                        y alertas críticas de contratos por vencer.
                    </p>
                </div>

                <!-- Feature 6: Portal Familias -->
                <div class="bg-slate-50 rounded-xl p-6 border border-slate-100 hover:shadow-lg transition-shadow group">
                    <div class="w-12 h-12 bg-indigo-100 text-indigo-600 rounded-lg flex items-center justify-center text-xl mb-4 group-hover:scale-110 transition-transform">
                        <i class="fa-solid fa-users"></i>
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Portal de Familias</h3>
                    <p class="text-slate-600 text-sm">
                        Autogestión para titulares. Consulta de ubicación, pago de mantenimientos 
                        en línea y solicitud de servicios funerarios.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Stats Section -->
    <div class="py-12 bg-gradient-to-r from-emerald-600 to-cyan-600 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 md:grid-cols-4 gap-8 text-center">
                <div>
                    <div class="text-4xl font-bold mb-2">99.5%</div>
                    <div class="text-emerald-100 text-sm">Uptime Garantizado</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">70%</div>
                    <div class="text-emerald-100 text-sm">Reducción Tiempo Admin</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">40%</div>
                    <div class="text-emerald-100 text-sm">Menos Morosidad</div>
                </div>
                <div>
                    <div class="text-4xl font-bold mb-2">100%</div>
                    <div class="text-emerald-100 text-sm">Cumplimiento CFDI</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Call to Action Footer -->
    <div class="bg-slate-900 text-white py-12 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center">
            <div class="mb-6 md:mb-0 text-center md:text-left">
                <h3 class="text-2xl font-bold">¿Listo para digitalizar tu cementerio?</h3>
                <p class="text-slate-400 mt-2">Únete a los administradores que ya confían en SGIC 2.0</p>
            </div>
            <div class="flex space-x-4">
                @auth
                    <a href="{{ url('/dashboard') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                        Ir al Dashboard
                    </a>
                @else
                    @if (Route::has('register'))
                        <a href="{{ route('register') }}" class="bg-emerald-600 hover:bg-emerald-500 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                            Solicitar Demo
                        </a>
                    @endif
                    <a href="{{ route('login') }}" class="bg-transparent border border-slate-600 hover:bg-slate-800 text-white font-bold py-3 px-6 rounded-lg transition-colors">
                        Acceder al Staff
                    </a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-slate-950 text-slate-400 py-8 border-t border-slate-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row justify-between items-center">
                <div class="mb-4 md:mb-0">
                    <div class="flex items-center justify-center md:justify-start">
                        <i class="fa-solid fa-church text-emerald-600 text-xl mr-2"></i>
                        <span class="font-bold text-lg text-white">SGIC 2.0</span>
                    </div>
                    <p class="text-sm mt-2">Sistema de Gestión Integral de Criptas</p>
                </div>
                <div class="text-sm text-center md:text-right">
                    <p>&copy; {{ date('Y') }} SGIC 2.0. Todos los derechos reservados.</p>
                    <p class="mt-1">
                        <a href="#" class="hover:text-emerald-400 transition-colors">Política de Privacidad</a> | 
                        <a href="#" class="hover:text-emerald-400 transition-colors">Términos de Servicio</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script>
        /**
         * Redirige al usuario al portal del tenant seleccionado
         */
        function redirectToTenant() {
            const select = document.getElementById('tenantSelect');
            const value = select.value;
            
            if (!value) {
                showToast('Por favor selecciona un cementerio de la lista.', 'warning');
                return;
            }

            // En producción, esto redirigiría al subdominio del tenant
            // window.location.href = `https://${value}.sgic.mx/login`;
            
            // Para desarrollo, redirigir al login genérico
            showToast(`Redirigiendo al portal de: ${select.options[select.selectedIndex].text}...`, 'success');
            
            setTimeout(() => {
                window.location.href = '{{ route("login") }}';
            }, 1500);
        }

        /**
         * Redirige usando el subdominio ingresado manualmente
         */
        document.getElementById('subdomainInput')?.addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                const subdomain = this.value.trim();
                if (subdomain) {
                    // En producción: window.location.href = `https://${subdomain}.sgic.mx/login`;
                    showToast(`Redirigiendo a: ${subdomain}.sgic.mx`, 'success');
                    setTimeout(() => {
                        window.location.href = '{{ route("login") }}';
                    }, 1500);
                } else {
                    showToast('Por favor ingresa un subdominio válido.', 'warning');
                }
            }
        });

        /**
         * Muestra un toast notification
         */
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed bottom-4 right-4 px-6 py-3 rounded-lg shadow-lg text-white z-50 transition-all transform translate-y-0`;
            
            const colors = {
                success: 'bg-emerald-600',
                warning: 'bg-amber-600',
                error: 'bg-red-600',
                info: 'bg-blue-600'
            };
            
            toast.classList.add(colors[type] || colors.info);
            toast.textContent = message;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.transform = 'translateY(100px)';
                toast.style.opacity = '0';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        /**
         * Auto-focus en el selector de tenants al cargar
         */
        document.addEventListener('DOMContentLoaded', function() {
            const tenantSelect = document.getElementById('tenantSelect');
            if (tenantSelect) {
                setTimeout(() => tenantSelect.focus(), 500);
            }
        });
    </script>
</body>
</html>