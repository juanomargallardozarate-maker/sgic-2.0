<x-guest-layout>
    <div class="bg-white rounded-2xl shadow-xl p-8 w-full max-w-md">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-100 rounded-full mb-4">
                <i class="fa-solid fa-shield-halved text-amber-600 text-2xl"></i>
            </div>
            <h2 class="text-2xl font-bold text-slate-900">Confirmar Contraseña</h2>
            <p class="text-sm text-slate-600 mt-2">
                Por seguridad, confirma tu contraseña para continuar
            </p>
        </div>

        <form method="POST" action="{{ route('password.confirm') }}" class="space-y-4">
            @csrf

            {{-- Password --}}
            <div>
                <x-input-label for="password" :value="__('Contraseña')" />
                <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="current-password" placeholder="••••••••" />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center justify-end mt-6">
                <x-primary-button>
                    Confirmar
                </x-primary-button>
            </div>
        </form>
    </div>
</x-guest-layout>