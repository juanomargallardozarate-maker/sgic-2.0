<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Crypt;
use App\Models\ContractType;
use App\Models\Beneficiary;
use App\Models\Heir;
use App\Models\GlobalSetting;
use App\Models\InterestRate;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ContractController extends Controller
{
    /**
     * Display a listing of contracts.
     * US-3.2: Emisión de contratos perpetuos y temporales (RN-02)
     */
    public function index(Request $request)
    {
        $query = Contract::with(['customer', 'crypt', 'contractType'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('contract_type_id')) {
            $query->where('contract_type_id', $request->contract_type_id);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('contract_number', 'like', "%{$search}%")
                  ->orWhereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('crypt', fn($q) => $q->where('code', 'like', "%{$search}%"));
            });
        }

        // Contratos por vencer (próximos 90 días)
        $expiringSoon = Contract::active()
            ->expiringSoon(90)
            ->count();

        // Contratos en periodo de gracia
        $inGracePeriod = Contract::inGracePeriod()->count();

        // Contratos en proceso de decadencia
        $decaying = Contract::where('status', 'decaying')->count();

        $contracts = $query->paginate(15)->withQueryString();
        $contractTypes = ContractType::all();
        $customers = Customer::where('is_active', true)->get();
        $availableCrypts = Crypt::whereHas('cryptStatus', fn($q) => $q->where('code', 'available'))
            ->where('is_blocked', false)
            ->get();

        return view('commercial.contracts.index', compact(
            'contracts',
            'contractTypes',
            'customers',
            'availableCrypts',
            'expiringSoon',
            'inGracePeriod',
            'decaying'
        ));
    }

    /**
     * Show the form for creating a new contract.
     */
    public function create()
    {
        $contractTypes = ContractType::all();
        $customers = Customer::where('is_active', true)->get();
        $availableCrypts = Crypt::whereHas('cryptStatus', fn($q) => $q->where('code', 'available'))
            ->where('is_blocked', false)
            ->with(['level.block.section'])
            ->get();
        
        // Obtener configuración global del cementerio actual
        $cemeteryId = auth()->user()->cemetery_id ?? auth()->user()->tenant_id;
        $maintenanceFee = GlobalSetting::getValue('maintenance_fee', $cemeteryId, 1500.00);
        $interestRates = InterestRate::getActiveRates($cemeteryId);

        return view('commercial.contracts.create', compact(
            'contractTypes', 
            'customers', 
            'availableCrypts',
            'maintenanceFee',
            'interestRates'
        ));
    }

    /**
     * Enviar código de verificación WhatsApp al cliente
     */
    public function sendVerificationCode(Request $request, WhatsAppService $whatsappService)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
        ]);

        $customer = Customer::find($validated['customer_id']);

        if (!$customer || !$customer->phone) {
            return response()->json([
                'success' => false,
                'message' => 'El cliente no tiene un número de teléfono registrado.',
            ], 400);
        }

        // Verificar si ya está verificado
        if ($customer->isPhoneVerified()) {
            return response()->json([
                'success' => false,
                'message' => 'El teléfono de este cliente ya está verificado.',
            ], 400);
        }

        // Enviar código
        $result = $whatsappService->sendVerificationCode($customer);

        return response()->json($result);
    }

    /**
     * Verificar código ingresado por el cliente
     */
    public function verifyCode(Request $request, WhatsAppService $whatsappService)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'code' => 'required|string|size:6',
        ]);

        $customer = Customer::find($validated['customer_id']);

        if (!$customer) {
            return response()->json([
                'success' => false,
                'message' => 'Cliente no encontrado.',
            ], 404);
        }

        // Verificar código
        $result = $whatsappService->verifyCode($customer, $validated['code']);

        return response()->json($result);
    }

    /**
     * Store a newly created contract in storage.
     * RN-02: Validación de contrato temporal vs perpetuo
     * RN-01: Solo criptas disponibles
     * 
     * Calcula todos los valores financieros usando el Método Francés
     * y guarda snapshots de las tasas aplicadas.
     */
    public function store(Request $request)
    {
        // Obtener ID del cementerio actual (multi-tenant)
        $cemeteryId = auth()->user()->cemetery_id ?? auth()->user()->tenant_id;
        
        // Obtener configuración global para validaciones y cálculos
        $maintenanceFeeConfig = GlobalSetting::getValue('maintenance_fee', $cemeteryId, 1500.00);
        
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'crypt_id' => 'required|exists:crypts,id',
            'contract_type_id' => 'required|exists:contract_types,id',
            'price' => 'required|numeric|min:0',
            'annual_maintenance_fee' => 'required|numeric|min:0',
            'payment_type' => 'required|in:cash,installments,mixed',
            'installments_count' => 'nullable|integer|min:1',
            'down_payment' => 'nullable|numeric|min:0',
            'financed_amount' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'monthly_payment' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'beneficiaries' => 'nullable|array',
            'heirs' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
            'phone' => 'nullable|string|max:20',
            'verification_code' => 'nullable|string|size:6',
        ], [
            'crypt_id.required' => 'Debe seleccionar una cripta.',
            'customer_id.required' => 'Debe seleccionar un cliente.',
            'contract_type_id.required' => 'Debe seleccionar un tipo de contrato.',
            'end_date.after' => 'La fecha de vigencia debe ser posterior a la fecha de inicio.',
        ]);

        // Validar que el down_payment sea requerido si es pago mixto
        if ($validated['payment_type'] === 'mixed' && empty($validated['down_payment'])) {
            return back()->withErrors(['down_payment' => 'El pago mixto requiere un monto de enganche.'])
                ->withInput();
        }

        // Validar que la cripta esté disponible (RN-01)
        $crypt = Crypt::findOrFail($validated['crypt_id']);
        if (!$crypt->isAvailableForSale) {
            return back()->withErrors(['crypt_id' => 'La cripta seleccionada no está disponible para venta.'])
                ->withInput();
        }

        // Validar fechas para contrato temporal
        $contractType = ContractType::findOrFail($validated['contract_type_id']);
        if ($contractType->is_temporary && !$validated['end_date']) {
            return back()->withErrors(['end_date' => 'Los contratos temporales requieren fecha de vencimiento.'])
                ->withInput();
        }

        // ============================================
        // RECÁLCULO DE VALORES FINANCIEROS (BACKEND)
        // El backend es la fuente de la verdad
        // ============================================
        $price = $crypt->price; // El precio siempre viene de la cripta, no del frontend
        $totalPrice = $price;   // total_price es inmutable
        
        // Calcular saldo a financiar y pagos según tipo de pago
        $financedAmount = 0;
        $monthlyPayment = 0;
        $interestRate = 0;
        $financingEndDate = null;
        $startDate = \Carbon\Carbon::parse($validated['start_date']);
        
        if ($validated['payment_type'] === 'cash') {
            // Contado: no hay financiamiento, interés 0
            $financedAmount = 0;
            $interestRate = 0;
            $monthlyPayment = 0;
            $validated['installments_count'] = null;
        } elseif ($validated['payment_type'] === 'mixed') {
            // Mixto: precio - enganche
            $downPayment = $validated['down_payment'] ?? 0;
            $financedAmount = bcsub($price, $downPayment, 2);
            
            if ($validated['installments_count'] > 0 && $financedAmount > 0) {
                // Obtener tasa de interés según rango de meses (filtrado por cemetery_id)
                $interestRateObj = InterestRate::getRateForMonths($validated['installments_count'], $cemeteryId);
                $interestRate = $interestRateObj ? (float)$interestRateObj->interest_rate : 0;
                
                // Aplicar Método Francés: M = P * [i(1+i)^n] / [(1+i)^n - 1]
                if ($interestRate > 0) {
                    $i = bcdiv($interestRate, 1200, 6); // Tasa mensual (interés anual / 12 / 100)
                    $n = $validated['installments_count'];
                    $pow = bcpow(bcadd(1, $i, 6), $n, 6);
                    $numerator = bcmul($i, $pow, 6);
                    $denominator = bcsub($pow, 1, 6);
                    $monthlyPayment = bcmul($financedAmount, bcdiv($numerator, $denominator, 6), 2);
                } else {
                    // Sin interés: división simple
                    $monthlyPayment = bcdiv($financedAmount, $validated['installments_count'], 2);
                }
                
                // Calcular fecha de fin del financiamiento
                $financingEndDate = (clone $startDate)->addMonths($validated['installments_count']);
            }
        } elseif ($validated['payment_type'] === 'installments') {
            // Crédito puro: todo se financia
            $financedAmount = $price;
            $validated['down_payment'] = 0;
            
            if ($validated['installments_count'] > 0) {
                // Obtener tasa de interés según rango de meses
                $interestRateObj = InterestRate::getRateForMonths($validated['installments_count'], $cemeteryId);
                $interestRate = $interestRateObj ? (float)$interestRateObj->interest_rate : 0;
                
                // Aplicar Método Francés
                if ($interestRate > 0) {
                    $i = bcdiv($interestRate, 1200, 6);
                    $n = $validated['installments_count'];
                    $pow = bcpow(bcadd(1, $i, 6), $n, 6);
                    $numerator = bcmul($i, $pow, 6);
                    $denominator = bcsub($pow, 1, 6);
                    $monthlyPayment = bcmul($financedAmount, bcdiv($numerator, $denominator, 6), 2);
                } else {
                    $monthlyPayment = bcdiv($financedAmount, $validated['installments_count'], 2);
                }
                
                // Calcular fecha de fin del financiamiento
                $financingEndDate = (clone $startDate)->addMonths($validated['installments_count']);
            }
        }
        
        // ============================================
        // VERIFICACIÓN DE TELÉFONO VÍA WHATSAPP
        // ============================================
        $customer = Customer::find($validated['customer_id']);
        
        // Validar que el cliente tenga el teléfono verificado
        if (!$customer || !$customer->isPhoneVerified()) {
            return back()->withErrors([
                'phone_verified' => 'El teléfono del cliente debe estar verificado antes de crear un contrato.'
            ])->withInput();
        }
        
        // ============================================
        // PREPARAR DATOS PARA GUARDAR
        // ============================================
        $validated['price'] = $price;
        $validated['total_price'] = $totalPrice;
        $validated['financed_amount'] = $financedAmount;
        $validated['interest_rate_applied'] = $interestRate;
        $validated['monthly_payment'] = $monthlyPayment;
        $validated['financing_end_date'] = $financingEndDate;
        
        // Snapshots de valores al momento de crear el contrato
        $validated['maintenance_fee_snapshot'] = $maintenanceFeeConfig;
        $validated['interest_rate_snapshot'] = $interestRate / 100; // Guardar como decimal (ej: 0.05 para 5%)
        
        // Campos de verificación
        $validated['phone_verified'] = $phoneVerified;
        $validated['verified_at'] = $verifiedAt;

        DB::beginTransaction();
        try {
            // Crear contrato
            $contract = Contract::create([
                'customer_id' => $validated['customer_id'],
                'crypt_id' => $validated['crypt_id'],
                'contract_type_id' => $validated['contract_type_id'],
                'contract_number' => $this->generateContractNumber(),
                'start_date' => $validated['start_date'],
                'end_date' => $validated['end_date'],
                'financing_end_date' => $validated['financing_end_date'],
                'price' => $validated['price'],
                'total_price' => $validated['total_price'],
                'annual_maintenance_fee' => $validated['annual_maintenance_fee'],
                'payment_type' => $validated['payment_type'],
                'installments_count' => $validated['installments_count'] ?? null,
                'down_payment' => $validated['down_payment'] ?? null,
                'financed_amount' => $validated['financed_amount'],
                'interest_rate_applied' => $validated['interest_rate'],
                'monthly_payment' => $validated['monthly_payment'],
                'maintenance_fee_snapshot' => $validated['maintenance_fee_snapshot'],
                'interest_rate_snapshot' => $validated['interest_rate_snapshot'],
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by_user_id' => Auth::id(),
            ]);

            // Guardar beneficiarios si existen
            if (!empty($validated['beneficiaries'])) {
                foreach ($validated['beneficiaries'] as $beneficiary) {
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
            if (!empty($validated['heirs'])) {
                foreach ($validated['heirs'] as $heir) {
                    Heir::create([
                        'cemetery_id' => $cemeteryId,
                        'contract_id' => $contract->id,
                        'customer_id' => $heir['customer_id'],
                        'is_designated' => true,
                        'inheritance_percent' => $heir['inheritance_percent'] ?? 0,
                    ]);
                }
            }

            DB::commit();

            // ============================================
            // ENVIAR NOTIFICACIÓN WHATSAPP - NUEVO CONTRATO
            // ============================================
            $whatsappService = new WhatsAppService();
            $cryptInfo = $contract->crypt->code ?? 'N/A';
            if ($contract->crypt->level) {
                $cryptInfo = $contract->crypt->level->name ?? $cryptInfo;
            }
            
            $whatsappService->sendNewContractNotification($customer, [
                'contract_number' => $contract->contract_number,
                'crypt_info' => $cryptInfo,
                'price' => $contract->price,
            ]);

            return redirect()->route('inventory.commercial.contracts.show', $contract)
                ->with('success', 'Contrato creado exitosamente. Folio: ' . $contract->contract_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear el contrato: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified contract.
     */
    public function show(Contract $contract)
    {
        $contract->load(['customer', 'crypt.level.block.section', 'contractType', 'beneficiaries.customer', 'heirs.customer', 'payments']);

        // Calcular adeudos si existen
        $totalDebts = 0;
        $totalPaid = 0;
        if ($contract->payments) {
            $totalPaid = $contract->payments->sum('amount');
            $totalDebts = max(0, $contract->price - $totalPaid);
        }

        // Verificar si puede ser transferido (RN-05)
        $canTransfer = $contract->canBeTransferred();

        return view('commercial.contracts.show', compact('contract', 'totalDebts', 'totalPaid', 'canTransfer'));
    }

    /**
     * Show the form for editing the specified contract.
     */
    public function edit(Contract $contract)
    {
        // Solo contratos en estado 'draft' pueden editarse
        if ($contract->status !== 'draft') {
            return back()->withErrors(['error' => 'Solo los contratos en borrador pueden editarse.']);
        }

        $contractTypes = ContractType::all();
        $customers = Customer::where('is_active', true)->get();
        $availableCrypts = Crypt::whereHas('cryptStatus', fn($q) => $q->where('code', 'available'))
            ->where('is_blocked', false)
            ->with(['level.block.section'])
            ->get();

        return view('commercial.contracts.edit', compact('contract', 'contractTypes', 'customers', 'availableCrypts'));
    }

    /**
     * Update the specified contract in storage.
     */
    public function update(Request $request, Contract $contract)
    {
        // Solo contratos en estado 'draft' pueden actualizarse
        if ($contract->status !== 'draft') {
            return back()->withErrors(['error' => 'Solo los contratos en borrador pueden actualizarse.']);
        }

        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'crypt_id' => 'required|exists:crypts,id',
            'contract_type_id' => 'required|exists:contract_types,id',
            'price' => 'required|numeric|min:0',
            'annual_maintenance_fee' => 'required|numeric|min:0',
            'payment_type' => 'required|in:cash,installments,mixed',
            'installments_count' => 'nullable|integer|min:1',
            'down_payment' => 'nullable|numeric|min:0',
            'financed_amount' => 'nullable|numeric|min:0',
            'interest_rate' => 'nullable|numeric|min:0|max:100',
            'monthly_payment' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Validar que el down_payment sea requerido si es pago mixto
        if ($validated['payment_type'] === 'mixed' && empty($validated['down_payment'])) {
            return back()->withErrors(['down_payment' => 'El pago mixto requiere un monto de enganche.'])
                ->withInput();
        }

        // Validar que la cripta esté disponible (si cambió)
        if ($contract->crypt_id != $validated['crypt_id']) {
            $crypt = Crypt::findOrFail($validated['crypt_id']);
            if (!$crypt->isAvailableForSale) {
                return back()->withErrors(['crypt_id' => 'La cripta seleccionada no está disponible para venta.'])
                    ->withInput();
            }
        }

        // Recalcular valores financieros en el servidor (seguridad)
        $crypt = Crypt::findOrFail($validated['crypt_id']);
        $price = $crypt->price;
        $validated['price'] = $price;
        
        // Calcular saldo a financiar y pagos según tipo de pago
        $financedAmount = 0;
        $monthlyPayment = 0;
        $interestRate = 0;
        
        if ($validated['payment_type'] === 'cash') {
            $financedAmount = 0;
            $interestRate = 0;
            $monthlyPayment = 0;
            $validated['installments_count'] = null;
        } elseif ($validated['payment_type'] === 'mixed') {
            $downPayment = $validated['down_payment'] ?? 0;
            $financedAmount = $price - $downPayment;
            
            if ($validated['installments_count'] > 0) {
                $interestRateObj = InterestRate::getRateForMonths($validated['installments_count']);
                $interestRate = $interestRateObj ? $interestRateObj->percentage : 0;
                
                if ($interestRate > 0 && $financedAmount > 0) {
                    $i = $interestRate / 100 / 12;
                    $n = $validated['installments_count'];
                    $monthlyPayment = $financedAmount * ($i * pow(1 + $i, $n)) / (pow(1 + $i, $n) - 1);
                } else {
                    $monthlyPayment = $financedAmount / $validated['installments_count'];
                }
            }
        } elseif ($validated['payment_type'] === 'installments') {
            $financedAmount = $price;
            $validated['down_payment'] = 0;
            
            if ($validated['installments_count'] > 0) {
                $interestRateObj = InterestRate::getRateForMonths($validated['installments_count']);
                $interestRate = $interestRateObj ? $interestRateObj->percentage : 0;
                
                if ($interestRate > 0 && $financedAmount > 0) {
                    $i = $interestRate / 100 / 12;
                    $n = $validated['installments_count'];
                    $monthlyPayment = $financedAmount * ($i * pow(1 + $i, $n)) / (pow(1 + $i, $n) - 1);
                } else {
                    $monthlyPayment = $financedAmount / $validated['installments_count'];
                }
            }
        }
        
        $validated['financed_amount'] = $financedAmount;
        $validated['interest_rate'] = $interestRate;
        $validated['monthly_payment'] = $monthlyPayment;

        DB::beginTransaction();
        try {
            $contract->update($validated);

            DB::commit();

            return redirect()->route('inventory.commercial.contracts.show', $contract)
                ->with('success', 'Contrato actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el contrato: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Sign the contract (firma digital).
     * RN-02: Firma digital simple con hash + timestamp + IP
     */
    public function sign(Request $request, Contract $contract)
    {
        if ($contract->status !== 'draft') {
            return back()->withErrors(['error' => 'Solo los contratos en borrador pueden firmarse.']);
        }

        $validated = $request->validate([
            'signature_image' => 'nullable|string', // Base64 de la firma
        ]);

        // Generar hash único de la firma
        $signatureHash = hash('sha256', $contract->id . now()->toIso8601String() . request()->ip());

        DB::beginTransaction();
        try {
            $contract->update([
                'signed_at' => now(),
                'signature_hash' => $signatureHash,
                'signature_ip' => request()->ip(),
                'status' => 'active',
            ]);

            // Cambiar estado de la cripta a ocupada (RN-01)
            $crypt = $contract->crypt;
            $occupiedStatus = \App\Models\CryptStatus::where('code', 'occupied')->first();
            if ($occupiedStatus) {
                $crypt->crypt_status_id = $occupiedStatus->id;
                $crypt->current_occupancy = $crypt->capacity; // Asumir ocupación completa
                $crypt->save();
            }

            DB::commit();

            return back()->with('success', 'Contrato firmado exitosamente. Hash de firma: ' . substr($signatureHash, 0, 16) . '...');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al firmar el contrato: ' . $e->getMessage()]);
        }
    }

    /**
     * Renew a temporary contract (RN-02, RN-03).
     */
    public function renew(Request $request, Contract $contract)
    {
        if (!$contract->is_temporary) {
            return back()->withErrors(['error' => 'Solo los contratos temporales pueden renovarse.']);
        }

        if (!in_array($contract->status, ['active', 'expired', 'grace_period'])) {
            return back()->withErrors(['error' => 'El contrato no puede renovarse en su estado actual.']);
        }

        $validated = $request->validate([
            'new_end_date' => 'required|date|after:' . $contract->end_date->format('Y-m-d'),
            'new_price' => 'required|numeric|min:0',
        ]);

        DB::beginTransaction();
        try {
            $renewedContract = $contract->renew(
                \Carbon\Carbon::parse($validated['new_end_date']),
                $validated['new_price']
            );

            DB::commit();

            return redirect()->route('inventory.commercial.contracts.show', $renewedContract)
                ->with('success', 'Contrato renovado exitosamente. Nuevo folio: ' . $renewedContract->contract_number);

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al renovar el contrato: ' . $e->getMessage()]);
        }
    }

    /**
     * Start succession process (RN-05).
     */
    public function startSuccession(Request $request, Contract $contract)
    {
        $validated = $request->validate([
            'heir_document_url' => 'required|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $contract->update([
                'is_succession_pending' => true,
                'heir_document_url' => $validated['heir_document_url'],
            ]);

            DB::commit();

            return back()->with('success', 'Proceso de sucesión iniciado. Se requiere documentación legal.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al iniciar sucesión: ' . $e->getMessage()]);
        }
    }

    /**
     * Complete succession process (RN-05).
     */
    public function completeSuccession(Request $request, Contract $contract)
    {
        if (!$contract->is_succession_pending) {
            return back()->withErrors(['error' => 'El contrato no tiene una sucesión pendiente.']);
        }

        $validated = $request->validate([
            'new_customer_id' => 'required|exists:customers,id',
        ]);

        DB::beginTransaction();
        try {
            $contract->update([
                'customer_id' => $validated['new_customer_id'],
                'is_succession_pending' => false,
                'succession_completed_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Sucesión completada. Titular actualizado.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al completar sucesión: ' . $e->getMessage()]);
        }
    }

    /**
     * Generate contract number (RN-02).
     */
    protected function generateContractNumber(): string
    {
        $year = now()->format('Y');
        $last = Contract::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
            ->where('tenant_id', auth()->user()->tenant_id)
            ->whereYear('created_at', $year)
            ->max('id') ?? 0;
        return sprintf('CTR-%s-%05d', $year, $last + 1);
    }

    /**
     * Export contracts to Excel.
     */
    public function export(Request $request)
    {
        // Implementación pendiente con Laravel Excel
        return back()->with('info', 'Funcionalidad de exportación en desarrollo.');
    }
}
