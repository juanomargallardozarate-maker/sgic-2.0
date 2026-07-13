<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TenantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // O usar $this->user()->hasRole('super_admin')
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:150'],
            'rfc' => [
                'required',
                'string',
                'max:13',
                Rule::unique('tenants', 'rfc')->ignore($this->route('tenant')),
                function ($attribute, $value, $fail) {
                    if (!$this->validateRFC($value)) {
                        $fail('El RFC no es válido. Verifica el formato y los caracteres.');
                    }
                },
            ],
            'subdomain' => [
                'required',
                'string',
                'max:63',
                'alpha_dash',
                Rule::unique('tenants', 'subdomain')->ignore($this->route('tenant')),
            ],
            'plan' => ['required', Rule::in(['basic', 'professional', 'enterprise'])],
            'grace_period_years' => ['nullable', 'integer', 'min:1', 'max:10'],
            'debt_months_to_block' => ['nullable', 'integer', 'min:1', 'max:12'],
            'moratorium_interest_rate' => ['nullable', 'numeric', 'min:0', 'max:10'],
        ];
    }

    /**
     * Custom messages for validation errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'El nombre comercial es obligatorio.',
            'name.max' => 'El nombre no debe exceder los 150 caracteres.',
            'rfc.required' => 'El RFC es obligatorio.',
            'rfc.unique' => 'El RFC ya está registrado en el sistema.',
            'rfc.max' => 'El RFC no debe exceder los 13 caracteres.',
            'subdomain.required' => 'El subdominio es obligatorio.',
            'subdomain.unique' => 'El subdominio ya está en uso.',
            'subdomain.alpha_dash' => 'El subdominio solo puede contener letras, números y guiones.',
            'plan.required' => 'Debe seleccionar un plan.',
            'plan.in' => 'El plan seleccionado no es válido.',
        ];
    }

    /**
     * Validate RFC format (Mexican SAT format).
     */
    private function validateRFC(string $rfc): bool
    {
        $rfc = strtoupper(trim($rfc));
        
        // Validar longitud (12 para PM, 13 para PF)
        $length = strlen($rfc);
        if (!in_array($length, [12, 13])) {
            return false;
        }

        // Expresión regular para validar formato RFC
        // PM: 3 letras + 6 números + 3 alfanuméricos
        // PF: 4 letras + 6 números + 3 alfanuméricos
        $pattern = '/^';
        $pattern .= '(';
        $pattern .= '[A-Z]{3,4}'; // 3 o 4 letras (PM o PF)
        $pattern .= '\d{6}';       // 6 números (año, mes, día)
        $pattern .= '[A-Z0-9]{3}'; // 3 alfanuméricos (homoclave)
        $pattern .= ')';
        $pattern .= '$/';

        if (!preg_match($pattern, $rfc)) {
            return false;
        }

        // Validación básica del homoclave (últimos 3 caracteres)
        $homoclave = substr($rfc, -3);
        if (!preg_match('/^[A-Z0-9]{3}$/', $homoclave)) {
            return false;
        }

        return true;
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Limpiar y estandarizar RFC
        if ($this->has('rfc')) {
            $this->merge([
                'rfc' => strtoupper(str_replace('-', '', trim($this->input('rfc')))),
            ]);
        }
        
        // Limpiar subdominio
        if ($this->has('subdomain')) {
            $this->merge([
                'subdomain' => strtolower(trim($this->input('subdomain'))),
            ]);
        }
    }
}