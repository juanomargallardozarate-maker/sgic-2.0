<x-app-layout>
    <x-slot name="title">Nuevo Cliente</x-slot>

    <div class="py-8">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Encabezado -->
            <div class="mb-6">
                <a href="{{ route('commercial.customers.index') }}" 
                   class="inline-flex items-center text-sm text-slate-500 hover:text-slate-700 mb-3">
                    <i class="fa-solid fa-arrow-left mr-2"></i>
                    Volver al listado
                </a>
                <h2 class="text-2xl font-bold leading-7 text-slate-900 sm:text-3xl">
                    <i class="fa-solid fa-user-plus mr-3 text-emerald-600"></i>
                    Registrar Nuevo Cliente
                </h2>
                <p class="mt-1 text-sm text-slate-500">
                    Ingresa la información del cliente. Los datos sensibles (RFC, CURP) serán encriptados.
                </p>
            </div>

            <!-- Formulario -->
            <form action="{{ route('commercial.customers.store') }}" method="POST" class="space-y-6">
                @csrf

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h3 class="text-lg font-semibold text-slate-900">
                            <i class="fa-solid fa-id-card mr-2 text-emerald-600"></i>
                            Información Básica
                        </h3>
                    </div>
                    
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Tipo de Cliente -->
                        <div>
                            <label for="type" class="block text-sm font-medium text-slate-700 mb-1">
                                Tipo de Cliente <span class="text-red-500">*</span>
                            </label>
                            <select name="type" id="type" required 
                                    class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('type') border-red-500 @enderror">
                                <option value="">Seleccionar...</option>
                                <option value="persona_fisica" {{ old('type') === 'persona_fisica' ? 'selected' : '' }}>Persona Física</option>
                                <option value="persona_moral" {{ old('type') === 'persona_moral' ? 'selected' : '' }}>Empresa</option>
                            </select>
                            @error('type')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Nombre / Razón Social -->
                        <div>
                            <label for="name" class="block text-sm font-medium text-slate-700 mb-1">
                                <span x-text="document.getElementById('type').value === 'persona_moral' ? 'Razón Social' : 'Nombre Completo'">Nombre Completo</span> 
                                <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="name" 
                                   id="name" 
                                   value="{{ old('name') }}" 
                                   required 
                                   maxlength="255"
                                   placeholder="Nombre completo o razón social"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('name') border-red-500 @enderror">
                            @error('name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- RFC -->
                        <div>
                            <label for="rfc" class="block text-sm font-medium text-slate-700 mb-1">
                                RFC <span class="text-red-500">*</span>
                            </label>
                            <input type="text" 
                                   name="rfc" 
                                   id="rfc" 
                                   value="{{ old('rfc') }}" 
                                   required 
                                   maxlength="13"
                                   placeholder="RFC sin espacios"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('rfc') border-red-500 @enderror uppercase">
                            @error('rfc')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-slate-500">
                                <i class="fa-solid fa-lock mr-1"></i>
                                Será encriptado automáticamente
                            </p>
                        </div>

                        <!-- CURP -->
                        <div>
                            <label for="curp" class="block text-sm font-medium text-slate-700 mb-1">
                                CURP <span class="text-slate-400">(Opcional)</span>
                            </label>
                            <input type="text" 
                                   name="curp" 
                                   id="curp" 
                                   value="{{ old('curp') }}" 
                                   maxlength="18"
                                   placeholder="CURP sin espacios"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('curp') border-red-500 @enderror uppercase">
                            @error('curp')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="mt-1 text-xs text-slate-500">
                                <i class="fa-solid fa-lock mr-1"></i>
                                Será encriptado automáticamente
                            </p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h3 class="text-lg font-semibold text-slate-900">
                            <i class="fa-solid fa-address-book mr-2 text-emerald-600"></i>
                            Información de Contacto
                        </h3>
                    </div>
                    
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Email -->
                        <div>
                            <label for="email" class="block text-sm font-medium text-slate-700 mb-1">
                                Correo Electrónico <span class="text-slate-400">(Opcional)</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   id="email" 
                                   value="{{ old('email') }}" 
                                   maxlength="255"
                                   placeholder="correo@ejemplo.com"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('email') border-red-500 @enderror">
                            @error('email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Teléfono -->
                        <div>
                            <label for="phone" class="block text-sm font-medium text-slate-700 mb-1">
                                Teléfono <span class="text-slate-400">(Opcional)</span>
                            </label>
                            <input type="text" 
                                   name="phone" 
                                   id="phone" 
                                   value="{{ old('phone') }}" 
                                   maxlength="20"
                                   placeholder="(55) 1234-5678"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('phone') border-red-500 @enderror">
                            @error('phone')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Celular -->
                        <div>
                            <label for="mobile" class="block text-sm font-medium text-slate-700 mb-1">
                                Celular <span class="text-slate-400">(Opcional)</span>
                            </label>
                            <input type="text" 
                                   name="mobile" 
                                   id="mobile" 
                                   value="{{ old('mobile') }}" 
                                   maxlength="20"
                                   placeholder="55 1234 5678"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('mobile') border-red-500 @enderror">
                            @error('mobile')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h3 class="text-lg font-semibold text-slate-900">
                            <i class="fa-solid fa-location-dot mr-2 text-emerald-600"></i>
                            Domicilio
                        </h3>
                    </div>
                    
                    <div class="p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Dirección (Calle y número) -->
                        <div class="md:col-span-2">
                            <label for="address" class="block text-sm font-medium text-slate-700 mb-1">
                                Calle y Número <span class="text-slate-400">(Opcional)</span>
                            </label>
                            <input type="text" 
                                   name="address" 
                                   id="address" 
                                   value="{{ old('address') }}" 
                                   maxlength="500"
                                   placeholder="Calle, número exterior, número interior..."
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('address') border-red-500 @enderror">
                            @error('address')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Colonia -->
                        <div>
                            <label for="colonia" class="block text-sm font-medium text-slate-700 mb-1">
                                Colonia <span class="text-slate-400">(Opcional)</span>
                            </label>
                            <input type="text" 
                                   name="colonia" 
                                   id="colonia" 
                                   value="{{ old('colonia') }}" 
                                   maxlength="150"
                                   placeholder="Colonia o barrio"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('colonia') border-red-500 @enderror">
                            @error('colonia')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Ciudad -->
                        <div>
                            <label for="ciudad" class="block text-sm font-medium text-slate-700 mb-1">
                                Ciudad <span class="text-slate-400">(Opcional)</span>
                            </label>
                            <input type="text" 
                                   name="ciudad" 
                                   id="ciudad" 
                                   value="{{ old('ciudad') }}" 
                                   maxlength="100"
                                   placeholder="Ciudad o municipio"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('ciudad') border-red-500 @enderror">
                            @error('ciudad')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Estado -->
                        <div>
                            <label for="estado" class="block text-sm font-medium text-slate-700 mb-1">
                                Estado <span class="text-slate-400">(Opcional)</span>
                            </label>
                            <input type="text" 
                                   name="estado" 
                                   id="estado" 
                                   value="{{ old('estado') }}" 
                                   maxlength="100"
                                   placeholder="Estado o entidad federativa"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('estado') border-red-500 @enderror">
                            @error('estado')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Código Postal -->
                        <div>
                            <label for="codigo_postal" class="block text-sm font-medium text-slate-700 mb-1">
                                Código Postal <span class="text-slate-400">(Opcional)</span>
                            </label>
                            <input type="text" 
                                   name="codigo_postal" 
                                   id="codigo_postal" 
                                   value="{{ old('codigo_postal') }}" 
                                   maxlength="10"
                                   placeholder="Ej: 06600"
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('codigo_postal') border-red-500 @enderror">
                            @error('codigo_postal')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-200 bg-slate-50">
                        <h3 class="text-lg font-semibold text-slate-900">
                            <i class="fa-solid fa-file-lines mr-2 text-emerald-600"></i>
                            Documentación y Notas
                        </h3>
                    </div>
                    
                    <div class="p-6 space-y-6">
                        <!-- URL INE -->
                        <div>
                            <label for="ine_url" class="block text-sm font-medium text-slate-700 mb-1">
                                URL de INE <span class="text-slate-400">(Opcional)</span>
                            </label>
                            <input type="text" 
                                   name="ine_url" 
                                   id="ine_url" 
                                   value="{{ old('ine_url') }}" 
                                   maxlength="500"
                                   placeholder="https://..."
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('ine_url') border-red-500 @enderror">
                            @error('ine_url')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- URL Comprobante de Domicilio -->
                        <div>
                            <label for="proof_of_address_url" class="block text-sm font-medium text-slate-700 mb-1">
                                URL de Comprobante de Domicilio <span class="text-slate-400">(Opcional)</span>
                            </label>
                            <input type="text" 
                                   name="proof_of_address_url" 
                                   id="proof_of_address_url" 
                                   value="{{ old('proof_of_address_url') }}" 
                                   maxlength="500"
                                   placeholder="https://..."
                                   class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('proof_of_address_url') border-red-500 @enderror">
                            @error('proof_of_address_url')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Notas -->
                        <div>
                            <label for="notes" class="block text-sm font-medium text-slate-700 mb-1">
                                Notas Adicionales <span class="text-slate-400">(Opcional)</span>
                            </label>
                            <textarea name="notes" 
                                      id="notes" 
                                      rows="3" 
                                      maxlength="1000"
                                      placeholder="Información adicional relevante..."
                                      class="w-full rounded-lg border-slate-300 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
                            @error('notes')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <!-- Botones de acción -->
                <div class="flex items-center justify-end space-x-4">
                    <a href="{{ route('commercial.customers.index') }}" 
                       class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-lg shadow-sm text-sm font-medium text-slate-700 bg-white hover:bg-slate-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        Cancelar
                    </a>
                    <button type="submit" 
                            class="inline-flex items-center px-6 py-2 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500">
                        <i class="fa-solid fa-save mr-2"></i>
                        Guardar Cliente
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Actualizar label de nombre según tipo de cliente
        document.getElementById('type').addEventListener('change', function() {
            const label = this.parentElement.nextElementSibling.querySelector('label span:first-child');
            if (this.value === 'persona_moral') {
                label.textContent = 'Razón Social ';
            } else {
                label.textContent = 'Nombre Completo ';
            }
        });
    </script>
</x-app-layout>
