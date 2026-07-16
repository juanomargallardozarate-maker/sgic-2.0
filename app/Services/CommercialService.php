<?php

namespace App\Services;

use App\Models\Crypt;
use App\Models\Customer;
use App\Models\Contract;
use App\Models\Beneficiary;
use App\Models\Heir;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Carbon\Carbon;

/**
 * CommercialService - Lógica de negocio comercial para SGIC 2.0
 * 
 * Implementa las reglas de negocio:
 * - RN-01: Unicidad y capacidad de criptas
 * - RN-02: Perpetuidad vs temporalidad en contratos
 * - RN-05: Sucesiones y traspasos
 */
class CommercialService
{
    /**
     * Validar disponibilidad de una cripta para venta/reserva (RN-01)
     * 
     * @param Crypt $crypt
     * @return array ['available' => bool, 'message' => string, 'reason' => string|null]
     */
    public function validateCryptAvailability(Crypt $crypt): array
    {
        // Verificar estado actual
        if (!$crypt->status || $crypt->status->slug !== 'available') {
            return [
                'available' => false,
                'message' => "La cripta {$crypt->identifier} no está disponible",
                'reason' => 'status_not_available',
                'current_status' => $crypt->status?->name
            ];
        }

        // Verificar que no tenga contratos activos
        $activeContract = Contract::where('crypt_id', $crypt->id)
            ->whereIn('status', ['active', 'reserved', 'pending_signature'])
            ->first();

        if ($activeContract) {
            return [
                'available' => false,
                'message' => "La cripta {$crypt->identifier} ya tiene un contrato activo",
                'reason' => 'has_active_contract',
                'contract_number' => $activeContract->contract_number
            ];
        }

        // Verificar capacidad (para nichos múltiples)
        if ($crypt->capacity > 1) {
            $currentOccupancy = $this->getCurrentOccupancy($crypt);
            
            if ($currentOccupancy >= $crypt->capacity) {
                return [
                    'available' => false,
                    'message' => "La cripta {$crypt->identifier} ha alcanzado su capacidad máxima ({$crypt->capacity})",
                    'reason' => 'capacity_exceeded',
                    'current_occupancy' => $currentOccupancy,
                    'capacity' => $crypt->capacity
                ];
            }
        }

        return [
            'available' => true,
            'message' => "La cripta {$crypt->identifier} está disponible para venta/reserva",
            'reason' => null,
            'capacity' => $crypt->capacity,
            'current_occupancy' => $crypt->capacity > 1 ? $this->getCurrentOccupancy($crypt) : 0
        ];
    }

    /**
     * Obtener ocupación actual de una cripta (número de restos)
     * 
     * @param Crypt $crypt
     * @return int
     */
    public function getCurrentOccupancy(Crypt $crypt): int
    {
        // Contar beneficiarios con contratos activos en esta cripta
        return Beneficiary::whereHas('contract', function ($query) use ($crypt) {
            $query->where('crypt_id', $crypt->id)
                  ->whereIn('status', ['active', 'deceased']);
        })->count();
    }

    /**
     * Crear contrato de compra-venta o asignación de cripta (RN-02)
     * 
     * @param array $data Datos del contrato
     * @param Tenant $tenant Tenant actual
     * @return array ['success' => bool, 'contract' => Contract|null, 'message' => string]
     */
    public function createContract(array $data, Tenant $tenant): array
    {
        try {
            DB::beginTransaction();

            // 1. Validar cripta
            $crypt = Crypt::findOrFail($data['crypt_id']);
            $validation = $this->validateCryptAvailability($crypt);

            if (!$validation['available']) {
                DB::rollBack();
                return [
                    'success' => false,
                    'contract' => null,
                    'message' => $validation['message']
                ];
            }

            // 2. Validar tipo de contrato (perpetuo vs temporal) - RN-02
            $contractType = $data['contract_type'];
            $isPerpetual = $contractType->is_perpetual;

            // Para contratos temporales, validar fecha de vencimiento
            if (!$isPerpetual) {
                if (empty($data['end_date'])) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'contract' => null,
                        'message' => 'Los contratos temporales requieren una fecha de vencimiento'
                    ];
                }

                $endDate = Carbon::parse($data['end_date']);
                if ($endDate->isPast()) {
                    DB::rollBack();
                    return [
                        'success' => false,
                        'contract' => null,
                        'message' => 'La fecha de vencimiento debe ser futura'
                    ];
                }
            }

