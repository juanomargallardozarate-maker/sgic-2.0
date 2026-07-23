<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SGIC 2.0') }} - {{ $title ?? 'Dashboard' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net ">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href=" https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css ">

    <!-- Tailwind CSS CDN (forzado para evitar conflictos con Vite) -->
    <script src="https://cdn.tailwindcss.com "></script>

    <!-- Alpine.js -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs @3.x.x/dist/cdn.min.js"></script>

    <style>
        body { 
            font-family: 'Inter', sans-serif; 
        }
        [x-cloak] { display: none !important; }
        
        /* Scrollbar personalizado */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: #94a3b8; }
    </style>
</head>
<body class="bg-slate-50 text-slate-800 antialiased" x-data="{ sidebarOpen: false }">

    <div class="min-h-screen flex">

        <!-- Sidebar -->
        @include('layouts.sidebar')

        <!-- Contenido Principal -->
        <div class="flex-1 flex flex-col min-w-0">

            <!-- Topbar -->
            @include('layouts.navigation')

            <!-- Contenido de la Página -->
            <main class="flex-1 overflow-y-auto p-6">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-6 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg" role="alert">
                        <div class="flex items-start">
                            <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 mr-3"></i>
                            <div>
                                <p class="text-sm font-medium text-emerald-800">{{ session('success') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg" role="alert">
                        <div class="flex items-start">
                            <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 mr-3"></i>
                            <div>
                                <p class="text-sm font-medium text-red-800">{{ session('error') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                @if (session('warning'))
                    <div class="mb-6 bg-amber-50 border-l-4 border-amber-500 p-4 rounded-r-lg" role="alert">
                        <div class="flex items-start">
                            <i class="fa-solid fa-triangle-exclamation text-amber-500 mt-0.5 mr-3"></i>
                            <div>
                                <p class="text-sm font-medium text-amber-800">{{ session('warning') }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Breadcrumbs -->
                @if (isset($breadcrumbs))
                    <div class="mb-4">
                        {{ $breadcrumbs }}
                    </div>
                @endif

                <!-- Header de Página -->
                @if (isset($header))
                    <div class="mb-6 bg-white rounded-xl shadow-sm border border-slate-100 p-6">
                        {{ $header }}
                    </div>
                @endif

                <!-- Contenido Principal -->
                <div>
                    {{ $slot }}
                </div>
            </main>
        </div>
    </div>

    <!-- Overlay para Sidebar Mobile -->
    <div x-show="sidebarOpen" 
         x-transition:enter="transition-opacity ease-linear duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="transition-opacity ease-linear duration-300"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="fixed inset-0 bg-slate-900/50 z-30 md:hidden"
         @click="sidebarOpen = false">
    </div>
</body>
</html>
