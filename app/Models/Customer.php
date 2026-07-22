<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Models\Traits\BelongsToTenant;

class Customer extends Model
{
    use HasFactory, SoftDeletes, BelongsToTenant;

    protected $fillable = [
        'type',
        'rfc_encrypted',
        'rfc_hash',
        'curp_encrypted',
        'name',
        'email',
        'phone',
        'mobile',
        'address',
        'colonia',
        'ciudad',
        'estado',
        'codigo_postal',
        'ine_url',
        'proof_of_address_url',
        'is_deceased',
        'deceased_at',
        'death_certificate_url',
        'heir_declaration_url',
        'notes',
        'is_active',
        // Campos de verificación WhatsApp
        'whatsapp_verification_code',
        'phone_verified',
        'whatsapp_verified_at',
    ];

    protected $casts = [
        'is_deceased' => 'boolean',
        'deceased_at' => 'date',
        'is_active' => 'boolean',
        'phone_verified' => 'boolean',
        'whatsapp_verified_at' => 'datetime',
    ];

    protected $appends = [
        'rfc',
        'curp',
    ];

    /**
     * Get the decrypted RFC attribute.
     */
    public function getRfcAttribute(): ?string
    {
        if (!$this->rfc_encrypted) {
            return null;
        }
        
        try {
            $key = base64_decode(str_replace('base64:', '', config('app.key')));
            $ivLength = openssl_cipher_iv_length('AES-256-CBC');
            
            $encrypted = base64_decode($this->rfc_encrypted);
            if ($encrypted === false || strlen($encrypted) < $ivLength) {
                return $this->rfc_encrypted;
            }
            
            $iv = substr($encrypted, 0, $ivLength);
            $encryptedData = substr($encrypted, $ivLength);
            
            $decrypted = openssl_decrypt($encryptedData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            
            return $decrypted ?: $this->rfc_encrypted;
        } catch (\Exception $e) {
            return $this->rfc_encrypted;
        }
    }

    /**
     * Get the decrypted CURP attribute.
     */
    public function getCurpAttribute(): ?string
    {
        if (!$this->curp_encrypted) {
            return null;
        }
        
        try {
            $key = base64_decode(str_replace('base64:', '', config('app.key')));
            $ivLength = openssl_cipher_iv_length('AES-256-CBC');
            
            $encrypted = base64_decode($this->curp_encrypted);
            if ($encrypted === false || strlen($encrypted) < $ivLength) {
                return $this->curp_encrypted;
            }
            
            $iv = substr($encrypted, 0, $ivLength);
            $encryptedData = substr($encrypted, $ivLength);
            
            $decrypted = openssl_decrypt($encryptedData, 'AES-256-CBC', $key, OPENSSL_RAW_DATA, $iv);
            
            return $decrypted ?: $this->curp_encrypted;
        } catch (\Exception $e) {
            return $this->curp_encrypted;
        }
    }

    public function contracts(): HasMany
    {
        return $this->hasMany(Contract::class);
    }

    public function beneficiaries(): HasMany
    {
        return $this->hasMany(Beneficiary::class);
    }

    public function heirs(): HasMany
    {
        return $this->hasMany(Heir::class);
    }

    /**
     * Verificar si el cliente tiene el teléfono verificado
     */
    public function isPhoneVerified(): bool
    {
        return $this->phone_verified && $this->whatsapp_verified_at !== null;
    }

    /**
     * Generar y guardar código de verificación
     */
    public function generateVerificationCode(): string
    {
        $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
        
        $this->update([
            'whatsapp_verification_code' => $code,
        ]);

        return $code;
    }

    /**
     * Validar código de verificación
     */
    public function verifyCode(string $code): bool
    {
        if ($this->whatsapp_verification_code !== $code) {
            return false;
        }

        $this->update([
            'phone_verified' => true,
            'whatsapp_verified_at' => now(),
            'whatsapp_verification_code' => null,
        ]);

        return true;
    }

    /**
     * Marcar teléfono como no verificado
     */
    public function markPhoneAsUnverified(): void
    {
        $this->update([
            'phone_verified' => false,
            'whatsapp_verified_at' => null,
            'whatsapp_verification_code' => null,
        ]);
    }
}