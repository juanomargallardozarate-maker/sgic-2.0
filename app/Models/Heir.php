<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\Traits\BelongsToTenant;

class Heir extends Model
{
    use HasFactory, BelongsToTenant;

    protected $fillable = [
        'contract_id',
        'full_name',
        'rfc',
        'curp',
        'email',
        'phone',
        'street',
        'exterior_number',
        'interior_number',
        'neighborhood',
        'city',
        'state',
        'zip_code',
        'succession_type',
        'legal_document_number',
        'legal_document_date',
        'notary_public',
        'notary_number',
        'state_of_issue',
        'death_certificate_path',
        'succession_document_path',
        'heir_ine_path',
        'validation_status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'legal_document_date' => 'date',
        'reviewed_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Relación con el contrato
     */
    public function contract(): BelongsTo
    {
        return $this->belongsTo(Contract::class);
    }

    /**
     * Relación con el usuario que revisó
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /**
     * Scope para herederos pendientes de revisión
     */
    public function scopePendingReview($query)
    {
        return $query->where('validation_status', 'pending_review');
    }

    /**
     * Scope para herederos aprobados
     */
    public function scopeApproved($query)
    {
        return $query->where('validation_status', 'approved');
    }

    /**
     * Scope para herederos rechazados
     */
    public function scopeRejected($query)
    {
        return $query->where('validation_status', 'rejected');
    }

    /**
     * Obtener tipo de sucesión formateado
     */
    public function getSuccessionTypeFormattedAttribute(): string
    {
        $types = [
            'testamentary' => 'Testamentaria',
            'intestate' => 'Intestada',
            'judicial' => 'Judicial',
        ];
        
        return $types[$this->succession_type] ?? $this->succession_type;
    }

    /**
     * Obtener dirección completa formateada
     */
    public function getFullAddressAttribute(): string
    {
        $address = "{$this->street} #{$this->exterior_number}";
        
        if ($this->interior_number) {
            $address .= " Int. {$this->interior_number}";
        }
        
        $address .= ", {$this->neighborhood}, {$this->city}, {$this->state} C.P. {$this->zip_code}";
        
        return $address;
    }
}