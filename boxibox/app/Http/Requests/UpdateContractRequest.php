<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContractRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Authorization handled by policy
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'price_monthly_ht' => ['sometimes', 'numeric', 'min:0'],
            'tax_rate' => ['sometimes', 'numeric', 'min:0', 'max:100'],
            'insurance_monthly' => ['nullable', 'numeric', 'min:0'],
            'payment_method' => ['sometimes', 'in:cash,check,bank_transfer,sepa,card'],
            'payment_day' => ['sometimes', 'integer', 'min:1', 'max:28'],
            'access_code' => ['nullable', 'string', 'max:20'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'end_date' => 'date de fin',
            'price_monthly_ht' => 'prix mensuel HT',
            'tax_rate' => 'taux de TVA',
            'insurance_monthly' => 'assurance mensuelle',
            'payment_method' => 'mode de paiement',
            'payment_day' => 'jour de prélèvement',
            'access_code' => 'code d\'accès',
            'notes' => 'notes',
        ];
    }
}
