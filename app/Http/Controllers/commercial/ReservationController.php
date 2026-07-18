<?php

namespace App\Http\Controllers\Commercial;

use App\Http\Controllers\Controller;
use App\Models\Reservation;
use App\Models\Customer;
use App\Models\Crypt;
use App\Models\Contract;
use App\Models\ContractType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ReservationController extends Controller
{
    /**
     * Display a listing of reservations.
     * US-3.3: Gestión de reservas de criptas
     */
    public function index(Request $request)
    {
        $query = Reservation::with(['customer', 'crypt', 'contract'])
            ->orderBy('created_at', 'desc');

        // Filtros
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('customer', fn($q) => $q->where('name', 'like', "%{$search}%"))
                  ->orWhereHas('crypt', fn($q) => $q->where('code', 'like', "%{$search}%"));
            });
        }

        // Estadísticas
        $activeReservations = Reservation::active()->count();
        $expiringSoon = Reservation::active()->expiringSoon(7)->count();
        $expiredReservations = Reservation::expired()->count();
        $convertedToday = Reservation::converted()
            ->whereDate('updated_at', today())
            ->count();

        $reservations = $query->paginate(15)->withQueryString();

        return view('commercial.reservations.index', compact(
            'reservations',
            'activeReservations',
            'expiringSoon',
            'expiredReservations',
            'convertedToday'
        ));
    }

    /**
     * Show the form for creating a new reservation.
     */
    public function create()
    {
        $customers = Customer::where('is_active', true)->get();
        $availableCrypts = Crypt::whereHas('cryptStatus', fn($q) => $q->where('code', 'available'))
            ->where('is_blocked', false)
            ->with(['level.block.section'])
            ->get();

        return view('commercial.reservations.create', compact('customers', 'availableCrypts'));
    }

    /**
     * Store a newly created reservation in storage.
     * US-3.3: Reserva con expiración automática (72 horas por defecto)
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'crypt_id' => 'required|exists:crypts,id',
            'deposit_amount' => 'nullable|numeric|min:0',
            'expiration_hours' => 'nullable|integer|min:1|max:720', // Máximo 30 días
            'notes' => 'nullable|string|max:1000',
        ], [
            'crypt_id.required' => 'Debe seleccionar una cripta.',
            'customer_id.required' => 'Debe seleccionar un cliente.',
            'crypt_id.exists' => 'La cripta seleccionada no existe.',
            'customer_id.exists' => 'El cliente seleccionado no existe.',
        ]);

        // Validar que la cripta esté disponible
        $crypt = Crypt::findOrFail($validated['crypt_id']);
        if (!$crypt->isAvailableForSale) {
            return back()->withErrors(['crypt_id' => 'La cripta seleccionada no está disponible para reserva.'])
                ->withInput();
        }

        // Verificar que no haya otra reserva activa para la misma cripta
        $existingReservation = Reservation::active()
            ->where('crypt_id', $validated['crypt_id'])
            ->first();

        if ($existingReservation) {
            return back()->withErrors(['crypt_id' => 'Esta cripta ya tiene una reserva activa.'])
                ->withInput();
        }

        DB::beginTransaction();
        try {
            // Calcular fecha de expiración
            $expirationHours = $validated['expiration_hours'] ?? 72; // 72 horas por defecto
            $expiresAt = now()->addHours($expirationHours);

            // Crear reserva
            $reservation = Reservation::create([
                'customer_id' => $validated['customer_id'],
                'crypt_id' => $validated['crypt_id'],
                'deposit_amount' => $validated['deposit_amount'] ?? 0,
                'reserved_at' => now(),
                'expires_at' => $expiresAt,
                'status' => Reservation::STATUS_ACTIVE,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Marcar cripta como reservada (cambiar estado temporalmente)
            $reservedStatus = \App\Models\CryptStatus::where('code', 'reserved')->first();
            if ($reservedStatus) {
                $crypt->crypt_status_id = $reservedStatus->id;
                $crypt->save();
            }

            DB::commit();

            return redirect()->route('commercial.reservations.show', $reservation)
                ->with('success', "Reserva creada exitosamente. Expira en {$expirationHours} horas.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => 'Error al crear la reserva: ' . $e->getMessage()])
                ->withInput();
        }
    }

    /**
     * Display the specified reservation.
     */
    public function show(Reservation $reservation)
    {
        $reservation->load(['customer', 'crypt.level.block.section', 'contract']);

        // Calcular tiempo restante
        $timeRemaining = null;
        $isExpired = false;
        
        if ($reservation->expires_at) {
            $timeRemaining = now()->diffInSeconds($reservation->expires_at, false);
            $isExpired = $timeRemaining <= 0 && $reservation->status === Reservation::STATUS_ACTIVE;
        }

        // Verificar si puede convertirse
        $canConvert = $reservation->canBeConverted();

        // Obtener tipos de contrato para conversión rápida
        $contractTypes = ContractType::all();

        return view('commercial.reservations.show', compact(
            'reservation',
            'timeRemaining',
            'isExpired',
            'canConvert',
            'contractTypes'
        ));
    }

    /**
     * Extend the expiration of a reservation.
     */
    public function extend(Request $request, Reservation $reservation)
    {
        if (!$reservation->isActive()) {
            return back()->withErrors(['error' => 'Solo las reservas activas pueden extenderse.']);
        }

        $validated = $request->validate([
            'hours' => 'required|integer|min:1|max:720',
        ]);

        try {
            $reservation->extendExpiration($validated['hours']);

            return back()->with('success', 
                "Reserva extendida por {$validated['hours']} horas. Nueva expiración: {$reservation->expires_at->format('d/m/Y H:i')}");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al extender la reserva: ' . $e->getMessage()]);
        }
    }

    /**
     * Cancel a reservation.
     */
    public function cancel(Request $request, Reservation $reservation)
    {
        if (!in_array($reservation->status, [Reservation::STATUS_ACTIVE, Reservation::STATUS_EXPIRED])) {
            return back()->withErrors(['error' => 'Solo las reservas activas o expiradas pueden cancelarse.']);
        }

        $validated = $request->validate([
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $reservation->cancel($validated['reason'] ?? null);

            return back()->with('success', 'Reserva cancelada exitosamente. La cripta ha sido liberada.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al cancelar la reserva: ' . $e->getMessage()]);
        }
    }

    /**
     * Convert reservation to contract.
     * US-3.3: Conversión de reserva a contrato
     */
    public function convertToContract(Request $request, Reservation $reservation)
    {
        if (!$reservation->canBeConverted()) {
            return back()->withErrors(['error' => 'La reserva no puede ser convertida a contrato. Verifique que esté activa y la cripta disponible.']);
        }

        $validated = $request->validate([
            'contract_type_id' => 'required|exists:contract_types,id',
            'price' => 'required|numeric|min:0',
            'annual_maintenance_fee' => 'nullable|numeric|min:0',
            'payment_type' => 'required|in:cash,installments,mixed',
            'installments_count' => 'nullable|integer|min:1',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after:start_date',
        ], [
            'contract_type_id.required' => 'Debe seleccionar un tipo de contrato.',
            'price.required' => 'El precio es obligatorio.',
            'payment_type.required' => 'El tipo de pago es obligatorio.',
            'start_date.required' => 'La fecha de inicio es obligatoria.',
        ]);

        try {
            $contract = $reservation->convertToContract($validated);

            return redirect()->route('commercial.contracts.show', $contract)
                ->with('success', "Reserva convertida a contrato exitosamente. Folio: {$contract->contract_number}");

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al convertir la reserva: ' . $e->getMessage()]);
        }
    }

    /**
     * Manually mark reservation as expired (for admin purposes).
     */
    public function markAsExpired(Reservation $reservation)
    {
        if ($reservation->status !== Reservation::STATUS_ACTIVE) {
            return back()->withErrors(['error' => 'Solo las reservas activas pueden marcarse como expiradas.']);
        }

        try {
            $reservation->markAsExpired();

            return back()->with('success', 'Reserva marcada como expirada. La cripta ha sido liberada.');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Error al marcar como expirada: ' . $e->getMessage()]);
        }
    }

    /**
     * Export reservations to Excel.
     */
    public function export(Request $request)
    {
        // Implementación básica - se puede expandir con Laravel Excel
        $reservations = Reservation::with(['customer', 'crypt', 'contract'])
            ->orderBy('created_at', 'desc')
            ->get();

        // Aquí iría la lógica de exportación a Excel usando maatwebsite/excel
        // Por ahora retornamos una vista simple
        return view('commercial.reservations.export', compact('reservations'));
    }
}
