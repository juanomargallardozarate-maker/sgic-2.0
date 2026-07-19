<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;
use Carbon\Carbon;

class Contract extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'customer_id',
        'crypt_id',
        'contract_type_id',
        'contract_number',
        'start_date',
        'end_date',
        'financing_end_date',
        'price',
        'total_price',
        'annual_maintenance_fee',
        'payment_type',
        'installments_count',
        'down_payment',
        'financed_amount',
        'interest_rate_applied',
        'monthly_payment',
        'maintenance_fee_snapshot',
        'interest_rate_snapshot',
        'whatsapp_verification_code',
        'whatsapp_verified_at',
        'phone_verified',
        'verification_code',
        'verified_at',
        'is_succession_pending',
        'heir_document_url',
        'succession_completed_at',
        'signed_at',
        'signature_hash',
        'signature_ip',
        'signed_document_url',
        'status',
        'grace_period_ends_at',
        'decay_process_started_at',
        'notes',
        'created_by_user_id',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'financing_end_date' => 'date',
        'price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'annual_maintenance_fee' => 'decimal:2',
        'down_payment' => 'decimal:2',
        'financed_amount' => 'decimal:2',
        'interest_rate_applied' => 'decimal:4',
        'monthly_payment' => 'decimal:2',
        'maintenance_fee_snapshot' => 'decimal:2',
        'interest_rate_snapshot' => 'decimal:4',
        'is_succession_pending' => 'boolean',
        'phone_verified' => 'boolean',
        'succession_completed_at' => 'datetime',
        'signed_at' => 'datetime',
        'whatsapp_verified_at' => 'datetime',
        'verified_at' => 'datetime',
        'grace_period_ends_at' => 'date',
        'decay_process_started_at' => 'date',
    ];

    // Relaciones
    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function crypt(): BelongsTo
    {
        return $this->belongsTo(Crypt::class);
    }

    public function contractType(): BelongsTo
    {
        return $this->belongsTo(ContractType::class);
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(Beneficiary::class);
    }

    public function heirs(): HasMany
    {
        return $this->hasMany(Heir::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(Payment::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function debts(): HasMany
    {
        return $this->hasMany(Debt::class);
    }

    // Accessors (RN-02, RN-03)
    public function getIsPerpetualAttribute(): bool
    {
        return !$this->contractType->is_temporary;
    }

    public function getIsTemporaryAttribute(): bool
    {
        return $this->contractType->is_temporary;
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->end_date && $this->end_date->isPast();
    }

    public function getDaysUntilExpiryAttribute(): ?int
    {
        if (!$this->end_date) return null;
        return now()->diffInDays($this->end_date, false);
    }

    public function getIsInGracePeriodAttribute(): bool
    {
        return $this->status === 'grace_period';
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTemporary($query)
    {
        return $query->whereHas('contractType', fn($q) => $q->where('is_temporary', true));
    }

    public function scopePerpetual($query)
    {
        return $query->whereHas('contractType', fn($q) => $q->where('is_temporary', false));
    }

    public function scopeExpiringSoon($query, int $days = 90)
    {
        return $query->temporary()
                     ->active()
                     ->whereNotNull('end_date')
                     ->whereBetween('end_date', [now(), now()->addDays($days)]);
    }

    public function scopeExpired($query)
    {
        return $query->temporary()
                     ->whereNotNull('end_date')
                     ->where('end_date', '<', now())
                     ->whereIn('status', ['active', 'expired', 'grace_period']);
    }

    public function scopeInGracePeriod($query)
    {
        return $query->where('status', 'grace_period');
    }

    public function scopeSuccessionPending($query)
    {
        return $query->where('is_succession_pending', true);
    }

    // Métodos de negocio (RN-03)
    public function enterGracePeriod(int $years): void
    {
        $this->update([
            'status' => 'grace_period',
            'grace_period_ends_at' => $this->end_date->addYears($years),
        ]);
    }

    public function startDecayProcess(): void
    {
        $this->update([
            'status' => 'decaying',
            'decay_process_started_at' => now(),
        ]);
        $this->crypt->changeStatus('decaying');
    }

    public function renew(Carbon $newEndDate, float $newPrice): self
    {
        $renewed = $this->replicate()->fill([
            'start_date' => $this->end_date->addDay(),
            'end_date' => $newEndDate,
            'price' => $newPrice,
            'status' => 'active',
            'contract_number' => $this->generateContractNumber(),
        ]);
        $renewed->save();
        $this->update(['status' => 'renewed']);
        return $renewed;
    }

    public function canBeTransferred(): bool
    {
        return !$this->is_succession_pending &&
               $this->debts()->where('status', '!=', 'paid')->count() === 0;
    }

    protected function generateContractNumber(): string
    {
        $year = now()->format('Y');
        $last = self::withoutGlobalScope(\App\Models\Scopes\TenantScope::class)
                    ->where('tenant_id', auth()->user()->tenant_id)
                    ->whereYear('created_at', $year)
                    ->max('id') ?? 0;
        return sprintf('CTR-%s-%05d', $year, $last + 1);
    }
}