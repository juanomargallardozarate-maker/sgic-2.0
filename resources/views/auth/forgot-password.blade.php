<x-guest-layout>
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-emerald-100 rounded-full mb-4">
                <i class="fa-solid fa-key text-emerald-600 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900">¿Olvidaste tu contraseña?</h2>
            <p class="text-sm text-slate-600 mt-2">
                Ingresa tu correo electrónico y te enviaremos un enlace para restablecerla
            </p>
        </div>

        {{-- Session Status --}}
        @if (session('status'))
            <div class="mb-4 bg-emerald-50 border-l-4 border-emerald-500 p-4 rounded-r-lg">
                <div class="flex items-start">
                    <i class="fa-solid fa-circle-check text-emerald-500 mt-0.5 mr-3"></i>
                    <p class="text-sm font-medium text-emerald-800">{{ session('status') }}</p>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
            @csrf

            {{-- Email --}}
            <div>
                <x-input-label for="email" :value="__('Correo Electrónico')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="tu@email.com" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="flex items-center justify-between mt-6">
                <a href="{{ route('login') }}" class="text-sm text-emerald-600 hover:text-emerald-500 transition-colors">
                    <i class="fa-solid fa-arrow-left mr-1"></i> Volver al login
                </a>

                <x-primary-button class="ml-4">
                    Enviar enlace
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>