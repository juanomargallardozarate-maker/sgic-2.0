<x-app-layout>
    <x-slot name="title">Perfil de Usuario</x-slot>

    <x-slot name="header">
        <h2 class="text-xl font-bold text-slate-800">
            Perfil de Usuario
        </h2>
    </x-slot>

    <div class="space-y-6">
        {{-- Actualizar Información del Perfil --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-user text-emerald-600 mr-2"></i>
                Información del Perfil
            </h3>
            <form method="POST" action="{{ route('profile.update') }}" class="space-y-4">
                @csrf
                @method('PATCH')

                <div>
                    <x-input-label for="name" :value="__('Nombre')" />
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $user->name)" required autofocus autocomplete="name" />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="email" :value="__('Correo Electrónico')" />
                    <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email', $user->email)" required autocomplete="username" />
                    <x-input-error :messages="$errors->get('email')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end">
                    <x-primary-button>
                        Guardar
                    </x-primary-button>
                </div>
            </form>
        </div>

        {{-- Actualizar Contraseña --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-lock text-indigo-600 mr-2"></i>
                Actualizar Contraseña
            </h3>
            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                @method('PUT')

                <div>
                    <x-input-label for="current_password" :value="__('Contraseña Actual')" />
                    <x-text-input id="current_password" class="block mt-1 w-full" type="password" name="current_password" required autocomplete="current-password" />
                    <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password" :value="__('Nueva Contraseña')" />
                    <x-text-input id="password" class="block mt-1 w-full" type="password" name="password" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="password_confirmation" :value="__('Confirmar Contraseña')" />
                    <x-text-input id="password_confirmation" class="block mt-1 w-full" type="password" name="password_confirmation" required autocomplete="new-password" />
                    <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
                </div>

                <div class="flex items-center justify-end">
                    <x-primary-button>
                        Actualizar contraseña
                    </x-primary-button>
                </div>
            </form>
        </div>

        {{-- Eliminar Cuenta --}}
        <div class="bg-white rounded-xl shadow-sm border border-red-200 p-6">
            <h3 class="text-lg font-bold text-red-600 mb-4 flex items-center">
                <i class="fa-solid fa-triangle-exclamation mr-2"></i>
                Zona de Peligro
            </h3>
            <p class="text-sm text-slate-600 mb-4">
                Una vez que tu cuenta sea eliminada, todos sus recursos y datos serán eliminados permanentemente.
            </p>
            <form method="POST" action="{{ route('profile.destroy') }}" onsubmit="return confirm('¿Estás seguro de eliminar tu cuenta? Esta acción no se puede deshacer.')">
                @csrf
                @method('DELETE')
                <x-danger-button>
                    <i class="fa-solid fa-trash mr-2"></i> Eliminar cuenta
                </x-danger-button>
            </form>
        </div>
    </div>
</x-app-layout>