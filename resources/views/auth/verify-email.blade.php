<x-guest-layout>
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-blue-100 rounded-full mb-4">
                <i class="fa-solid fa-envelope-circle-check text-blue-600 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900">Verifica tu Email</h2>
            <p class="text-sm text-slate-600 mt-2">
                Hemos enviado un enlace de verificación a tu correo electrónico
            </p>
        </div>

        @if (session('status') == 'verification-link-sent')
            <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg">
                <div class="flex items-start">
                    <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 mr-3"></i>
                    <p class="text-sm font-medium text-emerald-800">
                        ¡Nuevo enlace de verificación enviado!
                    </p>
                </div>
            </div>
        @endif

        <div class="mt-4 flex flex-col items-center space-y-4">
            <form method="POST" action="{{ route('verification.send') }}">
                @csrf
                <x-primary-button>
                    Reenviar email de verificación
                </x-primary-button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="text-sm text-slate-600 hover:text-emerald-600 transition-colors">
                    Cerrar sesión
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>