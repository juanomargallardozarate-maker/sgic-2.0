@extends('layouts.app')

@section('title', 'Editar Contrato')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-4xl mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('commercial.contracts.show', $contract) }}" class="text-emerald-600 hover:text-emerald-900 mb-2 inline-block">
                <i class="fa-solid fa-arrow-left mr-2"></i> Volver al Contrato
            </a>
            <h1 class="text-2xl font-bold text-gray-900">Editar Contrato {{ $contract->contract_number }}</h1>
            <p class="text-sm text-gray-600 mt-1">Solo contratos en borrador pueden editarse</p>
        </div>

        {{-- Form --}}
        <form method="POST" action="{{ route('commercial.contracts.update', $contract) }}" class="bg-white rounded-lg shadow p-6">
            @csrf
            @method('PUT')

            {{-- Datos del Cliente --}}
            <div class="mb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Información del Cliente</h3>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cliente *</label>
                        <select name="customer_id" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('customer_id') border-red-500 @enderror">
                            <option value="">Seleccionar cliente...</option>
                            @foreach($customers as $customer)
                                <option value="{{ $customer->id }}" {{ old('customer_id', $contract->customer_id) == $customer->id ? 'selected' : '' }}>
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
                        <select name="contract_type_id" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('contract_type_id') border-red-500 @enderror">
                            <option value="">Seleccionar tipo...</option>
                            @foreach($contractTypes as $type)
                                <option value="{{ $type->id }}" {{ old('contract_type_id', $contract->contract_type_id) == $type->id ? 'selected' : '' }}>
                                    {{ $type->name }}
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
                        <select name="crypt_id" id="crypt_id_edit" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('crypt_id') border-red-500 @enderror">
                            <option value="">Seleccionar cripta...</option>
                            @foreach($availableCrypts as $crypt)
                                <option value="{{ $crypt->id }}" {{ old('crypt_id', $contract->crypt_id) == $crypt->id ? 'selected' : '' }}
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
                        <input type="number" step="0.01" name="price" value="{{ old('price', $contract->price) }}" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('price') border-red-500 @enderror">
                        @error('price')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Cuota Anual de Mantenimiento *</label>
                        <input type="number" step="0.01" name="annual_maintenance_fee" value="{{ old('annual_maintenance_fee', $contract->annual_maintenance_fee) }}" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('annual_maintenance_fee') border-red-500 @enderror">
                        @error('annual_maintenance_fee')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Pago *</label>
                        <select name="payment_type" required
                                class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('payment_type') border-red-500 @enderror">
                            <option value="cash" {{ old('payment_type', $contract->payment_type) === 'cash' ? 'selected' : '' }}>Contado</option>
                            <option value="installments" {{ old('payment_type', $contract->payment_type) === 'installments' ? 'selected' : '' }}>Parcialidades</option>
                            <option value="mixed" {{ old('payment_type', $contract->payment_type) === 'mixed' ? 'selected' : '' }}>Mixto</option>
                        </select>
                        @error('payment_type')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Número de Parcialidades</label>
                        <input type="number" name="installments_count" id="installments_count" value="{{ old('installments_count', $contract->installments_count) }}" min="1"
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                        @error('installments_count')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div id="down_payment_field" style="{{ old('payment_type', $contract->payment_type) === 'mixed' ? '' : 'display: none;' }}">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Enganche / Anticipo *</label>
                        <input type="number" step="0.01" name="down_payment" id="down_payment" value="{{ old('down_payment', $contract->down_payment) }}" min="0"
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
                        <input type="date" name="start_date" value="{{ old('start_date', $contract->start_date->format('Y-m-d')) }}" required
                               class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500 @error('start_date') border-red-500 @enderror">
                        @error('start_date')
                            <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-1">Fecha de Vencimiento</label>
                        <input type="date" name="end_date" value="{{ old('end_date', $contract->end_date?->format('Y-m-d')) }}"
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
                          class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">{{ old('notes', $contract->notes) }}</textarea>
                @error('notes')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex justify-end gap-3 pt-4 border-t">
                <a href="{{ route('commercial.contracts.show', $contract) }}" 
                   class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50 transition-colors">
                    Cancelar
                </a>
                <button type="submit" 
                        class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md font-medium transition-colors">
                    <i class="fa-solid fa-save mr-2"></i>
                    Actualizar Contrato
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // === Actualizar precio al seleccionar cripta (EDIT) ===
    const cryptSelectEdit = document.getElementById('crypt_id_edit');
    const priceInputEdit = document.querySelector('input[name="price"]');
    
    if (cryptSelectEdit && priceInputEdit) {
        function updatePriceEdit() {
            const selectedOption = cryptSelectEdit.options[cryptSelectEdit.selectedIndex];
            const price = selectedOption.getAttribute('data-price');
            console.log('Precio seleccionado (edit):', price);
            if (price && !isNaN(price) && parseFloat(price) > 0) {
                priceInputEdit.value = parseFloat(price).toFixed(2);
                priceInputEdit.dispatchEvent(new Event('input', { bubbles: true }));
                priceInputEdit.dispatchEvent(new Event('change', { bubbles: true }));
            }
        }
        
        cryptSelectEdit.addEventListener('change', updatePriceEdit);
        cryptSelectEdit.addEventListener('input', updatePriceEdit);
        
        if (cryptSelectEdit.value) {
            updatePriceEdit();
        }
    }

    // === Manejo del campo de enganche para pago mixto ===
    const paymentTypeSelect = document.querySelector('select[name="payment_type"]');
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
    const endDateInput = document.querySelector('input[name="end_date"]');
    
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
