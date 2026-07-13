<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Crear Nuevo Tenant - SGIC SuperAdmin</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <style>
        body { font-family: 'Inter', sans-serif; }
        [x-cloak] { display: none !important; }
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: #f1f5f9; }
        ::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 3px; }
        .gradient-indigo { background: linear-gradient(135deg, #4f46e5 0%, #7c3aed 100%); }
    </style>
</head>
<body class="bg-slate-50 text-slate-800" x-data="tenantWizard()" x-cloak>

    <!-- Top Bar -->
    <header class="bg-white shadow-sm border-b border-slate-200 sticky top-0 z-40">
        <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <a href="{{ route('super-admin.tenants.index') }}" class="text-slate-400 hover:text-slate-600 transition-colors">
                    <i class="fa-solid fa-arrow-left text-lg"></i>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-slate-800">Crear Nuevo Tenant</h1>
                    <p class="text-xs text-slate-500">Onboarding de nuevo cementerio a la plataforma SaaS</p>
                </div>
            </div>
            <div class="flex items-center space-x-3">
                <span class="text-xs text-slate-500 hidden md:block">
                    <i class="fa-solid fa-shield-halved text-indigo-500 mr-1"></i>
                    SuperAdmin: {{ Auth::user()->name }}
                </span>
            </div>
        </div>
    </header>

    <!-- Breadcrumbs -->
    <div class="max-w-5xl mx-auto px-6 pt-4">
        <nav class="flex" aria-label="Breadcrumb">
            <ol class="flex items-center space-x-2 text-sm">
                <li>
                    <a href="{{ route('dashboard') }}" class="text-slate-500 hover:text-indigo-600 transition-colors">
                        <i class="fa-solid fa-house mr-1"></i> Dashboard
                    </a>
                </li>
                <li><i class="fa-solid fa-chevron-right text-xs text-slate-400 mx-2"></i></li>
                <li>
                    <a href="{{ route('super-admin.tenants.index') }}" class="text-slate-500 hover:text-indigo-600 transition-colors">
                        Tenants
                    </a>
                </li>
                <li><i class="fa-solid fa-chevron-right text-xs text-slate-400 mx-2"></i></li>
                <li><span class="text-indigo-600 font-medium">Crear Nuevo</span></li>
            </ol>
        </nav>
    </div>

    <!-- Main Content -->
    <div class="max-w-5xl mx-auto px-6 py-6">

        {{-- Errores Generales --}}
        @if ($errors->any())
            <div class="mb-6 bg-red-50 border-l-4 border-red-500 p-4 rounded-r-lg" role="alert">
                <div class="flex items-start">
                    <i class="fa-solid fa-circle-exclamation text-red-500 mt-0.5 mr-3"></i>
                    <div>
                        <h3 class="text-sm font-semibold text-red-800">Se encontraron errores en el formulario</h3>
                        <ul class="mt-2 text-sm text-red-700 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        {{-- Progress Bar --}}
        <div class="bg-white rounded-xl shadow-sm border border-slate-100 p-6 mb-6">
            <div class="flex items-center justify-between mb-2">
                <h2 class="text-lg font-bold text-slate-800">Asistente de Onboarding</h2>
                <span class="text-sm text-slate-500">
                    Paso <span class="font-bold text-indigo-600" x-text="currentStep"></span> de 4
                </span>
            </div>
            
            <div class="flex items-center justify-between mt-6">
                <template x-for="(step, index) in steps" :key="index">
                    <div class="flex items-center flex-1">
                        <div class="flex flex-col items-center relative">
                            <div class="w-11 h-11 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-sm"
                                 :class="{
                                    'bg-emerald-500 text-white ring-4 ring-emerald-100': currentStep > index + 1,
                                    'bg-gradient-to-br from-indigo-500 to-purple-600 text-white ring-4 ring-indigo-100': currentStep === index + 1,
                                    'bg-slate-100 text-slate-400': currentStep < index + 1
                                 }">
                                <i x-show="currentStep > index + 1" class="fa-solid fa-check"></i>
                                <span x-show="currentStep <= index + 1" x-text="index + 1"></span>
                            </div>
                            <span class="text-xs mt-2 font-medium text-center whitespace-nowrap"
                                  :class="currentStep >= index + 1 ? 'text-indigo-600' : 'text-slate-400'"
                                  x-text="step.title"></span>
                            <span class="text-[10px] text-slate-400 mt-0.5" x-text="step.subtitle"></span>
                        </div>
                        <div x-show="index < steps.length - 1" class="flex-1 h-1 mx-3 rounded-full mt-[-1.5rem]"
                             :class="currentStep > index + 1 ? 'bg-emerald-500' : 'bg-slate-200'">
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Form --}}
        <form action="{{ route('super-admin.tenants.store') }}" method="POST" id="tenantForm">
            @csrf

            {{-- Step 1: Legal Data --}}
            <div x-show="currentStep === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="gradient-indigo px-6 py-4 flex items-center">
                        <div class="h-10 w-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center mr-3">
                            <i class="fa-solid fa-file-contract text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold">Información Legal del Cementerio</h3>
                            <p class="text-indigo-100 text-xs">Datos fiscales y de identificación del tenant</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- Name + RFC --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                    Nombre Comercial <span class="text-red-500">*</span>
                                </label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-church text-slate-400"></i>
                                    </div>
                                    <input type="text" name="name" id="name" value="{{ old('name') }}" required
                                           x-model="form.name" @input="generateSubdomain()"
                                           class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('name') border-red-300 bg-red-50 @enderror"
                                           placeholder="Ej. Panteón San José">
                                </div>
                                @error('name')
                                    <p class="mt-1.5 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                                @enderror
                            </div>

                           
			<div>
			    <label for="rfc" class="block text-sm font-semibold text-slate-700 mb-1.5">
			        RFC <span class="text-red-500">*</span>
			    </label>
			    <div class="relative">
			        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
			            <i class="fa-solid fa-id-card text-slate-400"></i>
			        </div>
			        <input type="text" name="rfc" id="rfc" value="{{ old('rfc') }}" 
			               required maxlength="13"
			               x-model="form.rfc" 
			               @input="form.rfc = form.rfc.toUpperCase()"
			               class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg 
			                      focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 
			                      sm:text-sm font-mono uppercase @error('rfc') border-red-300 bg-red-50 @enderror"
			               placeholder="AAAA000000AAA (PF) o AAA000000AAA (PM)">
			    </div>
			    <p class="mt-1 text-xs text-slate-500">
			        <i class="fa-solid fa-circle-info mr-1 text-indigo-500"></i>
			        Persona Física: 13 caracteres | Persona Moral: 12 caracteres
			    </p>
			    @error('rfc')
			        <p class="mt-1.5 text-xs text-red-600 flex items-center">
			            <i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}
			        </p>
			    @enderror
			</div>                           
                        </div>

                        {{-- Subdomain --}}
                        <div>
                            <label for="subdomain" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Subdominio Único <span class="text-red-500">*</span>
                            </label>
                            <div class="flex rounded-lg shadow-sm">
                                <div class="relative flex-1">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-globe text-slate-400"></i>
                                    </div>
                                    <input type="text" name="subdomain" id="subdomain" value="{{ old('subdomain') }}" required
                                           x-model="form.subdomain"
                                           @input="form.subdomain = form.subdomain.toLowerCase().replace(/[^a-z0-9-]/g, '')"
                                           pattern="[a-z0-9-]+"
                                           class="block w-full pl-10 pr-3 py-2.5 rounded-l-lg border border-slate-300 focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('subdomain') border-red-300 bg-red-50 @enderror"
                                           placeholder="panteon-sanjose">
                                </div>
                                <span class="inline-flex items-center px-4 rounded-r-lg border border-l-0 border-slate-300 bg-slate-50 text-slate-600 sm:text-sm font-mono">
                                    .sgic.mx
                                </span>
                            </div>
                            <p class="mt-1.5 text-xs text-slate-500">
                                <i class="fa-solid fa-circle-info mr-1 text-indigo-500"></i>
                                URL de acceso: <span class="font-mono text-indigo-600" x-text="(form.subdomain || 'subdominio') + '.sgic.mx'"></span>
                            </p>
                            @error('subdomain')
                                <p class="mt-1.5 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Cemetery Name --}}
                        <div>
                            <label for="cemetery_name" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Nombre del Cementerio <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="cemetery_name" id="cemetery_name" value="{{ old('cemetery_name') }}" required
                                   class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('cemetery_name') border-red-300 bg-red-50 @enderror"
                                   placeholder="Ej. Panteón Jardines de la Eternidad">
                            @error('cemetery_name')
                                <p class="mt-1.5 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Address --}}
                        <div>
                            <label for="cemetery_address" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Dirección Completa <span class="text-red-500">*</span>
                            </label>
                            <div class="relative">
                                <div class="absolute top-3 left-0 pl-3 pointer-events-none">
                                    <i class="fa-solid fa-location-dot text-slate-400"></i>
                                </div>
                                <textarea name="cemetery_address" id="cemetery_address" rows="2" required
                                          class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('cemetery_address') border-red-300 bg-red-50 @enderror"
                                          placeholder="Calle, Número, Colonia">{{ old('cemetery_address') }}</textarea>
                            </div>
                            @error('cemetery_address')
                                <p class="mt-1.5 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Location --}}
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
                            <div>
                                <label for="cemetery_municipality" class="block text-sm font-semibold text-slate-700 mb-1.5">Municipio <span class="text-red-500">*</span></label>
                                <input type="text" name="cemetery_municipality" id="cemetery_municipality" value="{{ old('cemetery_municipality') }}" required
                                       class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('cemetery_municipality') border-red-300 bg-red-50 @enderror">
                                @error('cemetery_municipality')
                                    <p class="mt-1.5 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="cemetery_state" class="block text-sm font-semibold text-slate-700 mb-1.5">Estado <span class="text-red-500">*</span></label>
                                <select name="cemetery_state" id="cemetery_state" required
                                        class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white @error('cemetery_state') border-red-300 bg-red-50 @enderror">
                                    <option value="">Seleccionar...</option>
                                    @foreach(['Aguascalientes','Baja California','Baja California Sur','Campeche','Chiapas','Chihuahua','CDMX','Coahuila','Colima','Durango','Estado de México','Guanajuato','Guerrero','Hidalgo','Jalisco','Michoacán','Morelos','Nayarit','Nuevo León','Oaxaca','Puebla','Querétaro','Quintana Roo','San Luis Potosí','Sinaloa','Sonora','Tabasco','Tamaulipas','Tlaxcala','Veracruz','Yucatán','Zacatecas'] as $state)
                                        <option value="{{ $state }}" {{ old('cemetery_state') == $state ? 'selected' : '' }}>{{ $state }}</option>
                                    @endforeach
                                </select>
                                @error('cemetery_state')
                                    <p class="mt-1.5 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="cemetery_postal_code" class="block text-sm font-semibold text-slate-700 mb-1.5">C.P. <span class="text-red-500">*</span></label>
                                <input type="text" name="cemetery_postal_code" id="cemetery_postal_code" value="{{ old('cemetery_postal_code') }}" required maxlength="5" pattern="[0-9]{5}"
                                       class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-mono @error('cemetery_postal_code') border-red-300 bg-red-50 @enderror">
                                @error('cemetery_postal_code')
                                    <p class="mt-1.5 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        {{-- Contact --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                            <div>
                                <label for="cemetery_phone" class="block text-sm font-semibold text-slate-700 mb-1.5">Teléfono</label>
                                <input type="text" name="cemetery_phone" id="cemetery_phone" value="{{ old('cemetery_phone') }}"
                                       class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="+52 55 1234 5678">
                            </div>
                            <div>
                                <label for="cemetery_email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email</label>
                                <input type="email" name="cemetery_email" id="cemetery_email" value="{{ old('cemetery_email') }}"
                                       class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm"
                                       placeholder="contacto@cementerio.com">
                            </div>
                        </div>

                        {{-- Legal Representative --}}
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-5 pt-4 border-t border-slate-100">
                            <div>
                                <label for="legal_representative" class="block text-sm font-semibold text-slate-700 mb-1.5">Representante Legal <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-user-tie text-slate-400"></i>
                                    </div>
                                    <input type="text" name="legal_representative" id="legal_representative" value="{{ old('legal_representative') }}" required
                                           class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('legal_representative') border-red-300 bg-red-50 @enderror">
                                </div>
                                @error('legal_representative')
                                    <p class="mt-1.5 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                                @enderror
                            </div>

                            <div>
                                <label for="legal_representative_rfc" class="block text-sm font-semibold text-slate-700 mb-1.5">RFC Representante <span class="text-red-500">*</span></label>
                                <input type="text" name="legal_representative_rfc" id="legal_representative_rfc" value="{{ old('legal_representative_rfc') }}" required maxlength="13"
                                       @input="$event.target.value = $event.target.value.toUpperCase()"
                                       class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-mono uppercase @error('legal_representative_rfc') border-red-300 bg-red-50 @enderror">
                                @error('legal_representative_rfc')
                                    <p class="mt-1.5 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 2: RN Configuration --}}
            <div x-show="currentStep === 2" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="gradient-indigo px-6 py-4 flex items-center">
                        <div class="h-10 w-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center mr-3">
                            <i class="fa-solid fa-sliders text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold">Configuración de Reglas de Negocio</h3>
                            <p class="text-indigo-100 text-xs">Parámetros operativos del cementerio (RN-03, RN-04)</p>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="bg-indigo-50 border border-indigo-100 rounded-lg p-4 mb-6 flex items-start">
                            <i class="fa-solid fa-circle-info text-indigo-600 mt-0.5 mr-3"></i>
                            <div class="text-sm text-indigo-800">
                                <p class="font-semibold mb-1">Configuración parametrizable por tenant</p>
                                <p class="text-indigo-700">Estos valores definen el comportamiento operativo del cementerio según las reglas de negocio del sistema.</p>
                            </div>
                        </div>

                        <div class="space-y-4">
                            {{-- RN-03: Grace Period --}}
                            <div class="border border-slate-200 rounded-lg p-5">
                                <div class="flex items-start mb-3">
                                    <span class="bg-indigo-100 text-indigo-700 text-xs font-bold px-2.5 py-1 rounded mr-3 mt-0.5">RN-03</span>
                                    <div>
                                        <h4 class="font-semibold text-slate-800">Periodo de Gracia para Decadencia</h4>
                                        <p class="text-xs text-slate-500 mt-1">Tiempo tras vencimiento de contrato temporal antes de iniciar decadencia legal</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 pl-14">
                                    <input type="number" name="grace_period_years" value="{{ old('grace_period_years', 3) }}" min="1" max="10" required
                                           class="w-24 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center font-semibold">
                                    <span class="text-sm text-slate-600">años</span>
                                    <span class="ml-auto text-xs text-slate-400 bg-slate-50 px-2 py-1 rounded">Default: 3 años</span>
                                </div>
                            </div>

                            {{-- RN-04: Block Months --}}
                            <div class="border border-slate-200 rounded-lg p-5">
                                <div class="flex items-start mb-3">
                                    <span class="bg-red-100 text-red-700 text-xs font-bold px-2.5 py-1 rounded mr-3 mt-0.5">RN-04</span>
                                    <div>
                                        <h4 class="font-semibold text-slate-800">Bloqueo Automático por Morosidad</h4>
                                        <p class="text-xs text-slate-500 mt-1">Meses de atraso para bloquear cripta automáticamente</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 pl-14">
                                    <input type="number" name="debt_months_to_block" value="{{ old('debt_months_to_block', 3) }}" min="1" max="12" required
                                           class="w-24 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center font-semibold">
                                    <span class="text-sm text-slate-600">meses</span>
                                    <span class="ml-auto text-xs text-slate-400 bg-slate-50 px-2 py-1 rounded">Default: 3 meses</span>
                                </div>
                            </div>

                            {{-- RN-04: Interest Rate --}}
                            <div class="border border-slate-200 rounded-lg p-5">
                                <div class="flex items-start mb-3">
                                    <span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded mr-3 mt-0.5">RN-04</span>
                                    <div>
                                        <h4 class="font-semibold text-slate-800">Tasa de Interés Moratorio</h4>
                                        <p class="text-xs text-slate-500 mt-1">Tasa mensual aplicada sobre adeudos vencidos</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 pl-14">
                                    <input type="number" step="0.0001" name="moratorium_interest_rate" value="{{ old('moratorium_interest_rate', 0.02) }}" min="0" max="0.10" required
                                           class="w-24 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center font-semibold">
                                    <span class="text-sm text-slate-600">decimal (0.02 = 2%)</span>
                                    <span class="ml-auto text-xs text-slate-400 bg-slate-50 px-2 py-1 rounded">Default: 0.02</span>
                                </div>
                            </div>

                            {{-- Reservation Config --}}
                            <div class="border border-slate-200 rounded-lg p-5">
                                <div class="flex items-start mb-3">
                                    <span class="bg-blue-100 text-blue-700 text-xs font-bold px-2.5 py-1 rounded mr-3 mt-0.5">RES</span>
                                    <div>
                                        <h4 class="font-semibold text-slate-800">Configuración de Reservas</h4>
                                        <p class="text-xs text-slate-500 mt-1">Parámetros para el sistema de reservas de criptas</p>
                                    </div>
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pl-14">
                                    <div class="flex items-center gap-3">
                                        <input type="number" name="reservation_days" value="{{ old('reservation_days', 15) }}" min="1" max="60" required
                                               class="w-24 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center font-semibold">
                                        <span class="text-sm text-slate-600">días</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <input type="number" step="0.01" name="reservation_deposit_percent" value="{{ old('reservation_deposit_percent', 20.00) }}" min="0" max="100" required
                                               class="w-24 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center font-semibold">
                                        <span class="text-sm text-slate-600">% anticipo</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Maintenance Grace --}}
                            <div class="border border-slate-200 rounded-lg p-5">
                                <div class="flex items-start mb-3">
                                    <span class="bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded mr-3 mt-0.5">MANT</span>
                                    <div>
                                        <h4 class="font-semibold text-slate-800">Días de Gracia para Mantenimiento</h4>
                                        <p class="text-xs text-slate-500 mt-1">Días adicionales tras vencimiento de cuota anual</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-4 pl-14">
                                    <input type="number" name="maintenance_grace_days" value="{{ old('maintenance_grace_days', 30) }}" min="0" max="180" required
                                           class="w-24 px-3 py-2 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm text-center font-semibold">
                                    <span class="text-sm text-slate-600">días</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 3: Initial Admin --}}
            <div x-show="currentStep === 3" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="gradient-indigo px-6 py-4 flex items-center">
                        <div class="h-10 w-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center mr-3">
                            <i class="fa-solid fa-user-shield text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold">Usuario Administrador Inicial</h3>
                            <p class="text-indigo-100 text-xs">Credenciales del AdminCementerio principal</p>
                        </div>
                    </div>

                    <div class="p-6">
                        <div class="bg-amber-50 border border-amber-200 rounded-lg p-4 mb-6 flex items-start">
                            <i class="fa-solid fa-triangle-exclamation text-amber-600 mt-0.5 mr-3"></i>
                            <div class="text-sm text-amber-800">
                                <p class="font-semibold mb-1">Usuario con rol AdminCementerio</p>
                                <p class="text-amber-700">Este usuario tendrá permisos completos sobre el tenant.</p>
                            </div>
                        </div>

                        <div class="space-y-5">
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                                <div>
                                    <label for="admin_name" class="block text-sm font-semibold text-slate-700 mb-1.5">Nombre Completo <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fa-solid fa-user text-slate-400"></i>
                                        </div>
                                        <input type="text" name="admin_name" id="admin_name" value="{{ old('admin_name') }}" required
                                               class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('admin_name') border-red-300 bg-red-50 @enderror">
                                    </div>
                                    @error('admin_name')
                                        <p class="mt-1.5 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                                    @enderror
                                </div>

                                <div>
                                    <label for="admin_email" class="block text-sm font-semibold text-slate-700 mb-1.5">Email <span class="text-red-500">*</span></label>
                                    <div class="relative">
                                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                            <i class="fa-solid fa-envelope text-slate-400"></i>
                                        </div>
                                        <input type="email" name="admin_email" id="admin_email" value="{{ old('admin_email') }}" required
                                               class="block w-full pl-10 pr-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm @error('admin_email') border-red-300 bg-red-50 @enderror">
                                    </div>
                                    @error('admin_email')
                                        <p class="mt-1.5 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                                    @enderror
                                </div>
                            </div>

                            <div>
                                <label for="admin_password" class="block text-sm font-semibold text-slate-700 mb-1.5">Contraseña Temporal <span class="text-red-500">*</span></label>
                                <div class="relative">
                                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                        <i class="fa-solid fa-lock text-slate-400"></i>
                                    </div>
                                    <input :type="showPassword ? 'text' : 'password'" name="admin_password" id="admin_password" required
                                           x-model="adminPassword"
                                           class="block w-full pl-10 pr-20 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm font-mono @error('admin_password') border-red-300 bg-red-50 @enderror">
                                    <div class="absolute inset-y-0 right-0 pr-2 flex items-center gap-1">
                                        <button type="button" @click="showPassword = !showPassword" class="p-1.5 text-slate-400 hover:text-slate-600 rounded">
                                            <i class="fa-regular" :class="showPassword ? 'fa-eye-slash' : 'fa-eye'"></i>
                                        </button>
                                        <button type="button" @click="generatePassword()" class="p-1.5 text-indigo-500 hover:text-indigo-700 rounded" title="Generar contraseña segura">
                                            <i class="fa-solid fa-wand-magic-sparkles"></i>
                                        </button>
                                    </div>
                                </div>
                                @error('admin_password')
                                    <p class="mt-1.5 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Step 4: Plan & Summary --}}
            <div x-show="currentStep === 4" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="bg-white rounded-xl shadow-sm border border-slate-100 overflow-hidden">
                    <div class="gradient-indigo px-6 py-4 flex items-center">
                        <div class="h-10 w-10 rounded-lg bg-white/20 backdrop-blur-sm flex items-center justify-center mr-3">
                            <i class="fa-solid fa-credit-card text-white text-lg"></i>
                        </div>
                        <div>
                            <h3 class="text-white font-bold">Plan SaaS y Resumen</h3>
                            <p class="text-indigo-100 text-xs">Selecciona el plan y duración de suscripción</p>
                        </div>
                    </div>

                    <div class="p-6 space-y-6">
                        {{-- Plan Selection --}}
                        <div>
                            <label class="block text-sm font-semibold text-slate-700 mb-3">Selecciona el Plan <span class="text-red-500">*</span></label>
                            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                                @foreach([
                                    ['code' => 'basic', 'name' => 'Básico', 'price' => 1500, 'features' => ['Hasta 500 criptas', '3 usuarios', 'Soporte email']],
                                    ['code' => 'professional', 'name' => 'Profesional', 'price' => 3500, 'features' => ['Hasta 2,000 criptas', '10 usuarios', 'PWA Campo + BI', 'Soporte prioritario'], 'popular' => true],
                                    ['code' => 'enterprise', 'name' => 'Enterprise', 'price' => 8000, 'features' => ['Criptas ilimitadas', 'Usuarios ilimitados', 'API + Integraciones', 'Soporte 24/7']]
                                ] as $plan)
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="plan" value="{{ $plan['code'] }}" 
                                               {{ old('plan', 'professional') == $plan['code'] ? 'checked' : '' }}
                                               class="peer sr-only">
                                        <div class="border-2 rounded-xl p-5 transition-all peer-checked:border-indigo-500 peer-checked:bg-indigo-50 peer-checked:shadow-md hover:border-slate-300 {{ isset($plan['popular']) ? 'border-indigo-300' : 'border-slate-200' }} h-full relative">
                                            @if(isset($plan['popular']))
                                                <div class="absolute -top-3 right-4 bg-gradient-to-r from-indigo-500 to-purple-500 text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-sm">
                                                    MÁS POPULAR
                                                </div>
                                            @endif
                                            <div class="flex justify-between items-start mb-3">
                                                <span class="text-xs font-bold uppercase tracking-wider {{ $plan['code'] === 'enterprise' ? 'text-purple-600' : ($plan['code'] === 'professional' ? 'text-indigo-600' : 'text-slate-500') }}">
                                                    {{ $plan['name'] }}
                                                </span>
                                                <div class="h-5 w-5 rounded-full border-2 border-slate-300 peer-checked:border-indigo-500 peer-checked:bg-indigo-500 flex items-center justify-center">
                                                    <i class="fa-solid fa-check text-white text-[10px] opacity-0 peer-checked:opacity-100"></i>
                                                </div>
                                            </div>
                                            <div class="text-3xl font-bold text-slate-800">${{ number_format($plan['price']) }}<span class="text-sm text-slate-500 font-normal">/mes</span></div>
                                            <ul class="mt-4 space-y-2 text-xs text-slate-600">
                                                @foreach($plan['features'] as $feature)
                                                    <li class="flex items-start"><i class="fa-solid fa-check text-emerald-500 mr-2 mt-0.5"></i> {{ $feature }}</li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                            @error('plan')
                                <p class="mt-2 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Subscription Duration --}}
                        <div>
                            <label for="subscription_months" class="block text-sm font-semibold text-slate-700 mb-1.5">
                                Duración de Suscripción <span class="text-red-500">*</span>
                            </label>
                            <select name="subscription_months" id="subscription_months" required
                                    class="block w-full px-3 py-2.5 border border-slate-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm bg-white">
                                <option value="1" {{ old('subscription_months') == '1' ? 'selected' : '' }}>1 mes</option>
                                <option value="3" {{ old('subscription_months') == '3' ? 'selected' : '' }}>3 meses</option>
                                <option value="6" {{ old('subscription_months') == '6' ? 'selected' : '' }}>6 meses</option>
                                <option value="12" {{ old('subscription_months', '12') == '12' ? 'selected' : '' }}>12 meses (recomendado)</option>
                                <option value="24" {{ old('subscription_months') == '24' ? 'selected' : '' }}>24 meses</option>
                                <option value="36" {{ old('subscription_months') == '36' ? 'selected' : '' }}>36 meses</option>
                            </select>
                            @error('subscription_months')
                                <p class="mt-1.5 text-xs text-red-600 flex items-center"><i class="fa-solid fa-circle-exclamation mr-1"></i> {{ $message }}</p>
                            @enderror
                        </div>

                        {{-- Summary --}}
                        <div class="bg-gradient-to-br from-slate-50 to-indigo-50 border border-slate-200 rounded-xl p-6">
                            <h4 class="font-bold text-slate-800 mb-4 flex items-center">
                                <i class="fa-solid fa-clipboard-check text-indigo-600 mr-2"></i>
                                Resumen de Configuración
                            </h4>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-8 gap-y-3 text-sm">
                                <div class="flex justify-between py-2 border-b border-slate-200">
                                    <span class="text-slate-500">Tenant</span>
                                    <span class="font-semibold text-slate-800 text-right" x-text="form.name || '—'"></span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-200">
                                    <span class="text-slate-500">RFC</span>
                                    <span class="font-semibold text-slate-800 font-mono" x-text="form.rfc || '—'"></span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-200">
                                    <span class="text-slate-500">Subdominio</span>
                                    <span class="font-semibold text-indigo-600 font-mono text-right" x-text="(form.subdomain || 'subdominio') + '.sgic.mx'"></span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-200">
                                    <span class="text-slate-500">Admin</span>
                                    <span class="font-semibold text-slate-800 text-right truncate ml-2" id="summary-admin-email">—</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-200">
                                    <span class="text-slate-500">Periodo de Gracia</span>
                                    <span class="font-semibold text-slate-800">{{ old('grace_period_years', 3) }} años</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-200">
                                    <span class="text-slate-500">Bloqueo Morosidad</span>
                                    <span class="font-semibold text-slate-800">{{ old('debt_months_to_block', 3) }} meses</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-200">
                                    <span class="text-slate-500">Tasa Interés</span>
                                    <span class="font-semibold text-slate-800">{{ old('moratorium_interest_rate', 0.02) * 100 }}% mensual</span>
                                </div>
                                <div class="flex justify-between py-2 border-b border-slate-200">
                                    <span class="text-slate-500">Suscripción</span>
                                    <span class="font-semibold text-slate-800" id="summary-months">12 meses</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Navigation Buttons --}}
            <div class="flex justify-between items-center mt-6 bg-white rounded-xl shadow-sm border border-slate-100 p-4">
                <button type="button" x-show="currentStep > 1" @click="prevStep()"
                        class="inline-flex items-center px-5 py-2.5 border border-slate-300 shadow-sm text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50">
                    <i class="fa-solid fa-arrow-left mr-2"></i> Anterior
                </button>
                <div x-show="currentStep === 1"></div>
                
                <div class="flex items-center gap-3">
                    <a href="{{ route('super-admin.tenants.index') }}" 
                       class="inline-flex items-center px-5 py-2.5 border border-slate-300 shadow-sm text-sm font-medium rounded-lg text-slate-700 bg-white hover:bg-slate-50">
                        <i class="fa-solid fa-xmark mr-2"></i> Cancelar
                    </a>
                    
                    <button type="button" x-show="currentStep < 4" @click="nextStep()"
                            class="inline-flex items-center px-5 py-2.5 border border-transparent shadow-sm text-sm font-medium rounded-lg text-white bg-indigo-600 hover:bg-indigo-700">
                        Siguiente <i class="fa-solid fa-arrow-right ml-2"></i>
                    </button>
                    
                    <button type="submit" x-show="currentStep === 4"
                            class="inline-flex items-center px-6 py-2.5 border border-transparent shadow-sm text-sm font-semibold rounded-lg text-white bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-700 hover:to-emerald-600">
                        <i class="fa-solid fa-rocket mr-2"></i> Crear Tenant
                    </button>
                </div>
            </div>
        </form>
    </div>

    {{-- Toast --}}
    <div x-data="{ show: false, message: '', type: 'info' }" 
         x-on:show-toast.window="show = true; message = $event.detail.message; type = $event.detail.type; setTimeout(() => show = false, 4000)"
         x-show="show" x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         class="fixed bottom-6 right-6 z-50 max-w-md">
        <div class="bg-slate-900 text-white px-5 py-3 rounded-lg shadow-2xl flex items-center justify-between border border-slate-700">
            <div class="flex items-center">
                <i class="fa-solid mr-3" 
                   :class="{
                       'fa-circle-check text-emerald-400': type === 'success',
                       'fa-circle-exclamation text-amber-400': type === 'warning',
                       'fa-circle-info text-blue-400': type === 'info',
                       'fa-circle-xmark text-red-400': type === 'error'
                   }"></i>
                <span class="text-sm font-medium" x-text="message"></span>
            </div>
            <button @click="show = false" class="ml-4 text-slate-400 hover:text-white">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
    </div>

    <script>
        function tenantWizard() {
            // Detectar paso con errores de validación
            const errorStep = detectErrorStep();
            
            return {
                currentStep: errorStep > 0 ? errorStep : {{ old('step', 1) }},
                showPassword: false,
                adminPassword: '{{ old("admin_password") }}',
                form: {
                    name: '{{ old("name") }}',
                    rfc: '{{ old("rfc") }}',
                    subdomain: '{{ old("subdomain") }}'
                },
                steps: [
                    { title: 'Datos Legales', subtitle: 'Identificación' },
                    { title: 'Reglas de Negocio', subtitle: 'RN-03, RN-04' },
                    { title: 'Admin Inicial', subtitle: 'Credenciales' },
                    { title: 'Plan y Resumen', subtitle: 'Confirmación' }
                ],
                
                init() {
                    // Actualizar resumen cuando cambie el plan
                    document.querySelectorAll('input[name="plan"]').forEach(radio => {
                        radio.addEventListener('change', () => this.updateSummary());
                    });
                    document.getElementById('subscription_months')?.addEventListener('change', () => this.updateSummary());
                    document.getElementById('admin_email')?.addEventListener('input', () => this.updateSummary());
                    this.updateSummary();
                },

                updateSummary() {
                    const adminEmail = document.getElementById('admin_email')?.value || '—';
                    const months = document.getElementById('subscription_months')?.value || '12';
                    
                    document.getElementById('summary-admin-email').textContent = adminEmail;
                    document.getElementById('summary-months').textContent = months + ' meses';
                },

                generateSubdomain() {
                    if (!this.form.name) return;
                    this.form.subdomain = this.form.name
                        .toLowerCase()
                        .normalize('NFD')
                        .replace(/[\u0300-\u036f]/g, '')
                        .replace(/[^a-z0-9\s-]/g, '')
                        .trim()
                        .replace(/\s+/g, '-')
                        .replace(/-+/g, '-')
                        .substring(0, 40);
                },

                generatePassword() {
                    const upper = 'ABCDEFGHJKLMNPQRSTUVWXYZ';
                    const lower = 'abcdefghijkmnopqrstuvwxyz';
                    const numbers = '23456789';
                    const symbols = '!@#$%&*';
                    const all = upper + lower + numbers + symbols;
                    
                    let pwd = '';
                    pwd += upper[Math.floor(Math.random() * upper.length)];
                    pwd += lower[Math.floor(Math.random() * lower.length)];
                    pwd += numbers[Math.floor(Math.random() * numbers.length)];
                    pwd += symbols[Math.floor(Math.random() * symbols.length)];
                    
                    for (let i = 0; i < 8; i++) {
                        pwd += all[Math.floor(Math.random() * all.length)];
                    }
                    
                    this.adminPassword = pwd.split('').sort(() => Math.random() - 0.5).join('');
                },

                nextStep() {
                    if (!this.validateCurrentStep()) return;
                    if (this.currentStep < 4) this.currentStep++;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                prevStep() {
                    if (this.currentStep > 1) this.currentStep--;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },

                validateCurrentStep() {
                    if (this.currentStep === 1) {
                        const required = ['name', 'rfc', 'subdomain', 'cemetery_name', 'cemetery_address', 
                                         'cemetery_municipality', 'cemetery_state', 'cemetery_postal_code',
                                         'legal_representative', 'legal_representative_rfc'];
                        for (const field of required) {
                            const el = document.querySelector(`[name="${field}"]`);
                            if (el && !el.value.trim()) {
                                window.dispatchEvent(new CustomEvent('show-toast', { 
                                    detail: { message: 'Por favor completa todos los campos obligatorios', type: 'error' } 
                                }));
                                el.focus();
                                return false;
                            }
                        }
                    }
                    if (this.currentStep === 3) {
                        const pwd = document.getElementById('admin_password').value;
                        if (pwd.length < 8) {
                            window.dispatchEvent(new CustomEvent('show-toast', { 
                                detail: { message: 'La contraseña debe tener al menos 8 caracteres', type: 'error' } 
                            }));
                            return false;
                        }
                    }
                    return true;
                },

                showToast(message, type = 'info') {
                    window.dispatchEvent(new CustomEvent('show-toast', { 
                        detail: { message, type } 
                    }));
                }
            }
        }

        // Detectar en qué paso hay errores de validación
        function detectErrorStep() {
            @if($errors->any())
                const step1Fields = ['name', 'rfc', 'subdomain', 'cemetery_name', 'cemetery_address', 
                                    'cemetery_municipality', 'cemetery_state', 'cemetery_postal_code',
                                    'legal_representative', 'legal_representative_rfc', 'cemetery_phone', 'cemetery_email'];
                const step2Fields = ['grace_period_years', 'debt_months_to_block', 'moratorium_interest_rate', 
                                    'reservation_days', 'reservation_deposit_percent', 'maintenance_grace_days'];
                const step3Fields = ['admin_name', 'admin_email', 'admin_password'];
                const step4Fields = ['plan', 'subscription_months'];
                
                const errors = @json($errors->keys());
                
                if (errors.some(e => step4Fields.includes(e))) return 4;
                if (errors.some(e => step3Fields.includes(e))) return 3;
                if (errors.some(e => step2Fields.includes(e))) return 2;
                if (errors.some(e => step1Fields.includes(e))) return 1;
            @endif
            return 0;
        }
    </script>
</body>
</html>