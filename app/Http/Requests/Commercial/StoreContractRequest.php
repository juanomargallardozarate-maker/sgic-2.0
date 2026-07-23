<?php

namespace App\Http\Requests\Commercial;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'customer_id' => ['required', 'exists:customers,id'],
            'crypt_id' => ['required', 'exists:crypts,id'],
            'contract_type_id' => ['required', 'exists:contract_types,id'],
            'payment_type' => ['required', Rule::in(['cash', 'installments', 'mixed'])],
            'installments_count' => ['nullable', 'integer', 'min:1'],
            'down_payment' => ['nullable', 'numeric', 'min:0'],
            'financed_amount' => ['nullable', 'numeric', 'min:0'],
            'interest_rate' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'monthly_payment' => ['nullable', 'numeric', 'min:0'],
            'start_date' => ['required', 'date'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'annual_maintenance_fee' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string', 'max:1000'],
            'beneficiaries' => ['nullable', 'array'],
            'heirs' => ['nullable', 'array'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'crypt_id.required' => 'Debe seleccionar una cripta.',
            'customer_id.required' => 'Debe seleccionar un cliente.',
            'contract_type_id.required' => 'Debe seleccionar un tipo de contrato.',
            'end_date.after' => 'La fecha de vigencia debe ser posterior a la fecha de inicio.',
            'payment_type.in' => 'El tipo de pago debe ser contado, crédito o mixto.',
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            // Validar que el payment_type sea requerido si es mixto
            if ($this->payment_type === 'mixed' && empty($this->down_payment)) {
                $validator->errors()->add('down_payment', 'El pago mixto requiere un monto de enganche.');
            }
        });
    }
}
