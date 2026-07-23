@extends('layouts.app')

@section('title', 'Gestión de Contratos')

@section('content')
<div class="container mx-auto px-4 py-8">
    {{-- Header --}}
    <div class="flex justify-between items-center mb-6">
        <div>
            <h1 class="text-2xl font-bold text-gray-900">Contratos</h1>
            <p class="text-sm text-gray-600 mt-1">Gestión de contratos perpetuos y temporales</p>
        </div>
        <a href="{{ route('commercial.contracts.create') }}" 
           class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg font-medium transition-colors flex items-center">
            <i class="fa-solid fa-plus mr-2"></i>
            Nuevo Contrato
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-6">
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-blue-500">
            <div class="text-sm text-gray-600">Por Vencer (90 días)</div>
            <div class="text-2xl font-bold text-gray-900">{{ $expiringSoon }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-yellow-500">
            <div class="text-sm text-gray-600">En Periodo de Gracia</div>
            <div class="text-2xl font-bold text-gray-900">{{ $inGracePeriod }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-red-500">
            <div class="text-sm text-gray-600">En Decadencia</div>
            <div class="text-2xl font-bold text-gray-900">{{ $decaying }}</div>
        </div>
        <div class="bg-white rounded-lg shadow p-4 border-l-4 border-emerald-500">
            <div class="text-sm text-gray-600">Total Contratos</div>
            <div class="text-2xl font-bold text-gray-900">{{ $contracts->total() }}</div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-white rounded-lg shadow mb-6 p-4">
        <form method="GET" action="{{ route('commercial.contracts.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Buscar</label>
                <input type="text" name="search" value="{{ request('search') }}" 
                       placeholder="Folio, cliente o cripta"
                       class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Estado</label>
                <select name="status" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Todos</option>
                    <option value="draft" {{ request('status') == 'draft' ? 'selected' : '' }}>Borrador</option>
                    <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Activo</option>
                    <option value="expired" {{ request('status') == 'expired' ? 'selected' : '' }}>Vencido</option>
                    <option value="grace_period" {{ request('status') == 'grace_period' ? 'selected' : '' }}>Periodo de Gracia</option>
                    <option value="decaying" {{ request('status') == 'decaying' ? 'selected' : '' }}>Decadencia</option>
                    <option value="terminated" {{ request('status') == 'terminated' ? 'selected' : '' }}>Terminado</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">Tipo de Contrato</label>
                <select name="contract_type_id" class="w-full border-gray-300 rounded-md shadow-sm focus:border-emerald-500 focus:ring-emerald-500">
                    <option value="">Todos</option>
                    @foreach($contractTypes as $type)
                        <option value="{{ $type->id }}" {{ request('contract_type_id') == $type->id ? 'selected' : '' }}>
                            {{ $type->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="flex items-end">
                <button type="submit" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-md font-medium transition-colors">
                    <i class="fa-solid fa-filter mr-2"></i>
                    Filtrar
                </button>
            </div>
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-lg shadow overflow-hidden">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
                <tr>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Folio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cliente</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Cripta</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tipo</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Precio</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Estado</th>
                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vencimiento</th>
                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">Acciones</th>
                </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
                @forelse($contracts as $contract)
                    <tr class="hover:bg-gray-50">
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm font-medium text-gray-900">{{ $contract->contract_number }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm text-gray-900">{{ $contract->customer->name }}</div>
                            <div class="text-xs text-gray-500">{{ $contract->customer->rfc_encrypted ?? 'N/A' }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $contract->crypt->full_code }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">{{ $contract->contractType->name }}</span>
                            @if($contract->is_temporary)
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-100 text-blue-800">
                                    Temporal
                                </span>
                            @else
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-emerald-100 text-emerald-800">
                                    Perpetuo
                                </span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="text-sm text-gray-900">${{ number_format($contract->price, 2) }}</span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
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
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $statusColors[$contract->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $statusLabels[$contract->status] ?? $contract->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($contract->end_date)
                                <div class="text-sm text-gray-900">{{ $contract->end_date->format('d/m/Y') }}</div>
                                @if($contract->days_until_expiry && $contract->days_until_expiry <= 90 && $contract->is_temporary)
                                    <div class="text-xs text-red-600 font-medium">
                                        {{ $contract->days_until_expiry }} días
                                    </div>
                                @endif
                            @else
                                <span class="text-sm text-gray-500">Perpetuo</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                            <a href="{{ route('commercial.contracts.show', $contract) }}" 
                               class="text-emerald-600 hover:text-emerald-900 mr-3">
                                <i class="fa-solid fa-eye"></i>
                            </a>
                            @if($contract->status === 'draft')
                                <a href="{{ route('commercial.contracts.edit', $contract) }}" 
                                   class="text-blue-600 hover:text-blue-900 mr-3">
                                    <i class="fa-solid fa-pen"></i>
                                </a>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-6 py-12 text-center text-gray-500">
                            <i class="fa-solid fa-file-contract text-4xl text-gray-300 mb-4"></i>
                            <p>No hay contratos registrados</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($contracts->hasPages())
        <div class="mt-4">
            {{ $contracts->links() }}
        </div>
    @endif
</div>
@endsection
