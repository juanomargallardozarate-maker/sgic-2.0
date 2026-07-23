@extends('layouts.app')

@section('title', 'Detalle del Contrato')

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="max-w-6xl mx-auto">
        {{-- Header --}}
        <div class="mb-6">
            <a href="{{ route('inventory.commercial.contracts.index') }}" class="text-emerald-600 hover:text-emerald-900 mb-2 inline-block">
                <i class="fa-solid fa-arrow-left mr-2"></i> Volver a Contratos
            </a>
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-900">{{ $contract->contract_number }}</h1>
                    <p class="text-sm text-gray-600 mt-1">
                        {{ $contract->contractType->name }} - {{ $contract->is_temporary ? 'Temporal' : 'Perpetuo' }}
                    </p>
                </div>
                @php
                    $statusColors = [
                        'draft' => 'bg-gray-100 text-gray-800',
                        'active' => 'bg-emerald-100 text-emerald-800',
                        'expired' => 'bg-red-100 text-red-800',
                        'grace_period' => 'bg-yellow-100 text-yellow-800',
                        'decaying' => 'bg-purple-100 text-purple-800',
                        'terminated' => 'bg-gray-100 text-gray-800',
                        'renewed' => 'bg-blue-100 text-blue-800',
                    ];
                    $statusLabels = [
                        'draft' => 'Borrador',
                        'active' => 'Activo',
                        'expired' => 'Vencido',
                        'grace_period' => 'Periodo de Gracia',
                        'decaying' => 'Decadencia',
                        'terminated' => 'Terminado',
                        'renewed' => 'Renovado',
                    ];
                @endphp
                <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $statusColors[$contract->status] ?? 'bg-gray-100 text-gray-800' }}">
                    {{ $statusLabels[$contract->status] ?? $contract->status }}
                </span>
            </div>
        </div>

        {{-- Success/Error Messages --}}
        @if(session('success'))
            <div class="bg-emerald-50 border-l-4 border-emerald-500 p-4 mb-6 rounded">
                <p class="text-emerald-700">{{ session('success') }}</p>
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded">
                <ul class="list-disc list-inside text-red-700">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Main Info --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Contract Details --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Información del Contrato</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <span class="text-sm text-gray-500">Cliente</span>
                            <p class="font-medium text-gray-900">{{ $contract->customer->name }}</p>
                            <p class="text-xs text-gray-500">RFC: {{ $contract->customer->rfc_encrypted ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Cripta</span>
                            <p class="font-medium text-gray-900">{{ $contract->crypt->full_code }}</p>
                            <p class="text-xs text-gray-500">{{ $contract->crypt->cryptType->name ?? 'N/A' }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Fecha de Inicio</span>
                            <p class="font-medium text-gray-900">{{ $contract->start_date->format('d/m/Y') }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Fecha de Vencimiento</span>
                            <p class="font-medium text-gray-900">
                                @if($contract->end_date)
                                    {{ $contract->end_date->format('d/m/Y') }}
                                    @if($contract->days_until_expiry && $contract->days_until_expiry <= 90)
                                        <span class="text-red-600 text-xs">({{ $contract->days_until_expiry }} días restantes)</span>
                                    @endif
                                @else
                                    Perpetuo
                                @endif
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Precio Total</span>
                            <p class="font-medium text-gray-900">${{ number_format($contract->price, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Cuota Anual</span>
                            <p class="font-medium text-gray-900">${{ number_format($contract->annual_maintenance_fee, 2) }}</p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Tipo de Pago</span>
                            <p class="font-medium text-gray-900">
                                @php
                                    $paymentTypes = ['cash' => 'Contado', 'installments' => 'Parcialidades', 'mixed' => 'Mixto'];
                                @endphp
                                {{ $paymentTypes[$contract->payment_type] ?? $contract->payment_type }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm text-gray-500">Firmado</span>
                            <p class="font-medium text-gray-900">
                                @if($contract->signed_at)
                                    {{ $contract->signed_at->format('d/m/Y H:i') }}
                                @else
                                    <span class="text-yellow-600">Pendiente de firma</span>
                                @endif
                            </p>
                        </div>
                    </div>

                    @if($contract->notes)
                        <div class="mt-4 pt-4 border-t">
                            <span class="text-sm text-gray-500">Notas</span>
                            <p class="text-gray-900 mt-1">{{ $contract->notes }}</p>
                        </div>
                    @endif
                </div>

                {{-- Beneficiaries --}}
                @if($contract->beneficiaries->count() > 0)
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Beneficiarios</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase">Relación</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase">Principal</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($contract->beneficiaries as $beneficiary)
                                    <tr>
                                        <td class="py-2">{{ $beneficiary->customer->name }}</td>
                                        <td class="py-2">{{ $beneficiary->relationship }}</td>
                                        <td class="py-2">
                                            @if($beneficiary->is_primary)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    Principal
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif

                {{-- Heirs --}}
                @if($contract->heirs->count() > 0)
                    <div class="bg-white rounded-lg shadow p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Herederos</h3>
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase">Nombre</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase">Porcentaje</th>
                                    <th class="text-left text-xs font-medium text-gray-500 uppercase">Designado</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                @foreach($contract->heirs as $heir)
                                    <tr>
                                        <td class="py-2">{{ $heir->customer->name }}</td>
                                        <td class="py-2">{{ number_format($heir->inheritance_percent, 2) }}%</td>
                                        <td class="py-2">
                                            @if($heir->is_designated)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                                    Sí
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            {{-- Sidebar --}}
            <div class="space-y-6">
                {{-- Financial Summary --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Resumen Financiero</h3>
                    <div class="space-y-3">
                        <div class="flex justify-between">
                            <span class="text-gray-600">Precio Total</span>
                            <span class="font-medium">${{ number_format($contract->price, 2) }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">Total Pagado</span>
                            <span class="font-medium text-emerald-600">${{ number_format($totalPaid, 2) }}</span>
                        </div>
                        <div class="flex justify-between pt-3 border-t">
                            <span class="text-gray-900 font-semibold">Saldo Pendiente</span>
                            <span class="font-bold {{ $totalDebts > 0 ? 'text-red-600' : 'text-emerald-600' }}">
                                ${{ number_format($totalDebts, 2) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="bg-white rounded-lg shadow p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Acciones</h3>
                    <div class="space-y-2">
                        @if($contract->status === 'draft')
                            <form method="POST" action="{{ route('inventory.commercial.contracts.sign', $contract) }}" class="mb-2">
                                @csrf
                                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                                    <i class="fa-solid fa-signature mr-2"></i>
                                    Firmar Contrato
                                </button>
                            </form>
                            <a href="{{ route('inventory.commercial.contracts.edit', $contract) }}" 
                               class="block w-full bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-md font-medium transition-colors text-center">
                                <i class="fa-solid fa-pen mr-2"></i>
                                Editar Contrato
                            </a>
                        @endif

                        @if($contract->is_temporary && in_array($contract->status, ['active', 'expired', 'grace_period']))
                            <button onclick="document.getElementById('renewModal').classList.remove('hidden')"
                                    class="w-full bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                                <i class="fa-solid fa-rotate mr-2"></i>
                                Renovar Contrato
                            </button>
                        @endif

                        @if(!$contract->is_succession_pending && $canTransfer)
                            <button onclick="document.getElementById('successionModal').classList.remove('hidden')"
                                    class="w-full bg-orange-600 hover:bg-orange-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                                <i class="fa-solid fa-users mr-2"></i>
                                Iniciar Sucesión
                            </button>
                        @elseif($contract->is_succession_pending)
                            <button onclick="document.getElementById('completeSuccessionModal').classList.remove('hidden')"
                                    class="w-full bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                                <i class="fa-solid fa-check mr-2"></i>
                                Completar Sucesión
                            </button>
                        @endif

                        <button class="w-full bg-gray-100 hover:bg-gray-200 text-gray-700 px-4 py-2 rounded-md font-medium transition-colors">
                            <i class="fa-solid fa-file-pdf mr-2"></i>
                            Descargar PDF
                        </button>
                    </div>
                </div>

                {{-- Audit Info --}}
                <div class="bg-gray-50 rounded-lg p-4">
                    <h4 class="text-sm font-semibold text-gray-700 mb-2">Información de Auditoría</h4>
                    <div class="text-xs text-gray-600 space-y-1">
                        <div>Creado: {{ $contract->created_at->format('d/m/Y H:i') }}</div>
                        <div>Por: {{ $contract->createdBy?->name ?? 'N/A' }}</div>
                        @if($contract->signed_at)
                            <div>Firmado: {{ $contract->signed_at->format('d/m/Y H:i') }}</div>
                            <div>Hash: <code class="bg-gray-200 px-1 rounded">{{ substr($contract->signature_hash, 0, 16) }}...</code></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Renew Modal --}}
<div id="renewModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Renovar Contrato</h3>
        <form method="POST" action="{{ route('inventory.commercial.contracts.renew', $contract) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nueva Fecha de Vencimiento *</label>
                <input type="date" name="new_end_date" required min="{{ $contract->end_date->format('Y-m-d') }}"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nuevo Precio *</label>
                <input type="number" step="0.01" name="new_price" required
                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('renewModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-700 text-white rounded-md">
                    Renovar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Succession Modal --}}
<div id="successionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Iniciar Proceso de Sucesión</h3>
        <form method="POST" action="{{ route('inventory.commercial.contracts.succession.start', $contract) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">URL del Documento Legal *</label>
                <input type="text" name="heir_document_url" required placeholder="https://..."
                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                <p class="text-xs text-gray-500 mt-1">URL del documento de declaratoria de herederos o testamento</p>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('successionModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-orange-600 hover:bg-orange-700 text-white rounded-md">
                    Iniciar Sucesión
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Complete Succession Modal --}}
<div id="completeSuccessionModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
        <h3 class="text-lg font-semibold mb-4">Completar Sucesión</h3>
        <form method="POST" action="{{ route('inventory.commercial.contracts.succession.complete', $contract) }}">
            @csrf
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Nuevo Titular *</label>
                <select name="new_customer_id" required class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Seleccionar heredero...</option>
                    @foreach($contract->heirs as $heir)
                        <option value="{{ $heir->customer_id }}">{{ $heir->customer->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex justify-end gap-3">
                <button type="button" onclick="document.getElementById('completeSuccessionModal').classList.add('hidden')"
                        class="px-4 py-2 border border-gray-300 rounded-md text-gray-700 hover:bg-gray-50">
                    Cancelar
                </button>
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md">
                    Completar
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
