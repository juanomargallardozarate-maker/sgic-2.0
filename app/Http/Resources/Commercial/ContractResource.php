<?php

namespace App\Http\Resources\Commercial;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ContractResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'contract_number' => $this->contract_number,
            'status' => $this->status,
            'payment_type' => $this->payment_type,
            'price' => $this->price,
            'total_price' => $this->total_price,
            'financed_amount' => $this->financed_amount,
            'monthly_payment' => $this->monthly_payment,
            'interest_rate_applied' => $this->interest_rate_applied,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'financing_end_date' => $this->financing_end_date,
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
            
            // Relaciones
            'customer' => [
                'id' => $this->customer->id,
                'name' => $this->customer->name,
                'email' => $this->customer->email,
                'phone' => $this->customer->phone,
            ],
            'crypt' => [
                'id' => $this->crypt->id,
                'code' => $this->crypt->code,
                'level' => $this->crypt->level?->name,
                'block' => $this->crypt->block?->name,
                'section' => $this->crypt->section?->name,
            ],
            'contract_type' => [
                'id' => $this->contractType->id,
                'name' => $this->contractType->name,
                'is_temporary' => $this->contractType->is_temporary,
            ],
            
            // Métricas
            'days_until_expiration' => $this->when($this->end_date, function () {
                return now()->diffInDays($this->end_date, false);
            }),
            'is_expiring_soon' => $this->when($this->end_date, function () {
                return $this->end_date && now()->diffInDays($this->end_date, false) <= 90;
            }),
            'in_grace_period' => $this->when($this->end_date, function () {
                if (!$this->end_date) return false;
                return now()->isAfter($this->end_date) && 
                       now()->diffInDays($this->end_date, false) <= 30;
            }),
        ];
    }
}
