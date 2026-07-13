<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'SGIC 2.0') }} - {{ $title ?? 'Acceso' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:300,400,500,600,700&display=swap" rel="stylesheet" />
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        body { font-family: 'Inter', sans-serif; }
        .hero-pattern {
            background-color: #0f172a;
            background-image: url("data:image/svg+xml,%3Csvg width='60' height='60' viewBox='0 0 60 60' xmlns='http://www.w3.org/2000/svg'%3E%3Cg fill='none' fill-rule='evenodd'%3E%3Cg fill='%231e293b' fill-opacity='0.4'%3E%3Cpath d='M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E");
        }
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            33% { transform: translate(30px, -50px) scale(1.1); }
            66% { transform: translate(-20px, 20px) scale(0.9); }
        }
        .animate-blob { animation: blob 7s infinite; }
        .animation-delay-2000 { animation-delay: 2s; }
    </style>
</head>
<body class="min-h-screen bg-slate-50 flex items-center justify-center relative overflow-hidden">

    <!-- Decorative Background Blobs -->
    <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-emerald-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
    <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-96 h-96 bg-indigo-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>

    <!-- Logo Superior -->
    <div class="absolute top-6 left-6 md:top-10 md:left-10 z-10">
        <a href="/" class="flex items-center space-x-3 group">
            <i class="fa-solid fa-church text-emerald-600 text-3xl group-hover:text-emerald-700 transition-colors"></i>
            <div>
                <span class="font-bold text-xl tracking-tight text-slate-900">SGIC 2.0</span>
                <div class="text-[10px] text-slate-500 font-semibold tracking-widest uppercase">Sistema de Gestión</div>
            </div>
        </a>
    </div>

    <!-- Contenido Principal -->
    <div class="w-full max-w-md px-4 py-8 relative z-10">
        {{ $slot }}
    </div>

    <!-- Footer -->
    <div class="absolute bottom-4 left-0 right-0 text-center text-xs text-slate-500">
        <p>© {{ date('Y') }} SGIC 2.0 - Sistema de Gestión Integral de Criptas</p>
        <p class="mt-1">Versión 2.0.0 | Cumplimiento NOM-013, NOM-133, CFDI 4.0, LFPDPPP</p>
    </div>
</body>
</html>