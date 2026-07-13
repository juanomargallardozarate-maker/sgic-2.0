<x-app-layout>
    <x-slot name="title">Editar Tenant: {{ $tenant->name }}</x-slot>

    <x-slot name="header">
        <div class="flex items-center space-x-4">
            <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                <i class="fa-solid fa-arrow-left text-lg"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-slate-800">
                    Editar Tenant: {{ $tenant->name }}
                </h2>
                <p class="text-sm text-slate-500 mt-1">
                    Modifica la información del tenant y su configuración
                </p>
            </div>
        </div>
    </x-slot>

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg">
            <div class="flex items-start">
                <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 mr-3"></i>
                <div>
                    <h3 class="text-sm font-semibold text-red-800">Se encontraron errores</h3>
                    <ul class="mt-2 text-sm text-red-700 list-disc list-inside space-y-1">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    @endif

    <form method="POST" action="{{ route('super-admin.tenants.update', $tenant) }}" class="space-y-6">
        @csrf
        @method('PUT')

        {{-- Información General --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-building text-emerald-600 mr-2"></i>
                Información General
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="name" :value="__('Nombre Comercial')" />
                    <x-text-input id="name" class="block mt-1 w-full" type="text" name="name" :value="old('name', $tenant->name)" required />
                    <x-input-error :messages="$errors->get('name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="rfc" :value="__('RFC (12 o 13 caracteres)')" />
                    <x-text-input id="rfc" class="block mt-1 w-full" type="text" name="rfc" :value="old('rfc', $tenant->rfc)" required maxlength="13" />
                    <x-input-error :messages="$errors->get('rfc')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="subdomain" :value="__('Subdominio')" />
                    <div class="flex rounded-lg shadow-sm">
                        <x-text-input id="subdomain" class="flex-1 rounded-l-lg" type="text" name="subdomain" :value="old('subdomain', $tenant->subdomain)" required />
                        <span class="inline-flex items-center px-3 rounded-r-lg border border-l-0 border-slate-300 bg-slate-50 text-slate-500 text-sm">.sgic.mx</span>
                    </div>
                    <x-input-error :messages="$errors->get('subdomain')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="plan" :value="__('Plan')" />
                    <select id="plan" name="plan" required class="block mt-1 w-full border-slate-300 rounded-lg focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500">
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->code }}" {{ old('plan', $tenant->plan) == $plan->code ? 'selected' : '' }}>
                                {{ $plan->name }} - ${{ number_format($plan->monthly_price ?? 0, 2) }}/mes
                            </option>
                        @endforeach
                    </select>
                    <x-input-error :messages="$errors->get('plan')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="subscription_months" :value="__('Extender Suscripción (meses)')" />
                    <x-text-input id="subscription_months" class="block mt-1 w-full" type="number" name="subscription_months" :value="old('subscription_months')" min="1" max="120" placeholder="Dejar vacío para mantener fecha actual" />
                    <x-input-error :messages="$errors->get('subscription_months')" class="mt-2" />
                    <p class="text-xs text-slate-500 mt-1">
                        <i class="fa-solid fa-circle-info mr-1"></i>
                        Fecha actual de vencimiento: {{ $tenant->subscription_ends_at?->format('d/m/Y') ?? 'No definida' }}
                    </p>
                </div>
            </div>
        </div>

        {{-- Configuración de Reglas de Negocio --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-sliders text-indigo-600 mr-2"></i>
                Configuración de Reglas de Negocio
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="grace_period_years" :value="__('Periodo de Gracia (años)')" />
                    <x-text-input id="grace_period_years" class="block mt-1 w-full" type="number" name="grace_period_years" :value="old('grace_period_years', $tenant->grace_period_years)" min="1" max="10" />
                    <x-input-error :messages="$errors->get('grace_period_years')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="debt_months_to_block" :value="__('Meses para Bloqueo')" />
                    <x-text-input id="debt_months_to_block" class="block mt-1 w-full" type="number" name="debt_months_to_block" :value="old('debt_months_to_block', $tenant->debt_months_to_block)" min="1" max="12" />
                    <x-input-error :messages="$errors->get('debt_months_to_block')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="moratorium_interest_rate" :value="__('Tasa de Interés Moratorio (0.00 a 0.10)')" />
                    <x-text-input id="moratorium_interest_rate" class="block mt-1 w-full" type="number" step="0.0001" name="moratorium_interest_rate" :value="old('moratorium_interest_rate', $tenant->moratorium_interest_rate)" min="0" max="0.10" />
                    <x-input-error :messages="$errors->get('moratorium_interest_rate')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="reservation_days" :value="__('Días de Reserva')" />
                    <x-text-input id="reservation_days" class="block mt-1 w-full" type="number" name="reservation_days" :value="old('reservation_days', $tenant->reservation_days)" min="1" max="90" />
                    <x-input-error :messages="$errors->get('reservation_days')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="reservation_deposit_percent" :value="('% Anticipo Reserva')" />
                    <x-text-input id="reservation_deposit_percent" class="block mt-1 w-full" type="number" step="0.01" name="reservation_deposit_percent" :value="old('reservation_deposit_percent', $tenant->reservation_deposit_percent)" min="0" max="100" />
                    <x-input-error :messages="$errors->get('reservation_deposit_percent')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="maintenance_grace_days" :value="__('Días de Gracia Mantenimiento')" />
                    <x-text-input id="maintenance_grace_days" class="block mt-1 w-full" type="number" name="maintenance_grace_days" :value="old('maintenance_grace_days', $tenant->maintenance_grace_days)" min="0" max="180" />
                    <x-input-error :messages="$errors->get('maintenance_grace_days')" class="mt-2" />
                </div>
            </div>
        </div>

        {{-- Datos del Cementerio --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6">
            <h3 class="text-lg font-bold text-slate-800 mb-4 flex items-center">
                <i class="fa-solid fa-map-location-dot text-blue-600 mr-2"></i>
                Datos del Cementerio
            </h3>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <x-input-label for="cemetery_name" :value="__('Nombre del Cementerio')" />
                    <x-text-input id="cemetery_name" class="block mt-1 w-full" type="text" name="cemetery_name" :value="old('cemetery_name', $tenant->cemetery?->name)" required />
                    <x-input-error :messages="$errors->get('cemetery_name')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="cemetery_address" :value="__('Dirección')" />
                    <x-text-input id="cemetery_address" class="block mt-1 w-full" type="text" name="cemetery_address" :value="old('cemetery_address', $tenant->cemetery?->address)" required />
                    <x-input-error :messages="$errors->get('cemetery_address')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="cemetery_municipality" :value="__('Municipio')" />
                    <x-text-input id="cemetery_municipality" class="block mt-1 w-full" type="text" name="cemetery_municipality" :value="old('cemetery_municipality', $tenant->cemetery?->municipality)" required />
                    <x-input-error :messages="$errors->get('cemetery_municipality')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="cemetery_state" :value="__('Estado')" />
                    <x-text-input id="cemetery_state" class="block mt-1 w-full" type="text" name="cemetery_state" :value="old('cemetery_state', $tenant->cemetery?->state)" required />
                    <x-input-error :messages="$errors->get('cemetery_state')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="cemetery_postal_code" :value="__('Código Postal')" />
                    <x-text-input id="cemetery_postal_code" class="block mt-1 w-full" type="text" name="cemetery_postal_code" :value="old('cemetery_postal_code', $tenant->cemetery?->postal_code)" required maxlength="5" />
                    <x-input-error :messages="$errors->get('cemetery_postal_code')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="cemetery_phone" :value="__('Teléfono')" />
                    <x-text-input id="cemetery_phone" class="block mt-1 w-full" type="text" name="cemetery_phone" :value="old('cemetery_phone', $tenant->cemetery?->phone)" />
                    <x-input-error :messages="$errors->get('cemetery_phone')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="cemetery_email" :value="__('Email')" />
                    <x-text-input id="cemetery_email" class="block mt-1 w-full" type="email" name="cemetery_email" :value="old('cemetery_email', $tenant->cemetery?->email)" />
                    <x-input-error :messages="$errors->get('cemetery_email')" class="mt-2" />
                </div>

                <div>
                    <x-input-label for="legal_representative" :value="__('Representante Legal')" />
                    <x-text-input id="legal_representative" class="block mt-1 w-full" type="text" name="legal_representative" :value="old('legal_representative', $tenant->cemetery?->legal_representative)" required />
                    <x-input-error :messages="$errors->get('legal_representative')" class="mt-2" />
                </div>

                {{-- ✅ CORRECCIÓN: Campo faltante que causaba validation.required --}}
                <div class="md:col-span-2">
                    <x-input-label for="legal_representative_rfc" :value="__('RFC Representante Legal (12 o 13 caracteres)')" />
                    <x-text-input id="legal_representative_rfc" class="block mt-1 w-full" type="text" name="legal_representative_rfc" :value="old('legal_representative_rfc', $tenant->cemetery?->legal_representative_rfc)" required maxlength="13" />
                    <x-input-error :messages="$errors->get('legal_representative_rfc')" class="mt-2" />
                </div>
            </div>
        </div>

        {{-- Botones de Acción --}}
        <div class="flex items-center justify-end space-x-3">
            <a href="{{ route('super-admin.tenants.show', $tenant) }}" class="inline-flex items-center px-5 py-2.5 border border-slate-300 shadow-sm text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50 transition-colors">
                <i class="fa-solid fa-xmark mr-2"></i> Cancelar
            </a>
            <x-primary-button>
                <i class="fa-solid fa-save mr-2"></i> Guardar Cambios
            </x-primary-button>
        </div>
    </form>
</x-app-layout>