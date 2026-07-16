<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Beneficiary;
use App\Models\Contract;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt as CryptoFacade;
use Illuminate\Contracts\Encryption\DecryptException;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     * US-3.1: Gestión de clientes (altas, bajas, cambios)
     */
    public function index(Request $request)
    {
        $query = Customer::with(['contracts', 'beneficiaries'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'inactive') {
                $query->where('is_active', false);
            } elseif ($request->status === 'deceased') {
                $query->where('is_deceased', true);
            }
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('rfc_hash', 'like', "%{$search}%");
            });
        }

        // Estadísticas
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::where('is_active', true)->where('is_deceased', false)->count();
        $deceasedCustomers = Customer::where('is_deceased', true)->count();
        $inactiveCustomers = Customer::where('is_active', false)->count();

        $customers = $query->paginate(15)->withQueryString();

        return view('commercial.customers.index', compact(
            'customers',
            'totalCustomers',
            'activeCustomers',
            'deceasedCustomers',
            'inactiveCustomers'
        ));
    }

    /**
     * Show the form for creating a new customer.
     */
    public function create()
    {
        return view('commercial.customers.create');
    }

    /**
     * Store a newly created customer in storage.
     * RN-06: Encriptación de datos sensibles (RFC, CURP)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'type' => 'required|in:individual,company',
            'rfc' => 'required|string|max:13',
            'curp' => 'nullable|string|max:18',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'ine_url' => 'nullable|string|max:500',
            'proof_of_address_url' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ], [
            'type.required' => 'El tipo de cliente es obligatorio.',
            'type.in' => 'El tipo debe ser "individual" o "empresa".',
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.max' => 'El RFC no debe exceder 13 caracteres.',
            'curp.max' => 'La CURP no debe exceder 18 caracteres.',
            'name.required' => 'El nombre es obligatorio.',
            'email.email' => 'El correo electrónico no es válido.',
        ]);

        // Validar RFC único (usando hash para búsqueda)
        $rfcHash = hash('sha256', strtoupper(trim($validated['rfc'])));
        $existingCustomer = Customer::where('rfc_hash', $rfcHash)->first();
        if ($existingCustomer) {
            return back()->withErrors(['rfc' => 'Ya existe un cliente registrado con este RFC.'])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Encriptar datos sensibles (RN-06)
            $encryptedRfc = CryptoFacade::encrypt(strtoupper(trim($validated['rfc'])));
            $encryptedCurp = null;
            if (!empty($validated['curp'])) {
                $encryptedCurp = CryptoFacade::encrypt(strtoupper(trim($validated['curp'])));
            }

            // Crear cliente
            $customer = Customer::create([
                'tenant_id' => Auth::user()->tenant_id,
                'type' => $validated['type'],
                'rfc_encrypted' => $encryptedRfc,
                'rfc_hash' => $rfcHash,
                'curp_encrypted' => $encryptedCurp,
                'name' => trim($validated['name']),
                'email' => !empty($validated['email']) ? strtolower(trim($validated['email'])) : null,
                'phone' => $validated['phone'],
                'mobile' => $validated['mobile'],
                'address' => $validated['address'],
                'ine_url' => $validated['ine_url'],
                'proof_of_address_url' => $validated['proof_of_address_url'],
                'is_deceased' => false,
                'is_active' => true,
                'notes' => $validated['notes'],
            ]);

            DB::commit();

            return redirect()->route('commercial.customers.show', $customer)
                ->with('success', 'Cliente registrado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al registrar el cliente: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified customer.
     */
    public function show(Customer $customer)
    {
        $customer->load(['contracts.crypt.level.block.section', 'beneficiaries.customer', 'heirs.customer']);

        // Desencriptar RFC y CURP para visualización
        $rfc = null;
        $curp = null;
        try {
            if ($customer->rfc_encrypted) {
                $rfc = CryptoFacade::decrypt($customer->rfc_encrypted);
            }
            if ($customer->curp_encrypted) {
                $curp = CryptoFacade::decrypt($customer->curp_encrypted);
            }
        } catch (DecryptException $e) {
            // En caso de error, mostrar null
        }

        // Calcular estadísticas del cliente
        $totalContracts = $customer->contracts->count();
        $activeContracts = $customer->contracts->where('status', 'active')->count();
        $totalPaid = $customer->contracts->sum(function ($contract) {
            return $contract->payments->sum('amount');
        });

        return view('commercial.customers.show', compact('customer', 'rfc', 'curp', 'totalContracts', 'activeContracts', 'totalPaid'));
    }

    /**
     * Show the form for editing the specified customer.
     */
    public function edit(Customer $customer)
    {
        // Desencriptar RFC y CURP para edición
        $rfc = null;
        $curp = null;
        try {
            if ($customer->rfc_encrypted) {
                $rfc = CryptoFacade::decrypt($customer->rfc_encrypted);
            }
            if ($customer->curp_encrypted) {
                $curp = CryptoFacade::decrypt($customer->curp_encrypted);
            }
        } catch (DecryptException $e) {
            // En caso de error, mostrar null
        }

        return view('commercial.customers.edit', compact('customer', 'rfc', 'curp'));
    }

    /**
     * Update the specified customer in storage.
     */
    public function update(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'type' => 'required|in:individual,company',
            'rfc' => 'required|string|max:13',
            'curp' => 'nullable|string|max:18',
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'phone' => 'nullable|string|max:20',
            'mobile' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'ine_url' => 'nullable|string|max:500',
            'proof_of_address_url' => 'nullable|string|max:500',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Validar RFC único (si cambió)
        $newRfcHash = hash('sha256', strtoupper(trim($validated['rfc'])));
        if ($customer->rfc_hash !== $newRfcHash) {
            $existingCustomer = Customer::where('rfc_hash', $newRfcHash)
                ->where('id', '!=', $customer->id)
                ->first();
            if ($existingCustomer) {
                return back()->withErrors(['rfc' => 'Ya existe un cliente registrado con este RFC.'])
                    ->withInput();
            }
        }

        DB::beginTransaction();
        try {
            $updateData = [
                'type' => $validated['type'],
                'name' => trim($validated['name']),
                'email' => !empty($validated['email']) ? strtolower(trim($validated['email'])) : null,
                'phone' => $validated['phone'],
                'mobile' => $validated['mobile'],
                'address' => $validated['address'],
                'ine_url' => $validated['ine_url'],
                'proof_of_address_url' => $validated['proof_of_address_url'],
                'notes' => $validated['notes'],
            ];

            // Actualizar RFC encriptado si cambió
            if ($customer->rfc_hash !== $newRfcHash) {
                $updateData['rfc_encrypted'] = CryptoFacade::encrypt(strtoupper(trim($validated['rfc'])));
                $updateData['rfc_hash'] = $newRfcHash;
            }

            // Actualizar CURP encriptado
            if (!empty($validated['curp'])) {
                $updateData['curp_encrypted'] = CryptoFacade::encrypt(strtoupper(trim($validated['curp'])));
            } else {
                $updateData['curp_encrypted'] = null;
            }

            $customer->update($updateData);

            DB::commit();

            return redirect()->route('commercial.customers.show', $customer)
                ->with('success', 'Cliente actualizado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al actualizar el cliente: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Mark customer as deceased (baja por fallecimiento).
     */
    public function markAsDeceased(Request $request, Customer $customer)
    {
        if ($customer->is_deceased) {
            return back()->withErrors(['error' => 'El cliente ya está marcado como fallecido.']);
        }

        $validated = $request->validate([
            'deceased_at' => 'required|date|before_or_equal:today',
            'death_certificate_url' => 'required|string|max:500',
            'heir_declaration_url' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $customer->update([
                'is_deceased' => true,
                'deceased_at' => $validated['deceased_at'],
                'death_certificate_url' => $validated['death_certificate_url'],
                'heir_declaration_url' => $validated['heir_declaration_url'],
            ]);

            DB::commit();

            return back()->with('success', 'Cliente marcado como fallecido. Se han activado los procesos de sucesión correspondientes.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al marcar como fallecido: ' . $e->getMessage()]);
        }
    }

    /**
     * Deactivate customer (baja administrativa).
     */
    public function deactivate(Request $request, Customer $customer)
    {
        if ($customer->contracts()->where('status', 'active')->exists()) {
            return back()->withErrors(['error' => 'No se puede desactivar un cliente con contratos activos.']);
        }

        DB::beginTransaction();
        try {
            $customer->update([
                'is_active' => false,
            ]);

            DB::commit();

            return back()->with('success', 'Cliente desactivado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al desactivar el cliente: ' . $e->getMessage()]);
        }
    }

    /**
     * Reactivate customer.
     */
    public function reactivate(Customer $customer)
    {
        DB::beginTransaction();
        try {
            $customer->update([
                'is_active' => true,
            ]);

            DB::commit();

            return back()->with('success', 'Cliente reactivado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al reactivar el cliente: ' . $e->getMessage()]);
        }
    }

    /**
     * Export customers to CSV/Excel.
     */
    public function export(Request $request)
    {
        // Implementación básica de exportación
        $customers = Customer::with(['contracts', 'beneficiaries'])->get();

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="clientes_' . date('Y-m-d') . '.csv"',
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            
            // Encabezados
            fputcsv($file, ['ID', 'Tipo', 'Nombre', 'RFC', 'Email', 'Teléfono', 'Móvil', 'Estado', 'Fallecido', 'Contratos', 'Beneficiarios']);

            // Datos
            foreach ($customers as $customer) {
                $rfc = null;
                try {
                    if ($customer->rfc_encrypted) {
                        $rfc = CryptoFacade::decrypt($customer->rfc_encrypted);
                    }
                } catch (DecryptException $e) {
                    $rfc = 'ERROR';
                }

                fputcsv($file, [
                    $customer->id,
                    $customer->type === 'individual' ? 'Persona Física' : 'Empresa',
                    $customer->name,
                    $rfc,
                    $customer->email,
                    $customer->phone,
                    $customer->mobile,
                    $customer->is_active ? 'Activo' : 'Inactivo',
                    $customer->is_deceased ? 'Sí' : 'No',
                    $customer->contracts->count(),
                    $customer->beneficiaries->count(),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Add beneficiary to customer.
     */
    public function addBeneficiary(Request $request, Customer $customer)
    {
        $validated = $request->validate([
            'beneficiary_customer_id' => 'required|exists:customers,id',
            'relationship' => 'required|string|max:100',
            'is_primary' => 'boolean',
        ]);

        // Validar que el beneficiario no sea el mismo cliente
        if ($validated['beneficiary_customer_id'] == $customer->id) {
            return back()->withErrors(['beneficiary_customer_id' => 'El cliente no puede ser su propio beneficiario.']);
        }

        DB::beginTransaction();
        try {
            // Si es primary, quitar el flag de otros beneficiarios
            if ($validated['is_primary']) {
                Beneficiary::where('customer_id', $customer->id)
                    ->update(['is_primary' => false]);
            }

            Beneficiary::create([
                'tenant_id' => Auth::user()->tenant_id,
                'customer_id' => $customer->id,
                'relationship' => $validated['relationship'],
                'is_primary' => $validated['is_primary'] ?? false,
            ]);

            DB::commit();

            return back()->with('success', 'Beneficiario agregado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al agregar beneficiario: ' . $e->getMessage()]);
        }
    }

    /**
     * Remove beneficiary from customer.
     */
    public function removeBeneficiary(Customer $customer, Beneficiary $beneficiary)
    {
        if ($beneficiary->customer_id !== $customer->id) {
            return back()->withErrors(['error' => 'El beneficiario no pertenece a este cliente.']);
        }

        DB::beginTransaction();
        try {
            $beneficiary->delete();

            DB::commit();

            return back()->with('success', 'Beneficiario eliminado exitosamente.');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al eliminar beneficiario: ' . $e->getMessage()]);
        }
    }
}
