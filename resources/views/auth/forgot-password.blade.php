<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Recuperar Contraseña - SGIC 2.0</title>
    
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
                ¿Problemas para <br>
                <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 to-cyan-400">ingresar?</span>
            </h1>
            <p class="text-slate-300 text-lg max-w-md">
                No te preocupes, te enviaremos un enlace seguro para restablecer tu contraseña en segundos.
            </p>
        </div>

        <div class="relative z-10">
            <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl p-6 max-w-md">
                <div class="flex items-start space-x-4">
                    <div class="bg-emerald-500/20 p-3 rounded-lg">
                        <i class="fa-solid fa-envelope-circle-check text-emerald-400 text-xl"></i>
                    </div>
                    <div>
                        <h3 class="font-bold text-lg">Recuperación Segura</h3>
                        <p class="text-sm text-slate-300 mt-1">El enlace expira en 60 minutos y solo puede usarse una vez.</p>
                    </div>
                </div>
            </div>
            <p class="text-xs text-slate-500 mt-6">© 2026 SGIC SaaS Platform. Todos los derechos reservados.</p>
        </div>
    </div>

    <!-- Right Side: Forgot Password Form -->
    <div class="w-full lg:w-1/2 flex items-center justify-center p-8 bg-white relative overflow-y-auto">
        
        <!-- Mobile Logo (Visible only on mobile) -->
        <div class="absolute top-8 left-8 lg:hidden flex items-center space-x-2">
            <i class="fa-solid fa-church text-emerald-600 text-2xl"></i>
            <span class="text-xl font-bold text-slate-900">SGIC 2.0</span>
        </div>

        <div class="w-full max-w-md space-y-8 mt-16 lg:mt-0">
            <div class="text-center lg:text-left">
                <!-- Key Icon -->
                <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-full mb-4">
                    <i class="fa-solid fa-key text-emerald-600 text-2xl"></i>
                </div>
                <h2 class="text-3xl font-bold text-slate-900">¿Olvidaste tu contraseña?</h2>
                <p class="mt-2 text-sm text-slate-600">Ingresa tu correo y te enviaremos un enlace para restablecerla</p>
            </div>

            <!-- Session Status Message -->
            @if (session('status'))
                <div class="rounded-md bg-emerald-50 p-4 border border-emerald-200">
                    <div class="flex">
                        <div class="flex-shrink-0">
                            <i class="fa-solid fa-circle-check text-emerald-400"></i>
                        </div>
                        <div class="ml-3">
                            <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
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

            <!-- Forgot Password Form -->
            <form class="mt-8 space-y-6" action="{{ route('password.email') }}" method="POST" @submit="isLoading = true">
                @csrf

                <!-- Email Field -->
                <div>
                    <label for="email" class="block text-sm font-medium text-slate-700">
                        Correo Electrónico <span class="text-red-500">*</span>
                    </label>
                    <div class="mt-1 relative rounded-md shadow-sm">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fa-regular fa-envelope text-slate-400"></i>
                        </div>
                        <input 
                            id="email" 
                            name="email" 
                            type="email" 
                            autocomplete="email" 
                            required 
                            autofocus
                            value="{{ old('email') }}"
                            class="focus:ring-emerald-500 focus:border-emerald-500 block w-full pl-10 sm:text-sm border-slate-300 rounded-lg py-2.5 border bg-slate-50 transition-colors @error('email') border-red-300 text-red-900 placeholder-red-300 focus:outline-none focus:ring-red-500 focus:border-red-500 @enderror"
                            placeholder="tu@email.com"
                        >
                    </div>
                    @error('email')
                        <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                            <i class="fa-solid fa-circle-exclamation"></i>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                <!-- Submit Button & Back Link -->
                <div class="flex items-center justify-between">
                    <a href="{{ route('login') }}" class="text-sm font-medium text-slate-600 hover:text-emerald-600 transition-colors flex items-center">
                        <i class="fa-solid fa-arrow-left mr-2"></i> Volver al login
                    </a>

                    <button 
                        type="submit" 
                        :disabled="isLoading"
                        class="group relative flex justify-center py-3 px-6 border border-transparent text-sm font-medium rounded-lg text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all shadow-md hover:shadow-lg transform active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed"
                    >
                        <span x-show="!isLoading" class="flex items-center">
                            Enviar enlace
                        </span>
                        <span x-show="isLoading" class="flex items-center">
                            <i class="fa-solid fa-spinner fa-spin mr-2"></i> Enviando...
                        </span>
                    </button>
                </div>
            </form>

            <!-- Help Text -->
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-lg">
                <p class="text-xs text-blue-800">
                    <i class="fa-solid fa-info-circle mr-1"></i> 
                    <strong>Nota:</strong> El enlace de recuperación expirará en 60 minutos por seguridad. Revisa tu carpeta de spam si no recibes el correo.
                </p>
            </div>
        </div>
    </div>
</body>
</html>