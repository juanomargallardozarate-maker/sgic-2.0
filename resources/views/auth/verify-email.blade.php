<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Verificar Email - SGIC 2.0</title>
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <!-- Alpine.js para interactividad -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>

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
<body class="bg-slate-50 h-screen flex overflow-hidden" x-data="{ isLoading: false }">

    <!-- Left Side: Branding & Value Prop (Hidden on Mobile) -->
    <div class="hidden lg:flex w-1/2 hero-pattern flex-col justify-between p-12 text-white relative overflow-hidden">
        <!-- Decorative Circles -->
        <div class="absolute top-0 right-0 -mr-20 -mt-20 w-96 h-96 bg-emerald-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob"></div>
        <div class="absolute bottom-0 left-0 -ml-20 -mb-20 w-96 h-96 bg-cyan-500 rounded-full mix-blend-multiply filter blur-3xl opacity-20 animate-blob animation-delay-2000"></div>

        <div class="relative z-10">
            <div class="flex items-center space-x-3 mb-8">
                <i class="fa-solid fa-church text-emerald-400 text-3xl"></i>
                <span class="text-2xl font-bold tracking-tight">SGIC 2.0</span>
            </div>
            <h1 class="text-4xl font-bold leading-tight mb-6">
                Verificación de <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400">Seguridad</span>
            </h1>
            <p class="text-slate-300 text-lg max-w-md">
                Para proteger tu cuenta, necesitamos verificar que este correo electrónico te pertenece.
            </p>
        </div>

        <div class="relative z-10">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 max-w-md">
                <div class="flex items-start space-x-4">
                    <div class="bg-emerald-500/20 p-3 rounded-lg">
                        <i class="fa-solid fa-envelope-circle-check text-emerald-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Email Verificado</h3>
                        <p class="text-sm text-slate-300 mt-1">Una vez verificado, podrás acceder a todas las funcionalidades de la plataforma.</p>
                    </div>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-6">© 2026 SGIC SaaS Platform. Todos los derechos reservados.</p>
        </div>
    </div>

    <!-- Right Side: Verify Email Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white relative overflow-y-auto">
        
        <!-- Mobile Logo (Visible only on mobile) -->
        <div class="absolute top-8 left-8 lg:hidden flex items-center space-x-2">
            <i class="fa-solid fa-church text-emerald-600 text-2xl"></i>
            <span class="text-xl font-bold text-slate-900">SGIC 2.0</span>
        </div>

        <div class="w-full max-w-md space-y-6 mt-16 lg:mt-0">
            <div class="text-center lg:text-left">
                <!-- Envelope Check Icon -->
                <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                    <i class="fa-solid fa-envelope-circle-check text-blue-600 text-2xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-slate-900">Verifica tu Email</h2>
                <p class="mt-2 text-sm text-slate-600">Hemos enviado un enlace de verificación a tu correo electrónico</p>
            </div>

            <!-- Session Status Message -->
            @if (session('status') == 'verification-link-sent')
                <div class="rounded-md bg-emerald-50 p-4 border border-emerald-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-emerald-800">¡Nuevo enlace de verificación enviado!</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- General Errors -->
            @if ($errors->any())
                <div class="rounded-md bg-red-50 p-4 border border-red-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-circle-exclamation text-red-400"></i>
                        </div>
                        <div class="ml-3">
                            <h3 class="text-sm font-medium text-red-800">Error de validación</h3>
                            <div class="mt-2 text-sm text-red-700">
                                <ul class="list-disc pl-5 space-y-1">
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Instructions -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-6">
                <h3 class="font-semibold text-slate-800 mb-3 flex items-center">
                    <i class="fa-solid fa-list-check text-emerald-600 mr-2"></i>
                    Pasos a seguir:
                </h3>
                <ol class="space-y-2 text-sm text-slate-600">
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-xs font-semibold mr-2">1</span>
                        Revisa tu bandeja de entrada
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-xs font-semibold mr-2">2</span>
                        Busca el correo de verificación de SGIC 2.0
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-xs font-semibold mr-2">3</span>
                        Haz clic en el enlace de verificación
                    </li>
                    <li class="flex items-start">
                        <span class="flex-shrink-0 w-5 h-5 bg-emerald-100 text-emerald-600 rounded-full flex items-center justify-center text-xs font-semibold mr-2">4</span>
                        ¡Listo! Tu cuenta estará verificada
                    </li>
                </ol>
            </div>

            <!-- Resend Button -->
            <div class="flex flex-col items-center space-y-4 pt-4">
                <form method="POST" action="{{ route('verification.send') }}" @submit="isLoading = true">
                    @csrf
                    <button 
                        type="submit" 
                        :disabled="isLoading"
                        class="group relative flex justify-center py-3 px-6 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all shadow-md hover:shadow-lg transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!isLoading" class="flex items-center">
                            <i class="fa-solid fa-paper-plane mr-2"></i> Reenviar email de verificación
                        </span>
                        <span x-show="isLoading" class="flex items-center">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Enviando...
                        </span>
                    </button>
                </form>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-slate-600 hover:text-emerald-600 transition-colors flex items-center font-medium">
                        <i class="fa-solid fa-arrow-right-from-bracket mr-2"></i> Cerrar sesión
                    </button>
                </form>
            </div>

            <!-- Help Text -->
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-xs text-blue-800">
                    <i class="fa-solid fa-info-circle mr-1"></i> 
                    <strong>Nota:</strong> Si no recibes el correo en unos minutos, revisa tu carpeta de spam o solicita un nuevo enlace. El enlace expira en 60 minutos.
                </p>
            </div>
        </div>
    </div>
</body>
</html>