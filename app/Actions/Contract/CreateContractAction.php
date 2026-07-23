<?php

namespace App\Actions\Contract;

use App\Models\Contract;
use App\Models\Crypt;
use App\Models\ContractType;
use App\Models\Customer;
use App\Models\Beneficiary;
use App\Models\Heir;
use App\Models\GlobalSetting;
use App\Models\InterestRate;
use App\Models\AuditLog;
use App\ValueObjects\Money;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Exception;

class CreateContractAction
{
    /**
     * Ejecutar la acción de crear un contrato.
     * 
     * @param array $data Datos validados del request
     * @param Customer $customer Cliente asociado
     * @param Crypt $crypt Cripta asociada
     * @param ContractType $contractType Tipo de contrato
     * @return Contract Contrato creado
     * @throws Exception
     */
    public function execute(
        array $data,
        Customer $customer,
        Crypt $crypt,
        ContractType $contractType
    ): Contract {
        // Obtener ID del cementerio actual (multi-tenant)
        $cemeteryId = auth()->user()->cemetery_id ?? auth()->user()->tenant_id;
        
        // Obtener configuración global
        $maintenanceFeeConfig = GlobalSetting::getValue('maintenance_fee', $cemeteryId, 1500.00);
        
        // Validar que la cripta esté disponible
        if (!$crypt->isAvailableForSale) {
            throw new Exception('La cripta seleccionada no está disponible para venta.');
        }
        
        // Validar fechas para contrato temporal
        if ($contractType->is_temporary && empty($data['end_date'])) {
            throw new Exception('Los contratos temporales requieren fecha de vencimiento.');
        }
        
        // Validar teléfono verificado
        if (!$customer->isPhoneVerified()) {
            throw new Exception('El teléfono del cliente debe estar verificado antes de crear un contrato.');
        }
        
        // Calcular valores financieros
        $financialData = $this->calculateFinancialValues($data, $crypt, $cemeteryId);
        
        DB::beginTransaction();
        try {
            // Crear contrato
            $contract = Contract::create([
                'customer_id' => $customer->id,
                'crypt_id' => $crypt->id,
                'contract_type_id' => $contractType->id,
                'contract_number' => $this->generateContractNumber(),
                'start_date' => $data['start_date'],
                'end_date' => $data['end_date'] ?? null,
                'financing_end_date' => $financialData['financing_end_date'],
                'price' => $financialData['price']->getAmount(),
                'total_price' => $financialData['total_price']->getAmount(),
                'annual_maintenance_fee' => $data['annual_maintenance_fee'],
                'payment_type' => $data['payment_type'],
                'installments_count' => $data['installments_count'] ?? null,
                'down_payment' => $data['down_payment'] ?? null,
                'financed_amount' => $financialData['financed_amount']->getAmount(),
                'interest_rate_applied' => $financialData['interest_rate'],
                'monthly_payment' => $financialData['monthly_payment']->getAmount(),
                'maintenance_fee_snapshot' => $maintenanceFeeConfig,
                'interest_rate_snapshot' => $financialData['interest_rate'] / 100,
                'status' => 'draft',
                'notes' => $data['notes'] ?? null,
                'created_by_user_id' => Auth::id(),
            ]);
            
            // Guardar beneficiarios si existen
            if (!empty($data['beneficiaries'])) {
                foreach ($data['beneficiaries'] as $beneficiary) {
                    Beneficiary::create([
                        'cemetery_id' => $cemeteryId,
                        'contract_id' => $contract->id,
                        'beneficiary_customer_id' => $beneficiary['customer_id'],
                        'relationship' => $beneficiary['relationship'],
                        'is_primary' => $beneficiary['is_primary'] ?? false,
                    ]);
                }
            }
            
            // Guardar herederos si existen
            if (!empty($data['heirs'])) {
                foreach ($data['heirs'] as $heir) {
                    Heir::create([
                        'cemetery_id' => $cemeteryId,
                        'contract_id' => $contract->id,
                        'customer_id' => $heir['customer_id'],
                        'is_designated' => true,
                    ]);
                }
            }
            
            // Actualizar estado de la cripta a "reservada" o "vendida"
            $crypt->update(['crypt_status_id' => 2]); // Asumiendo que 2 es "vendida"
            
            // Registrar en auditoría
            AuditLog::create([
                'user_id' => Auth::id(),
                'action' => 'contract_created',
                'description' => "Contrato {$contract->contract_number} creado para el cliente {$customer->name}",
                'model_type' => Contract::class,
                'model_id' => $contract->id,
                'old_values' => null,
                'new_values' => json_encode($contract->toArray()),
            ]);
            
            DB::commit();
            
            return $contract;
            
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }
    
    /**
     * Calcular todos los valores financieros del contrato.
     */
    private function calculateFinancialValues(array $data, Crypt $crypt, int $cemeteryId): array
    {
        $price = new Money($crypt->price);
        $totalPrice = clone $price; // total_price es inmutable
        
        $financedAmount = new Money(0);
        $monthlyPayment = new Money(0);
        $interestRate = 0.0;
        $financingEndDate = null;
        
        $startDate = Carbon::parse($data['start_date']);
        
        if ($data['payment_type'] === 'cash') {
            // Contado: no hay financiamiento
            $interestRate = 0;
        } elseif ($data['payment_type'] === 'mixed') {
            // Mixto: precio - enganche
            $downPayment = new Money($data['down_payment'] ?? 0);
            $financedAmount = $price->subtract($downPayment);
            
            if (!empty($data['installments_count']) && $financedAmount->getAmount() > 0) {
                $result = $this->calculateInstallments(
                    $financedAmount->getAmount(),
                    $data['installments_count'],
                    $cemeteryId,
                    $startDate
                );
                
                $interestRate = $result['interest_rate'];
                $monthlyPayment = new Money($result['monthly_payment']);
                $financingEndDate = $result['end_date'];
            }
        } elseif ($data['payment_type'] === 'installments') {
            // Crédito puro: todo se financia
            $financedAmount = clone $price;
            
            if (!empty($data['installments_count'])) {
                $result = $this->calculateInstallments(
                    $financedAmount->getAmount(),
                    $data['installments_count'],
                    $cemeteryId,
                    $startDate
                );
                
                $interestRate = $result['interest_rate'];
                $monthlyPayment = new Money($result['monthly_payment']);
                $financingEndDate = $result['end_date'];
            }
        }
        
        return [
            'price' => $price,
            'total_price' => $totalPrice,
            'financed_amount' => $financedAmount,
            'interest_rate' => $interestRate,
            'monthly_payment' => $monthlyPayment,
            'financing_end_date' => $financingEndDate,
        ];
    }
    
    /**
     * Calcular cuotas usando el Método Francés.
     */
    private function calculateInstallments(
        float $principal,
        int $months,
        int $cemeteryId,
        Carbon $startDate
    ): array {
        // Obtener tasa de interés según rango de meses
        $interestRateObj = InterestRate::getRateForMonths($months, $cemeteryId);
        $interestRate = $interestRateObj ? (float)$interestRateObj->interest_rate : 0;
        
        $monthlyPayment = 0.0;
        
        if ($interestRate > 0) {
            // Método Francés: M = P * [i(1+i)^n] / [(1+i)^n - 1]
            $i = $interestRate / 1200; // Tasa mensual
            $n = $months;
            $pow = pow(1 + $i, $n);
            $monthlyPayment = $principal * ($i * $pow) / ($pow - 1);
        } else {
            // Sin interés: división simple
            $monthlyPayment = $principal / $months;
        }
        
        // Calcular fecha de fin del financiamiento
        $endDate = (clone $startDate)->addMonths($months);
        
        return [
            'interest_rate' => $interestRate,
            'monthly_payment' => round($monthlyPayment, 2),
            'end_date' => $endDate,
        ];
    }
    
    /**
     * Generar número de contrato único.
     */
    private function generateContractNumber(): string
    {
        $year = date('Y');
        $prefix = 'CTR';
        $random = strtoupper(substr(uniqid(), -6));
        
        return "{$prefix}-{$year}-{$random}";
    }
}
