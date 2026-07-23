<?php

namespace App\Services\Commercial;

use App\Models\Contract;
use App\Models\Crypt;
use App\Models\Customer;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\LengthAwarePaginator;

class ContractService
{
    /**
     * Obtener contratos con filtros y paginación.
     */
    public function getContracts(array $filters = []): LengthAwarePaginator
    {
        $query = Contract::with(['customer', 'crypt', 'contractType'])
            ->orderBy('created_at', 'desc');
        
        // Aplicar filtros
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }
        
        if (!empty($filters['contract_type_id'])) {
            $query->where('contract_type_id', $filters['contract_type_id']);
        }
        
        if (!empty($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('crypt', fn($q) => $q->where('code', 'like', "%{$search}%"));
            });
        }
        
        return $query->paginate(15);
    }
    
    /**
     * Obtener métricas de contratos.
     */
    public function getContractMetrics(): array
    {
        return [
            'expiring_soon' => Contract::active()
                ->expiringSoon(90)
                ->count(),
            'in_grace_period' => Contract::inGracePeriod()->count(),
            'decaying' => Contract::where('status', 'decaying')->count(),
        ];
    }
    
    /**
     * Verificar si una cripta está disponible para venta.
     */
    public function isCryptAvailable(Crypt $crypt): bool
    {
        return $crypt->isAvailableForSale;
    }
    
    /**
     * Obtener criptas disponibles.
     */
    public function getAvailableCrypts(): Collection
    {
        return Crypt::whereHas('cryptStatus', fn($q) => $q->where('code', 'available'))
            ->where('is_blocked', false)
            ->with(['level.block.section'])
            ->get();
    }
    
    /**
     * Obtener clientes activos.
     */
    public function getActiveCustomers(): Collection
    {
        return Customer::where('is_active', true)->get();
    }
}
