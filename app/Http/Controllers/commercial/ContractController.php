<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Crypt;
use App\Models\ContractType;
use App\Models\Beneficiary;
use App\Models\Heir;
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

        return view('commercial.contracts.create', compact('contractTypes', 'customers', 'availableCrypts'));
    }

    /**
     * Store a newly created contract in storage.
     * RN-02: Validación de contrato temporal vs perpetuo
     * RN-01: Solo criptas disponibles
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'crypt_id' => 'required|exists:crypts,id',
            'contract_type_id' => 'required|exists:contract_types,id',
            'price' => 'required|numeric|min:0',
            'annual_maintenance_fee' => 'required|numeric|min:0',
            'payment_type' => 'required|in:cash,installments,mixed',
            'installments_count' => 'nullable|integer|min:1',
            'down_payment' => 'nullable|numeric|min:0',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
            'beneficiaries' => 'nullable|array',
            'heirs' => 'nullable|array',
            'notes' => 'nullable|string|max:1000',
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
                'price' => $validated['price'],
                'annual_maintenance_fee' => $validated['annual_maintenance_fee'],
                'payment_type' => $validated['payment_type'],
                'installments_count' => $validated['installments_count'] ?? null,
                'down_payment' => $validated['down_payment'] ?? null,
                'status' => 'draft',
                'notes' => $validated['notes'] ?? null,
                'created_by_user_id' => Auth::id(),
            ]);

            // Guardar beneficiarios si existen
            if (!empty($validated['beneficiaries'])) {
                foreach ($validated['beneficiaries'] as $beneficiary) {
                    Beneficiary::create([
                        'tenant_id' => Auth::user()->tenant_id,
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
                        'tenant_id' => Auth::user()->tenant_id,
                        'contract_id' => $contract->id,
                        'customer_id' => $heir['customer_id'],
                        'is_designated' => true,
                        'inheritance_percent' => $heir['inheritance_percent'] ?? 0,
                    ]);
                }
            }

            DB::commit();

            return redirect()->route('commercial.contracts.show', $contract)
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

        DB::beginTransaction();
        try {
            $contract->update($validated);

            DB::commit();

            return redirect()->route('commercial.contracts.show', $contract)
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

            return redirect()->route('commercial.contracts.show', $renewedContract)
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
