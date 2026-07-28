@extends('layouts.app')

@section('title', 'Detalle de Reserva')

@section('content')
<div class="container mx-auto px-4 py-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <div>
            <a href="{{ route('inventory.commercial.reservations.index') }}" 
               class="text-blue-600 hover:text-blue-900 flex items-center gap-2 mb-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Volver al listado
            </a>
            <h1 class="text-2xl font-bold text-gray-800">Reserva #{{ $reservation->id }}</h1>
            <p class="text-gray-600 mt-1">Creada el {{ $reservation->created_at->format('d/m/Y H:i') }}</p>
        </div>
        <div class="flex gap-2">
            @if($reservation->isActive())
                <button onclick="document.getElementById('extendModal').classList.remove('hidden')"
                        class="bg-yellow-600 hover:bg-yellow-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Extender
                </button>
                <button onclick="document.getElementById('convertModal').classList.remove('hidden')"
                        {{ !$canConvert ? 'disabled' : '' }}
                        class="{{ !$canConvert ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700' }} text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Convertir a Contrato
                </button>
                <button onclick="document.getElementById('cancelModal').classList.remove('hidden')"
                        class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                    Cancelar
                </button>
            @endif
        </div>
    </div>

    <!-- Success/Error Messages -->
    @if(session('success'))
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Status Card -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Estado de la Reserva</h2>
                
                @php
                    $statusColors = [
                        'active' => 'bg-green-100 text-green-800 border-green-300',
                        'converted' => 'bg-blue-100 text-blue-800 border-blue-300',
                        'expired' => 'bg-red-100 text-red-800 border-red-300',
                        'cancelled' => 'bg-gray-100 text-gray-800 border-gray-300',
                    ];
                    $statusLabels = [
                        'active' => 'Activa',
                        'converted' => 'Convertida',
                        'expired' => 'Expirada',
                        'cancelled' => 'Cancelada',
                    ];
                @endphp

                <div class="flex items-center gap-4 mb-4">
                    <span class="px-4 py-2 text-lg font-semibold rounded-full border {{ $statusColors[$reservation->status] }}">
                        {{ $statusLabels[$reservation->status] ?? ucfirst($reservation->status) }}
                    </span>
                    
                    @if($isExpired && $reservation->isActive())
                        <span class="text-red-600 font-semibold animate-pulse">⚠️ Expirada</span>
                    @endif
                </div>

                @if($reservation->expires_at)
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p class="text-sm text-gray-600">Fecha de expiración</p>
                            <p class="text-lg font-semibold {{ $isExpired ? 'text-red-600' : 'text-gray-800' }}">
                                {{ $reservation->expires_at->format('d/m/Y H:i') }}
                            </p>
                        </div>
                        @if($reservation->isActive() && !$isExpired && $reservation->days_until_expiry !== null)
                            <div>
                                <p class="text-sm text-gray-600">Tiempo restante</p>
                                <p class="text-lg font-semibold {{ $reservation->days_until_expiry <= 1 ? 'text-red-600' : 'text-green-600' }}">
                                    {{ $reservation->days_until_expiry }} días
                                    @if($reservation->days_until_expiry < 1)
                                        ({{ floor($timeRemaining / 3600) }} horas)
                                    @endif
                                </p>
                            </div>
                        @endif
                    </div>
                @endif
            </div>

            <!-- Customer & Crypt Info -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Información Detallada</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Cliente -->
                    <div>
                        <h3 class="font-medium text-gray-700 mb-2">Cliente</h3>
                        <div class="space-y-1 text-sm">
                            <p><span class="text-gray-600">Nombre:</span> {{ $reservation->customer->name }}</p>
                            <p><span class="text-gray-600">Email:</span> {{ $reservation->customer->email }}</p>
                            <p><span class="text-gray-600">Teléfono:</span> {{ $reservation->customer->phone ?? 'N/A' }}</p>
                        </div>
                    </div>

                    <!-- Cripta -->
                    <div>
                        <h3 class="font-medium text-gray-700 mb-2">Cripta</h3>
                        <div class="space-y-1 text-sm">
                            <p><span class="text-gray-600">Código:</span> {{ $reservation->crypt->fullCode }}</p>
                            <p><span class="text-gray-600">Tipo:</span> {{ $reservation->crypt->cryptType?->name ?? 'N/A' }}</p>
                            <p><span class="text-gray-600">Precio:</span> ${{ number_format($reservation->crypt->price, 2) }}</p>
                            <p><span class="text-gray-600">Ubicación:</span> 
                                {{ $reservation->crypt->level?->block?->section?->name ?? 'N/A' }} - 
                                {{ $reservation->crypt->level?->block?->code ?? 'N/A' }} - 
                                {{ $reservation->crypt->level?->code ?? 'N/A' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contract Link -->
            @if($reservation->contract)
                <div class="bg-green-50 border border-green-200 rounded-lg p-6">
                    <h2 class="text-lg font-semibold text-green-800 mb-2">✅ Contrato Asociado</h2>
                    <p class="text-green-700 mb-4">Esta reserva fue convertida exitosamente a contrato.</p>
                    <a href="{{ route('inventory.commercial.contracts.show', $reservation->contract) }}" 
                       class="inline-block bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg font-medium transition-colors">
                        Ver Contrato #{{ $reservation->contract->contract_number }}
                    </a>
                </div>
            @endif

            <!-- Notes -->
            @if($reservation->notes)
                <div class="bg-white rounded-lg shadow p-6">
                    <h2 class="text-lg font-semibold text-gray-800 mb-2">Notas</h2>
                    <p class="text-gray-700 whitespace-pre-wrap">{{ $reservation->notes }}</p>
                </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Acciones Rápidas</h2>
                <div class="space-y-2">
                    @if($reservation->isActive())
                        <button onclick="document.getElementById('extendModal').classList.remove('hidden')"
                                class="w-full text-left px-4 py-2 text-yellow-700 hover:bg-yellow-50 rounded-lg transition-colors">
                            🕐 Extender reserva
                        </button>
                        <button onclick="document.getElementById('convertModal').classList.remove('hidden')"
                                {{ !$canConvert ? 'disabled' : '' }}
                                class="w-full text-left px-4 py-2 text-green-700 hover:bg-green-50 rounded-lg transition-colors {{ !$canConvert ? 'opacity-50 cursor-not-allowed' : '' }}">
                            📄 Convertir a contrato
                        </button>
                        <button onclick="document.getElementById('cancelModal').classList.remove('hidden')"
                                class="w-full text-left px-4 py-2 text-red-700 hover:bg-red-50 rounded-lg transition-colors">
                            ❌ Cancelar reserva
                        </button>
                    @else
                        <p class="text-gray-500 text-sm">No hay acciones disponibles para este estado</p>
                    @endif
                </div>
            </div>

            <!-- Timeline -->
            <div class="bg-white rounded-lg shadow p-6">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Línea de Tiempo</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex items-start gap-2">
                        <span class="text-green-600">✓</span>
                        <div>
                            <p class="font-medium">Reserva creada</p>
                            <p class="text-gray-500">{{ $reservation->created_at->format('d/m/Y H:i') }}</p>
                        </div>
                    </div>
                    
                    @if($reservation->expires_at)
                        <div class="flex items-start gap-2">
                            <span class="text-blue-600">⏰</span>
                            <div>
                                <p class="font-medium">Expira el</p>
                                <p class="text-gray-500">{{ $reservation->expires_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif

                    @if($reservation->contract)
                        <div class="flex items-start gap-2">
                            <span class="text-green-600">✓</span>
                            <div>
                                <p class="font-medium">Convertida a contrato</p>
                                <p class="text-gray-500">{{ $reservation->updated_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Extend Modal -->
    <div id="extendModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4">Extender Reserva</h3>
            <form action="{{ route('inventory.commercial.reservations.extend', $reservation) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Horas adicionales</label>
                    <input type="number" name="hours" min="1" max="720" value="24" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <p class="text-xs text-gray-500 mt-1">Máximo: 720 horas (30 días)</p>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg">
                        Extender
                    </button>
                    <button type="button" onclick="document.getElementById('extendModal').classList.add('hidden')"
                            class="flex-1 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Convert Modal -->
    <div id="convertModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-2xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <h3 class="text-lg font-semibold mb-4">Convertir Reserva a Contrato</h3>
            <form action="{{ route('inventory.commercial.reservations.convert', $reservation) }}" method="POST">
                @csrf
                
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de contrato *</label>
                        <select name="contract_type_id" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            @foreach($contractTypes as $type)
                                <option value="{{ $type->id }}">{{ $type->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Precio *</label>
                        <input type="number" name="price" step="0.01" min="0" 
                               value="{{ $reservation->crypt->price }}" required
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Tipo de pago *</label>
                        <select name="payment_type" required
                                class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="cash">Contado</option>
                            <option value="installments">Meses sin intereses</option>
                            <option value="mixed">Mixto</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">Número de mensualidades</label>
                        <input type="number" name="installments_count" min="1" 
                               class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de inicio *</label>
                    <input type="date" name="start_date" value="{{ date('Y-m-d') }}" required
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Fecha de vencimiento (solo temporales)</label>
                    <input type="date" name="end_date" 
                           class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500">
                </div>

                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg">
                        Convertir
                    </button>
                    <button type="button" onclick="document.getElementById('convertModal').classList.add('hidden')"
                            class="flex-1 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div id="cancelModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
            <h3 class="text-lg font-semibold mb-4">Cancelar Reserva</h3>
            <form action="{{ route('inventory.commercial.reservations.cancel', $reservation) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-700 mb-2">Motivo (opcional)</label>
                    <textarea name="reason" rows="3"
                              class="w-full border border-gray-300 rounded-lg px-4 py-2 focus:outline-none focus:ring-2 focus:ring-blue-500"></textarea>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="flex-1 bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg">
                        Confirmar cancelación
                    </button>
                    <button type="button" onclick="document.getElementById('cancelModal').classList.add('hidden')"
                            class="flex-1 border border-gray-300 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-50">
                        Cancelar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
