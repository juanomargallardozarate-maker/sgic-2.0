@extends('layouts.app')

@section('title', 'Nueva Reserva')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-3xl">
    <!-- Header -->
    <div class="mb-6">
        <a href="{{ route('commercial.reservations.index') }}" 
           class="text-blue-600 hover:text-blue-900 flex items-center gap-2 mb-4">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Volver al listado
        </a>
        <h1 class="text-2xl font-bold text-gray-800">Nueva Reserva de Cripta</h1>
        <p class="text-gray-600 mt-1">Reserva temporalmente una cripta para un cliente (72 horas por defecto)</p>
    </div>

    <!-- Error Messages -->
    @if($errors->any())
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
            <ul class="list-disc list-inside">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Form -->
    <form action="{{ route('commercial.reservations.store') }}" method="POST" class="bg-white rounded-lg shadow p-6">
        @csrf

        <!-- Cliente -->
        <div class="mb-4">
            <label for="customer_id" class="block text-sm font-medium text-gray-700 mb-2">
                Cliente *
            </label>
            <select name="customer_id" id="customer_id" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('customer_id') border-red-500 @enderror">
                <option value="">Seleccionar cliente...</option>
                @foreach($customers as $customer)
                    <option value="{{ $customer->id }}" {{ old('customer_id') == $customer->id ? 'selected' : '' }}>
                        {{ $customer->name }} ({{ $customer->email }})
                    </option>
                @endforeach
            </select>
            @error('customer_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Cripta -->
        <div class="mb-4">
            <label for="crypt_id" class="block text-sm font-medium text-gray-700 mb-2">
                Cripta *
            </label>
            <select name="crypt_id" id="crypt_id" required
                    class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('crypt_id') border-red-500 @enderror">
                <option value="">Seleccionar cripta...</option>
                @foreach($availableCrypts as $crypt)
                    <option value="{{ $crypt->id }}" {{ old('crypt_id') == $crypt->id ? 'selected' : '' }}>
                        {{ $crypt->fullCode }} - {{ $crypt->cryptType?->name ?? 'Sin tipo' }} 
                        (${{ number_format($crypt->price, 2) }})
                    </option>
                @endforeach
            </select>
            @error('crypt_id')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">Solo se muestran criptas disponibles</p>
        </div>

        <!-- Monto del depósito -->
        <div class="mb-4">
            <label for="deposit_amount" class="block text-sm font-medium text-gray-700 mb-2">
                Monto del depósito (opcional)
            </label>
            <input type="number" name="deposit_amount" id="deposit_amount" step="0.01" min="0"
                   value="{{ old('deposit_amount', 0) }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('deposit_amount') border-red-500 @enderror">
            @error('deposit_amount')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">Monto que el cliente paga como señal de reserva</p>
        </div>

        <!-- Horas de expiración -->
        <div class="mb-4">
            <label for="expiration_hours" class="block text-sm font-medium text-gray-700 mb-2">
                Duración de la reserva (horas)
            </label>
            <input type="number" name="expiration_hours" id="expiration_hours" min="1" max="720"
                   value="{{ old('expiration_hours', 72) }}"
                   class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('expiration_hours') border-red-500 @enderror">
            @error('expiration_hours')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
            <p class="text-xs text-gray-500 mt-1">Por defecto: 72 horas. Máximo: 720 horas (30 días)</p>
        </div>

        <!-- Notas -->
        <div class="mb-6">
            <label for="notes" class="block text-sm font-medium text-gray-700 mb-2">
                Notas (opcional)
            </label>
            <textarea name="notes" id="notes" rows="3"
                      class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500 @error('notes') border-red-500 @enderror">{{ old('notes') }}</textarea>
            @error('notes')
                <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
            @enderror
        </div>

        <!-- Actions -->
        <div class="flex gap-4">
            <button type="submit" 
                    class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 rounded-lg font-medium transition-colors">
                Crear Reserva
            </button>
            <a href="{{ route('commercial.reservations.index') }}" 
               class="px-6 py-3 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors text-center">
                Cancelar
            </a>
        </div>
    </form>

    <!-- Info Card -->
    <div class="mt-6 bg-blue-50 border border-blue-200 rounded-lg p-4">
        <h3 class="font-semibold text-blue-800 mb-2">ℹ️ Información importante:</h3>
        <ul class="text-sm text-blue-700 space-y-1">
            <li>• La cripta quedará marcada como "reservada" y no podrá ser vendida a otro cliente</li>
            <li>• La reserva expira automáticamente al finalizar el plazo establecido</li>
            <li>• Puedes extender la reserva antes de que expire si es necesario</li>
            <li>• Al convertir la reserva en contrato, la cripta cambiará a "ocupada"</li>
        </ul>
    </div>
</div>
@endsection
