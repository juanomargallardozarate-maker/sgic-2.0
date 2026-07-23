@extends('layouts.app')

@section('title', 'Nuevo Contrato')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('inventory.commercial.contracts.index') }}" class="text-emerald-600 hover:text-emerald-900 mb-2 inline-block">
                <i class="fa-solid fa-arrow-left mr-2"></i> Volver a Contratos
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Crear Nuevo Contrato</h1>
            <p class="text-sm text-gray-600 mt-1">Complete la información para generar un nuevo contrato</p>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('inventory.commercial.contracts.store') }}" class="bg-white rounded-lg shadow p-6">
            @csrf

            {{-- Datos del Cliente --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Información del Cliente</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                        <select name="customer_id" id="customer_id" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('customer_id') border-red-500 @enderror">
                            <option value="">Seleccionar cliente...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                                    {{ $customer->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('customer_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                        @error('phone_verified')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Contrato *</label>
                        <select name="contract_type_id" id="contract_type_id" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('contract_type_id') border-red-500 @enderror">
                            <option value="">Seleccionar tipo...</option>
                            @foreach($contractTypes as $type)
                                <option value="{{ $type->id }}" {{ old('contract_type_id') == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }} {{ $type->is_temporary ? '(' . $type->years . ' años)' : '(Perpetuo)' }}
                                </option>
                            @endforeach
                        </select>
                        @error('contract_type_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Verificación de Teléfono vía WhatsApp --}}
                <div class="mt-4 p-4 bg-blue-50 border border-blue-200 rounded-md">
                    <div class="flex items-start">
                        <div class="flex-shrink-0">
                            <i class="fa-brands fa-whatsapp text-blue-500 text-xl"></i>
                        </div>
                        <div class="ml-3 flex-1">
                            <h4 class="text-sm font-medium text-blue-800">Verificación de Teléfono</h4>
                            <p class="text-xs text-blue-700 mt-1">
                                Es necesario verificar el teléfono del cliente antes de crear el contrato. El cliente recibirá un código de 6 dígitos por WhatsApp.
                            </p>
                            
                            <div id="verification-section" class="mt-3 hidden">
                                <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                    <div class="md:col-span-2">
                                        <p class="text-xs text-gray-600 mb-1">
                                            <strong>Teléfono:</strong> <span id="customer-phone"></span>
                                            <span id="verified-badge" class="hidden ml-2 px-2 py-1 bg-green-100 text-green-800 text-xs rounded-full">
                                                <i class="fa-solid fa-check-circle"></i> Verificado
                                            </span>
                                        </p>
                                    </div>
                                </div>
                                
                                <div class="flex gap-2 mt-2">
                                    <button type="button" id="btn-send-code" class="px-3 py-2 bg-blue-600 text-white text-xs rounded-md hover:bg-blue-700 transition">
                                        <i class="fa-solid fa-paper-plane mr-1"></i> Enviar Código
                                    </button>
                                    
                                    <div id="code-input-section" class="hidden flex items-center gap-2">
                                        <input type="text" id="verification-code" maxlength="6" 
                                               placeholder="Código de 6 dígitos"
                                               class="w-32 border-gray-300 rounded-md shadow-sm text-sm focus:border-emerald-500 focus:ring-emerald-500">
                                        <button type="button" id="btn-verify-code" class="px-3 py-2 bg-green-600 text-white text-xs rounded-md hover:bg-green-700 transition">
                                            <i class="fa-solid fa-check mr-1"></i> Verificar
                                        </button>
                                    </div>
                                    
                                    <div id="loading-spinner" class="hidden">
                                        <i class="fa-solid fa-circle-notch fa-spin text-blue-500"></i>
                                        <span class="text-xs text-gray-600 ml-1">Enviando...</span>
                                    </div>
                                    
                                    <div id="verification-message" class="text-xs mt-1"></div>
                                </div>
                                
                                <input type="hidden" id="is-phone-verified" name="is_phone_verified" value="0">
                            </div>
                            
                            <div id="no-phone-message" class="hidden mt-2 text-xs text-orange-600">
                                <i class="fa-solid fa-exclamation-triangle"></i> El cliente seleccionado no tiene un número de teléfono registrado.
                            </div>
                            
                            <div id="already-verified-message" class="hidden mt-2 text-xs text-green-600">
                                <i class="fa-solid fa-check-circle"></i> El teléfono de este cliente ya está verificado.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Información de la Cripta --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Información de la Cripta</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cripta *</label>
                        <select name="crypt_id" id="crypt_id" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('crypt_id') border-red-500 @enderror">
                            <option value="">Seleccionar cripta...</option>
                            @foreach($availableCrypts as $crypt)
                                <option value="{{ $crypt->id }}" {{ old('crypt_id') == $crypt->id ? 'selected' : '' }} 
                                        data-price="{{ $crypt->price ?? 0 }}">
                                    {{ $crypt->full_code }} - {{ $crypt->cryptType->name ?? 'N/A' }} (Capacidad: {{ $crypt->capacity }}) - Precio: ${{ number_format($crypt->price ?? 0, 2) }}
                                </option>
                            @endforeach
                        </select>
                        @error('crypt_id')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Condiciones Económicas --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Condiciones Económicas</h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Precio Total *</label>
                        <input type="number" step="0.01" name="price" id="price" value="{{ old('price') }}" required readonly
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-gray-100 @error('price') border-red-500 @enderror">
                        @error('price')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cuota Anual de Mantenimiento *</label>
                        <input type="number" step="0.01" name="annual_maintenance_fee" id="annual_maintenance_fee" value="{{ old('annual_maintenance_fee', $maintenanceFee) }}" required readonly
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-gray-100 @error('annual_maintenance_fee') border-red-500 @enderror">
                        @error('annual_maintenance_fee')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Pago *</label>
                        <select name="payment_type" id="payment_type" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('payment_type') border-red-500 @enderror">
                            <option value="">Seleccionar tipo de pago...</option>
                            <option value="cash" {{ old('payment_type') === 'cash' ? 'selected' : '' }}>Contado</option>
                            <option value="installments" {{ old('payment_type') === 'installments' ? 'selected' : '' }}>Parcialidades</option>
                            <option value="mixed" {{ old('payment_type') === 'mixed' ? 'selected' : '' }}>Mixto</option>
                        </select>
                        @error('payment_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de Parcialidades</label>
                        <input type="number" name="installments_count" id="installments_count" value="{{ old('installments_count') }}" min="1"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('installments_count')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="down_payment_field" style="display: none;">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Enganche / Anticipo *</label>
                        <input type="number" step="0.01" name="down_payment" id="down_payment" value="{{ old('down_payment') }}" min="0"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        <p class="text-xs text-gray-500 mt-1">Sugerido: 10% del precio total. Este monto se restará del precio para calcular el financiamiento.</p>
                        @error('down_payment')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Saldo a Financiar</label>
                        <input type="number" step="0.01" name="financed_amount" id="financed_amount" value="{{ old('financed_amount', 0) }}" readonly
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-gray-100">
                        <p class="text-xs text-gray-500 mt-1">Precio total - Enganche</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tasa de Interés (%)</label>
                        <input type="number" step="0.01" name="interest_rate" id="interest_rate" value="{{ old('interest_rate', 0) }}" readonly
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-gray-100">
                        <p class="text-xs text-gray-500 mt-1">Tasa anual según plazo</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Mensualidad a Pagar</label>
                        <input type="number" step="0.01" name="monthly_payment" id="monthly_payment" value="{{ old('monthly_payment', 0) }}" readonly
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-gray-100">
                        <p class="text-xs text-gray-500 mt-1">Calculado por método francés</p>
                    </div>
                </div>
            </div>

            {{-- Vigencia --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Vigencia del Contrato</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Inicio *</label>
                        <input type="date" name="start_date" value="{{ old('start_date', date('Y-m-d')) }}" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('start_date') border-red-500 @enderror">
                        @error('start_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="end_date_field">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Vencimiento del Contrato</label>
                        <input type="date" name="end_date" id="end_date" value="{{ old('end_date') }}" readonly
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-gray-100 @error('end_date') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Se calcula automáticamente según el tipo de contrato</p>
                        @error('end_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Fin del Financiamiento</label>
                        <input type="date" name="financing_end_date" id="financing_end_date" value="{{ old('financing_end_date') }}" readonly
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 bg-gray-100 @error('financing_end_date') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Se calcula automáticamente cuando hay parcialidades</p>
                        @error('financing_end_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Notas --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-1">Notas Adicionales</label>
                <textarea name="notes" rows="3" 
                          class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('notes') }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('inventory.commercial.contracts.index') }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-emerald-600 hover:bg-emerald-700 text-white rounded-md font-medium transition-colors">
                    <i class="fa-solid fa-save mr-2"></i>
                    Guardar Contrato
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === Manejo de Tipo de Contrato y Fecha de Vencimiento ===
    const contractTypeSelect = document.getElementById('contract_type_id');
    const endDateField = document.getElementById('end_date_field');
    const endDateInput = document.getElementById('end_date');
    const financingEndDateInput = document.getElementById('financing_end_date');
    
    // Obtener datos del tipo de contrato seleccionado
    function getContractTypeData() {
        const selectedOption = contractTypeSelect.options[contractTypeSelect.selectedIndex];
        const yearsText = selectedOption.getAttribute('data-years');
        return yearsText ? parseInt(yearsText) : null;
    }

    function calculateContractEndDate() {
        const startDateInput = document.querySelector('input[name="start_date"]');
        if (!startDateInput || !startDateInput.value) return;
        
        const years = getContractTypeData();
        if (years && years > 0) {
            const startDate = new Date(startDateInput.value + 'T00:00:00');
            const endDate = new Date(startDate);
            endDate.setFullYear(endDate.getFullYear() + years);
            
            const year = endDate.getFullYear();
            const month = String(endDate.getMonth() + 1).padStart(2, '0');
            const day = String(endDate.getDate()).padStart(2, '0');
            
            endDateInput.value = `${year}-${month}-${day}`;
        } else {
            // Perpetuo: no hay fecha de vencimiento
            endDateInput.value = '';
        }
    }
    
    contractTypeSelect.addEventListener('change', calculateContractEndDate);
    document.querySelector('input[name="start_date"]').addEventListener('change', calculateContractEndDate);
    
    // Calcular fecha inicial si ya hay datos
    if (contractTypeSelect.value && document.querySelector('input[name="start_date"]').value) {
        calculateContractEndDate();
    }

    // === Obtener datos de configuración ===
    const maintenanceFee = {{ $maintenanceFee ?? 0 }};
    const interestRatesData = @json($interestRates ?? []);
    
    // Convertir a mapa para búsqueda rápida: { months: percentage }
    const interestRatesMap = {};
    interestRatesData.forEach(rate => {
        interestRatesMap[rate.months] = rate.percentage;
    });

    // === Actualizar precio y mantenimiento al seleccionar cripta ===
    const cryptSelect = document.getElementById('crypt_id');
    const priceInput = document.getElementById('price');
    const maintenanceFeeInput = document.getElementById('annual_maintenance_fee');

    // Establecer cuota de mantenimiento inicial
    if (maintenanceFeeInput) {
        maintenanceFeeInput.value = maintenanceFee.toFixed(2);
    }

    if (cryptSelect && priceInput) {
        function updatePrice() {
            const selectedOption = cryptSelect.options[cryptSelect.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            if (price && !isNaN(price) && parseFloat(price) > 0) {
                priceInput.value = parseFloat(price).toFixed(2);
                calculateFinancing();
            }
        }

        cryptSelect.addEventListener('change', updatePrice);
        
        if (cryptSelect.value) {
            updatePrice();
        }
    }

    // === Manejo del campo de enganche para pago mixto ===
    const paymentTypeSelect = document.getElementById('payment_type');
    const downPaymentField = document.getElementById('down_payment_field');
    const downPaymentInput = document.getElementById('down_payment');

    if (paymentTypeSelect && downPaymentField) {
        paymentTypeSelect.addEventListener('change', function() {
            if (this.value === 'mixed') {
                downPaymentField.style.display = 'block';
                downPaymentInput.required = true;
                // Sugerir 10% del precio total
                const price = parseFloat(priceInput.value) || 0;
                if (price > 0) {
                    downPaymentInput.value = (price * 0.10).toFixed(2);
                }
            } else {
                downPaymentField.style.display = 'none';
                downPaymentInput.required = false;
                downPaymentInput.value = '';
            }
            calculateFinancing();
        });

        // Initial check
        if (paymentTypeSelect.value === 'mixed') {
            downPaymentField.style.display = 'block';
            downPaymentInput.required = true;
            const price = parseFloat(priceInput.value) || 0;
            if (price > 0 && !downPaymentInput.value) {
                downPaymentInput.value = (price * 0.10).toFixed(2);
            }
        }
    }

    // === Escuchar cambios en enganche y mensualidades para recalcular ===
    if (downPaymentInput) {
        downPaymentInput.addEventListener('input', calculateFinancing);
        downPaymentInput.addEventListener('change', calculateFinancing);
    }

    const installmentsInput = document.getElementById('installments_count');
    if (installmentsInput) {
        installmentsInput.addEventListener('input', calculateFinancing);
        installmentsInput.addEventListener('change', calculateFinancing);
    }

    // === Función principal para calcular financiamiento ===
    function calculateFinancing() {
        const price = parseFloat(priceInput.value) || 0;
        const paymentType = paymentTypeSelect ? paymentTypeSelect.value : '';
        const downPayment = parseFloat(downPaymentInput.value) || 0;
        const months = parseInt(installmentsInput.value) || 0;

        const financedAmountInput = document.getElementById('financed_amount');
        const interestRateInput = document.getElementById('interest_rate');
        const monthlyPaymentInput = document.getElementById('monthly_payment');

        if (!financedAmountInput || !interestRateInput || !monthlyPaymentInput) return;

        let financedAmount = 0;
        let interestRate = 0;
        let monthlyPayment = 0;

        if (paymentType === 'cash') {
            financedAmount = 0;
            interestRate = 0;
            monthlyPayment = 0;
        } else if (paymentType === 'mixed') {
            financedAmount = price - downPayment;
            
            if (months > 0 && financedAmount > 0) {
                interestRate = interestRatesMap[months] || 0;
                
                if (interestRate > 0) {
                    const i = interestRate / 100 / 12;
                    const n = months;
                    monthlyPayment = financedAmount * (i * Math.pow(1 + i, n)) / (Math.pow(1 + i, n) - 1);
                } else {
                    monthlyPayment = financedAmount / months;
                }
            }
        } else if (paymentType === 'installments') {
            financedAmount = price;
            
            if (months > 0) {
                interestRate = interestRatesMap[months] || 0;
                
                if (interestRate > 0) {
                    const i = interestRate / 100 / 12;
                    const n = months;
                    monthlyPayment = financedAmount * (i * Math.pow(1 + i, n)) / (Math.pow(1 + i, n) - 1);
                } else {
                    monthlyPayment = financedAmount / months;
                }
            }
        }

        financedAmountInput.value = financedAmount.toFixed(2);
        interestRateInput.value = interestRate.toFixed(2);
        monthlyPaymentInput.value = monthlyPayment.toFixed(2);
    }

    // === Calcular fecha de vencimiento basada en mensualidades ===
    const startDateInput = document.querySelector('input[name="start_date"]');

    if (installmentsInput && startDateInput && endDateInput) {
        installmentsInput.addEventListener('input', function() {
            const months = parseInt(this.value);
            if (months > 0 && startDateInput.value) {
                const startDate = new Date(startDateInput.value + 'T00:00:00');
                const endDate = new Date(startDate);
                endDate.setMonth(endDate.getMonth() + months);

                const year = endDate.getFullYear();
                const month = String(endDate.getMonth() + 1).padStart(2, '0');
                const day = String(endDate.getDate()).padStart(2, '0');

                endDateInput.value = `${year}-${month}-${day}`;
            }
        });

        startDateInput.addEventListener('change', function() {
            const months = parseInt(installmentsInput.value);
            if (months > 0 && this.value) {
                const startDate = new Date(this.value + 'T00:00:00');
                const endDate = new Date(startDate);
                endDate.setMonth(endDate.getMonth() + months);

                const year = endDate.getFullYear();
                const month = String(endDate.getMonth() + 1).padStart(2, '0');
                const day = String(endDate.getDate()).padStart(2, '0');

                endDateInput.value = `${year}-${month}-${day}`;
            }
        });
    }

    // === VERIFICACIÓN DE TELÉFONO VÍA WHATSAPP ===
    const customerSelect = document.getElementById('customer_id');
    const verificationSection = document.getElementById('verification-section');
    const noPhoneMessage = document.getElementById('no-phone-message');
    const alreadyVerifiedMessage = document.getElementById('already-verified-message');
    const btnSendCode = document.getElementById('btn-send-code');
    const btnVerifyCode = document.getElementById('btn-verify-code');
    const codeInputSection = document.getElementById('code-input-section');
    const verificationCodeInput = document.getElementById('verification-code');
    const loadingSpinner = document.getElementById('loading-spinner');
    const verificationMessage = document.getElementById('verification-message');
    const customerPhoneSpan = document.getElementById('customer-phone');
    const verifiedBadge = document.getElementById('verified-badge');
    const isPhoneVerifiedInput = document.getElementById('is-phone-verified');

    let selectedCustomerId = null;

    // Escuchar cambio en el select de cliente
    if (customerSelect) {
        customerSelect.addEventListener('change', function() {
            const customerId = this.value;
            
            // Ocultar sección de verificación inicialmente
            verificationSection.classList.add('hidden');
            noPhoneMessage.classList.add('hidden');
            alreadyVerifiedMessage.classList.add('hidden');
            codeInputSection.classList.add('hidden');
            verifiedBadge.classList.add('hidden');
            isPhoneVerifiedInput.value = '0';
            
            if (!customerId) {
                selectedCustomerId = null;
                return;
            }

            selectedCustomerId = customerId;

            // Verificar estado del teléfono del cliente
            fetch(`/api/customers/${customerId}/phone-status`)
                .then(response => response.json())
                .then(data => {
                    if (data.has_phone) {
                        customerPhoneSpan.textContent = data.phone;
                        verificationSection.classList.remove('hidden');
                        
                        if (data.is_verified) {
                            // Ya está verificado
                            alreadyVerifiedMessage.classList.remove('hidden');
                            verifiedBadge.classList.remove('hidden');
                            btnSendCode.disabled = true;
                            btnSendCode.classList.add('opacity-50', 'cursor-not-allowed');
                            isPhoneVerifiedInput.value = '1';
                        } else {
                            // No verificado, mostrar botón para enviar código
                            btnSendCode.disabled = false;
                            btnSendCode.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    } else {
                        // No tiene teléfono
                        noPhoneMessage.classList.remove('hidden');
                    }
                })
                .catch(error => {
                    console.error('Error al verificar teléfono:', error);
                    verificationMessage.innerHTML = '<span class="text-red-600">Error al cargar información del cliente</span>';
                });
        });
    }

    // Enviar código de verificación
    if (btnSendCode) {
        btnSendCode.addEventListener('click', function() {
            if (!selectedCustomerId) {
                verificationMessage.innerHTML = '<span class="text-red-600">Debe seleccionar un cliente primero</span>';
                return;
            }

            loadingSpinner.classList.remove('hidden');
            btnSendCode.disabled = true;
            verificationMessage.textContent = '';

            fetch('{{ route("commercial.contracts.send-verification-code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ customer_id: selectedCustomerId })
            })
            .then(response => response.json())
            .then(data => {
                loadingSpinner.classList.add('hidden');
                
                if (data.success) {
                    verificationMessage.innerHTML = '<span class="text-green-600">' + data.message + '</span>';
                    codeInputSection.classList.remove('hidden');
                    verificationCodeInput.focus();
                } else {
                    verificationMessage.innerHTML = '<span class="text-red-600">' + data.message + '</span>';
                    btnSendCode.disabled = false;
                }
            })
            .catch(error => {
                loadingSpinner.classList.add('hidden');
                btnSendCode.disabled = false;
                verificationMessage.innerHTML = '<span class="text-red-600">Error de conexión. Intente nuevamente.</span>';
                console.error('Error:', error);
            });
        });
    }

    // Verificar código ingresado
    if (btnVerifyCode) {
        btnVerifyCode.addEventListener('click', function() {
            const code = verificationCodeInput.value.trim();
            
            if (!code || code.length !== 6) {
                verificationMessage.innerHTML = '<span class="text-red-600">Ingrese un código de 6 dígitos</span>';
                return;
            }

            if (!selectedCustomerId) {
                verificationMessage.innerHTML = '<span class="text-red-600">Debe seleccionar un cliente primero</span>';
                return;
            }

            loadingSpinner.classList.remove('hidden');
            verificationMessage.textContent = '';

            fetch('{{ route("commercial.contracts.verify-code") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    customer_id: selectedCustomerId,
                    code: code
                })
            })
            .then(response => response.json())
            .then(data => {
                loadingSpinner.classList.add('hidden');
                
                if (data.success) {
                    verificationMessage.innerHTML = '<span class="text-green-600">' + data.message + '</span>';
                    verifiedBadge.classList.remove('hidden');
                    codeInputSection.classList.add('hidden');
                    btnSendCode.disabled = true;
                    btnSendCode.classList.add('opacity-50', 'cursor-not-allowed');
                    isPhoneVerifiedInput.value = '1';
                    
                    // Marcar como verificado para permitir envío del formulario
                    setTimeout(() => {
                        verificationMessage.innerHTML += '<br><span class="text-xs text-gray-500">✅ Teléfono verificado. Puede continuar con la creación del contrato.</span>';
                    }, 500);
                } else {
                    verificationMessage.innerHTML = '<span class="text-red-600">' + data.message + '</span>';
                    verificationCodeInput.value = '';
                    verificationCodeInput.focus();
                }
            })
            .catch(error => {
                loadingSpinner.classList.add('hidden');
                verificationMessage.innerHTML = '<span class="text-red-600">Error de conexión. Intente nuevamente.</span>';
                console.error('Error:', error);
            });
        });
    }
});
</script>
@endpush
@endsection
