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
        'tenant_id',
        'customer_id',
        'crypt_id',
        'contract_type_id',
        'contract_number',
        'status',
        'start_date',
        'end_date',
        'signed_date',
        'expiry_warning_sent_at',
        'total_amount',
        'amount_paid',
        'balance',
        'payment_installments',
        'installments_paid',
        'digital_signature_hash',
        'ip_address_at_signing',
        'signed_at',
        'special_conditions',
        'metadata',
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
        'signed_date' => 'date',
        'expiry_warning_sent_at' => 'datetime',
        'total_amount' => 'decimal:2',
        'amount_paid' => 'decimal:2',
        'balance' => 'decimal:2',
        'signed_at' => 'datetime',
        'metadata' => 'array',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];

    // Relaciones
    public function tenant(): BelongsTo
    {
        return $this->belongsTo(Tenant::class);
    }

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

    // Accessors (RN-02)
    public function getIsPerpetualAttribute(): bool
    {
        return $this->contractType->modality === 'perpetual';
    }

    public function getIsTemporaryAttribute(): bool
    {
        return $this->contractType->modality === 'temporary';
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

    public function getIsSignedAttribute(): bool
    {
        return $this->status === 'active' && $this->signed_at !== null;
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeTemporary($query)
    {
        return $query->whereHas('contractType', fn($q) => $q->where('modality', 'temporary'));
    }

    public function scopePerpetual($query)
    {
        return $query->whereHas('contractType', fn($q) => $q->where('modality', 'perpetual'));
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
                     ->whereIn('status', ['active', 'expired']);
    }

    public function scopeInSuccession($query)
    {
        return $query->where('status', 'in_succession');
    }

    public function scopePendingSignature($query)
    {
        return $query->where('status', 'pending_signature');
    }

    // Métodos de negocio
    public function sign(string $signatureHash, string $ipAddress): void
    {
        $this->update([
            'status' => 'active',
            'signed_at' => now(),
            'signed_date' => now()->format('Y-m-d'),
            'digital_signature_hash' => $signatureHash,
            'ip_address_at_signing' => $ipAddress,
        ]);
    }

    public function expire(): void
    {
        $this->update(['status' => 'expired']);
    }

    public function cancel(): void
    {
        $this->update(['status' => 'cancelled']);
    }

    public function startSuccession(): void
    {
        $this->update(['status' => 'in_succession']);
    }

    public function completeSuccession(): void
    {
        $this->update(['status' => 'active']);
    }

    public function canBeTransferred(): bool
    {
        return $this->status !== 'in_succession' && 
               $this->balance <= 0;
    }

    public function getProgressPercentageAttribute(): float
    {
        if ($this->payment_installments <= 0) return 100.00;
        return round(($this->installments_paid / $this->payment_installments) * 100, 2);
    }
}