            // 3. Validar cliente
            $customer = Customer::findOrFail($data['customer_id']);

            // 4. Calcular monto total
            $basePrice = $data['base_price'] ?? $crypt->price ?? 0;
            $taxes = $data['taxes'] ?? 0;
            $discounts = $data['discounts'] ?? 0;
            $totalAmount = $basePrice + $taxes - $discounts;

            // 5. Generar número de contrato único
            $contractNumber = $this->generateContractNumber($tenant, $contractType);

            // 6. Crear contrato
            $contract = new Contract();
            $contract->tenant_id = $tenant->id;
            $contract->crypt_id = $crypt->id;
            $contract->customer_id = $customer->id;
            $contract->contract_type_id = $contractType->id;
            $contract->contract_number = $contractNumber;
            $contract->start_date = $data['start_date'] ?? Carbon::now();
            $contract->end_date = $isPerpetual ? null : $data['end_date'];
            $contract->base_price = $basePrice;
            $contract->taxes = $taxes;
            $contract->discounts = $discounts;
            $contract->total_amount = $totalAmount;
            $contract->payment_method = $data['payment_method'] ?? 'cash';
            $contract->payment_frequency = $data['payment_frequency'] ?? 'one_time';
            $contract->status = 'pending_signature';
            $contract->digital_signature_hash = null; // Se genera al firmar
            $contract->signed_at = null;
            $contract->notes = $data['notes'] ?? null;
            $contract->metadata = $data['metadata'] ?? [];
            
            $contract->save();

            // 7. Registrar beneficiario principal (el cliente)
            $beneficiary = new Beneficiary();
            $beneficiary->tenant_id = $tenant->id;
            $beneficiary->contract_id = $contract->id;
            $beneficiary->customer_id = $customer->id;
            $beneficiary->relationship = 'owner';
            $beneficiary->priority = 1;
            $beneficiary->inheritance_percentage = 100;
            $beneficiary->is_primary = true;
            $beneficiary->save();

            // 8. Registrar herederos si existen
            if (!empty($data['heirs'])) {
                foreach ($data['heirs'] as $index => $heirData) {
                    $heir = new Heir();
                    $heir->tenant_id = $tenant->id;
                    $heir->contract_id = $contract->id;
                    $heir->customer_id = $heirData['customer_id'];
                    $heir->relationship = $heirData['relationship'];
                    $heir->inheritance_percentage = $heirData['inheritance_percentage'] ?? 0;
                    $heir->priority = $index + 1;
                    $heir->document_verification_status = 'pending';
                    $heir->save();
                }
            }

            // 9. Actualizar estado de la cripta a "reserved" o "occupied"
            $newStatus = $isPerpetual ? 'occupied' : 'reserved';
            $crypt->status_id = \App\Models\CryptStatus::where('slug', $newStatus)->first()->id;
            $crypt->current_occupancy = $crypt->capacity > 1 ? 1 : $crypt->current_occupancy;
            $crypt->save();

            // 10. Registrar en bitácora de auditoría (RN-07)
            $this->logContractCreation($contract, $tenant);

            DB::commit();

