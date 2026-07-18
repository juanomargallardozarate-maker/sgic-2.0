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
                        <input type="number" step="0.01" name="price" id="price" value="{{ old('price') }}" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('price') border-red-500 @enderror">
                        @error('price')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cuota Anual de Mantenimiento *</label>
                        <input type="number" step="0.01" name="annual_maintenance_fee" value="{{ old('annual_maintenance_fee') }}" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('annual_maintenance_fee') border-red-500 @enderror">
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
                        <p class="text-xs text-gray-500 mt-1">Monto inicial que paga el cliente</p>
                        @error('down_payment')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
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
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Vencimiento</label>
                        <input type="date" name="end_date" value="{{ old('end_date') }}"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('end_date') border-red-500 @enderror">
                        <p class="text-xs text-gray-500 mt-1">Obligatorio para contratos temporales</p>
                        @error('end_date')
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
    const endDateInput = endDateField.querySelector('input');
    const endDateLabel = endDateField.querySelector('label');

    function toggleEndDateField() {
        const selectedOption = contractTypeSelect.options[contractTypeSelect.selectedIndex];
        const isTemporary = selectedOption.text.includes('años');

        if (isTemporary) {
            endDateLabel.innerHTML = 'Fecha de Vencimiento *';
            endDateInput.required = true;
        } else {
            endDateLabel.innerHTML = 'Fecha de Vencimiento';
            endDateInput.required = false;
            endDateInput.value = '';
        }
    }

    contractTypeSelect.addEventListener('change', toggleEndDateField);
    toggleEndDateField(); // Initial check

    // === Actualizar precio al seleccionar cripta ===
    const cryptSelect = document.getElementById('crypt_id');
    const priceInput = document.getElementById('price');
    
    if (cryptSelect && priceInput) {
        function updatePrice() {
            const selectedOption = cryptSelect.options[cryptSelect.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            console.log('Precio seleccionado:', price); // Debug
            if (price && !isNaN(price) && parseFloat(price) > 0) {
                priceInput.value = parseFloat(price).toFixed(2);
                // Disparar eventos para asegurar reactividad
                priceInput.dispatchEvent(new Event('input', { bubbles: true }));
                priceInput.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
        
        cryptSelect.addEventListener('change', updatePrice);
        cryptSelect.addEventListener('input', updatePrice);
        
        // Trigger initial price update if crypt is pre-selected
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
            } else {
                downPaymentField.style.display = 'none';
                downPaymentInput.required = false;
                downPaymentInput.value = '';
            }
        });
        
        // Initial check
        if (paymentTypeSelect.value === 'mixed') {
            downPaymentField.style.display = 'block';
            downPaymentInput.required = true;
        }
    }

    // === Calcular fecha de vencimiento basada en mensualidades ===
    const installmentsInput = document.getElementById('installments_count');
    const startDateInput = document.querySelector('input[name="start_date"]');
    
    if (installmentsInput && startDateInput && endDateInput) {
        installmentsInput.addEventListener('input', function() {
            const months = parseInt(this.value);
            if (months > 0 && startDateInput.value) {
                const startDate = new Date(startDateInput.value + 'T00:00:00');
                const endDate = new Date(startDate);
                endDate.setMonth(endDate.getMonth() + months);
                
                // Formatear fecha como YYYY-MM-DD
                const year = endDate.getFullYear();
                const month = String(endDate.getMonth() + 1).padStart(2, '0');
                const day = String(endDate.getDate()).padStart(2, '0');
                
                endDateInput.value = `${year}-${month}-${day}`;
            }
        });
        
        // También actualizar cuando cambia la fecha de inicio
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
});
</script>
@endpush
@endsection