            return [
                'success' => true,
                'contract' => $contract,
                'message' => "Contrato {$contractNumber} creado exitosamente"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creando contrato: ' . $e->getMessage(), [
                'data' => $data,
                'trace' => $e->getTraceAsString()
            ]);

            return [
                'success' => false,
                'contract' => null,
                'message' => 'Error al crear el contrato: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Firmar digitalmente un contrato
     * 
     * @param Contract $contract
     * @param string $signatureData Datos biométricos o hash de firma
     * @param array $signatories Lista de firmantes
     * @return array ['success' => bool, 'message' => string]
     */
    public function signContract(Contract $contract, string $signatureData, array $signatories): array
    {
        try {
            DB::beginTransaction();

            // Generar hash único de firma digital
            $hashData = json_encode([
                'contract_id' => $contract->id,
                'contract_number' => $contract->contract_number,
                'signatories' => $signatories,
                'timestamp' => Carbon::now()->toIso8601String(),
                'signature_data' => $signatureData
            ]);

            $digitalSignatureHash = hash('sha256', $hashData);

            // Actualizar contrato
            $contract->digital_signature_hash = $digitalSignatureHash;
            $contract->signed_at = Carbon::now();
            $contract->status = 'active';
            $contract->metadata = array_merge($contract->metadata ?? [], [
                'signature_data' => $signatureData,
                'signatories' => $signatories,
                'signed_via' => 'web'
            ]);
            
            $contract->save();

            // Si es contrato perpetuo, actualizar cripta a "occupied"
            if ($contract->contractType->is_perpetual) {
                $crypt = $contract->crypt;
                $crypt->status_id = \App\Models\CryptStatus::where('slug', 'occupied')->first()->id;
                $crypt->save();
            }

            // Registrar en auditoría
            $this->logContractSigning($contract, $signatories);

            DB::commit();

            return [
                'success' => true,
                'message' => "Contrato {$contract->contract_number} firmado digitalmente"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error firmando contrato: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error al firmar el contrato: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Crear reserva de cripta (pre-contrato)
     * 
     * @param Crypt $crypt
     * @param Customer $customer
     * @param Tenant $tenant
     * @param int $reservationDays Días de vigencia de la reserva (default: 7)
     * @return array ['success' => bool, 'reservation' => Contract|null, 'message' => string]
     */
    public function reserveCrypt(Crypt $crypt, Customer $customer, Tenant $tenant, int $reservationDays = 7): array
    {
        try {
            DB::beginTransaction();

            // Validar disponibilidad
            $validation = $this->validateCryptAvailability($crypt);
            if (!$validation['available']) {
                DB::rollBack();
                return [
                    'success' => false,
                    'reservation' => null,
                    'message' => $validation['message']
                ];
            }

            // Crear contrato en estado "reserved"
            $contract = new Contract();
            $contract->tenant_id = $tenant->id;
            $contract->crypt_id = $crypt->id;
            $contract->customer_id = $customer->id;
            $contract->contract_type_id = \App\Models\ContractType::where('slug', 'perpetual')->first()->id;
            $contract->contract_number = $this->generateReservationNumber($tenant);
            $contract->start_date = Carbon::now();
            $contract->end_date = Carbon::now()->addDays($reservationDays);
            $contract->status = 'reserved';
            $contract->total_amount = 0; // Se define al convertir a contrato
            $contract->metadata = [
                'reservation_expires_at' => Carbon::now()->addDays($reservationDays)->toIso8601String(),
                'reservation_type' => 'pre_contract'
            ];

            $contract->save();

            // Actualizar estado de cripta
            $crypt->status_id = \App\Models\CryptStatus::where('slug', 'reserved')->first()->id;
            $crypt->save();

            DB::commit();

            return [
                'success' => true,
                'reservation' => $contract,
                'message' => "Cripta {$crypt->identifier} reservada por {$reservationDays} días"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error reservando cripta: ' . $e->getMessage());

            return [
                'success' => false,
                'reservation' => null,
                'message' => 'Error al reservar la cripta: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Cancelar reserva (expirada o por solicitud del cliente)
     * 
     * @param Contract $reservation
     * @return array ['success' => bool, 'message' => string]
     */
    public function cancelReservation(Contract $reservation): array
    {
        try {
            DB::beginTransaction();

            if ($reservation->status !== 'reserved') {
                DB::rollBack();
                return [
                    'success' => false,
                    'message' => 'Solo se pueden cancelar reservas en estado "reserved"'
                ];
            }

            // Liberar cripta
            $crypt = $reservation->crypt;
            $crypt->status_id = \App\Models\CryptStatus::where('slug', 'available')->first()->id;
            $crypt->save();

            // Cancelar contrato
            $reservation->status = 'cancelled';
            $reservation->cancelled_at = Carbon::now();
            $reservation->cancellation_reason = $reservation->metadata['cancellation_reason'] ?? 'expired';
            $reservation->save();

            DB::commit();

            return [
                'success' => true,
                'message' => "Reserva {$reservation->contract_number} cancelada exitosamente"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error cancelando reserva: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Error al cancelar la reserva: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Procesar sucesión de contrato (RN-05)
     * 
     * @param Contract $originalContract Contrato del titular fallecido
     * @param Heir $heir Heredero que sucede
     * @param array $legalDocuments Documentos legales de sucesión
     * @return array ['success' => bool, 'newContract' => Contract|null, 'message' => string]
     */
    public function processSuccession(Contract $originalContract, Heir $heir, array $legalDocuments): array
    {
        try {
            DB::beginTransaction();

            // Validar que el heredero esté verificado
            if ($heir->document_verification_status !== 'approved') {
                DB::rollBack();
                return [
                    'success' => false,
                    'newContract' => null,
                    'message' => 'El heredero debe tener documentos verificados antes de procesar la sucesión'
                ];
            }

            // Validar que el contrato original sea perpetuo
            if (!$originalContract->contractType->is_perpetual) {
                DB::rollBack();
                return [
                    'success' => false,
                    'newContract' => null,
                    'message' => 'Solo los contratos perpetuos pueden heredarse'
                ];
            }

            // Crear nuevo contrato a nombre del heredero
            $newContract = $originalContract->replicate();
            $newContract->customer_id = $heir->customer_id;
            $newContract->contract_number = $this->generateSuccessionContractNumber($originalContract);
            $newContract->status = 'active';
            $newContract->parent_contract_id = $originalContract->id;
            $newContract->succession_processed_at = Carbon::now();
            $newContract->metadata = array_merge($originalContract->metadata ?? [], [
                'succession_from' => $originalContract->contract_number,
                'heir_id' => $heir->id,
                'legal_documents' => $legalDocuments,
                'succession_date' => Carbon::now()->toIso8601String()
            ]);

            $newContract->save();

            // Actualizar contrato original
            $originalContract->status = 'transferred';
            $originalContract->transferred_at = Carbon::now();
            $originalContract->metadata = array_merge($originalContract->metadata ?? [], [
                'transferred_to_contract' => $newContract->contract_number,
                'succession_heir_id' => $heir->id
            ]);
            $originalContract->save();

            // Actualizar beneficiarios
            $newBeneficiary = new Beneficiary();
            $newBeneficiary->tenant_id = $originalContract->tenant_id;
            $newBeneficiary->contract_id = $newContract->id;
            $newBeneficiary->customer_id = $heir->customer_id;
            $newBeneficiary->relationship = 'heir';
            $newBeneficiary->priority = 1;
            $newBeneficiary->inheritance_percentage = 100;
            $newBeneficiary->is_primary = true;
            $newBeneficiary->save();

            // Registrar en auditoría
            $this->logSuccession($originalContract, $newContract, $heir);

            DB::commit();

            return [
                'success' => true,
                'newContract' => $newContract,
                'message' => "Sucesión procesada exitosamente. Nuevo contrato: {$newContract->contract_number}"
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error procesando sucesión: ' . $e->getMessage());

            return [
                'success' => false,
                'newContract' => null,
                'message' => 'Error al procesar la sucesión: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Generar número de contrato único
     * 
     * @param Tenant $tenant
     * @param \App\Models\ContractType $contractType
     * @return string
     */
    private function generateContractNumber(Tenant $tenant, \App\Models\ContractType $contractType): string
    {
        $year = Carbon::now()->format('Y');
        $prefix = $contractType->is_perpetual ? 'PER' : 'TEM';
        
        $lastContract = Contract::whereYear('created_at', $year)
            ->where('tenant_id', $tenant->id)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastContract ? intval(substr($lastContract->contract_number, -6)) + 1 : 1;

        return "{$tenant->rfc}-{$prefix}-{$year}-" . str_pad($nextNumber, 6, '0', STR_PAD_LEFT);
    }

    /**
     * Generar número de reserva
     * 
     * @param Tenant $tenant
     * @return string
     */
    private function generateReservationNumber(Tenant $tenant): string
    {
        $year = Carbon::now()->format('y');
        $prefix = 'RES';
        
        $lastReservation = Contract::where('tenant_id', $tenant->id)
            ->where('contract_number', 'like', "%{$prefix}%")
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastReservation ? intval(substr($lastReservation->contract_number, -4)) + 1 : 1;

        return "{$tenant->rfc}-{$prefix}-{$year}-" . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Generar número de contrato por sucesión
     * 
     * @param Contract $originalContract
     * @return string
     */
    private function generateSuccessionContractNumber(Contract $originalContract): string
    {
        $baseNumber = $originalContract->contract_number;
        $suffix = '-SUC';
        
        $lastSuccession = Contract::where('parent_contract_id', $originalContract->id)
            ->orderBy('id', 'desc')
            ->first();

        $nextNumber = $lastSuccession ? intval(substr($lastSuccession->contract_number, -1)) + 1 : 1;

        return $baseNumber . $suffix . $nextNumber;
    }

    /**
     * Registrar creación de contrato en bitácora (RN-07)
     * 
     * @param Contract $contract
     * @param Tenant $tenant
     */
    private function logContractCreation(Contract $contract, Tenant $tenant): void
    {
        \App\Models\AuditLog::create([
            'tenant_id' => $tenant->id,
            'user_id' => auth()->id(),
            'action' => 'contract_created',
            'entity_type' => 'Contract',
            'entity_id' => $contract->id,
            'description' => "Se creó el contrato {$contract->contract_number} para la cripta {$contract->crypt->identifier}",
            'old_values' => null,
            'new_values' => [
                'contract_number' => $contract->contract_number,
                'crypt_id' => $contract->crypt_id,
                'customer_id' => $contract->customer_id,
                'total_amount' => $contract->total_amount,
                'type' => $contract->contractType->name
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Registrar firma de contrato en bitácora (RN-07)
     * 
     * @param Contract $contract
     * @param array $signatories
     */
    private function logContractSigning(Contract $contract, array $signatories): void
    {
        \App\Models\AuditLog::create([
            'tenant_id' => $contract->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'contract_signed',
            'entity_type' => 'Contract',
            'entity_id' => $contract->id,
            'description' => "Se firmó digitalmente el contrato {$contract->contract_number}",
            'old_values' => ['status' => 'pending_signature'],
            'new_values' => [
                'status' => 'active',
                'signed_at' => $contract->signed_at,
                'signatories' => $signatories
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }

    /**
     * Registrar sucesión en bitácora (RN-07)
     * 
     * @param Contract $originalContract
     * @param Contract $newContract
     * @param Heir $heir
     */
    private function logSuccession(Contract $originalContract, Contract $newContract, Heir $heir): void
    {
        \App\Models\AuditLog::create([
            'tenant_id' => $originalContract->tenant_id,
            'user_id' => auth()->id(),
            'action' => 'contract_succession',
            'entity_type' => 'Contract',
            'entity_id' => $newContract->id,
            'description' => "Se procesó sucesión del contrato {$originalContract->contract_number} a {$newContract->contract_number}",
            'old_values' => [
                'original_contract' => $originalContract->contract_number,
                'original_holder_id' => $originalContract->customer_id
            ],
            'new_values' => [
                'new_contract' => $newContract->contract_number,
                'new_holder_id' => $newContract->customer_id,
                'heir_id' => $heir->id
            ],
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent()
        ]);
    }
}
