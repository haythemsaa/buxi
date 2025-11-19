<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreContractRequest extends FormRequest
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
            'customer_id' => ['required', 'exists:customers,id'],
            'box_id' => ['required', 'exists:boxes,id'],
            'site_id' => ['required', 'exists:sites,id'],
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['nullable', 'date', 'after:start_date'],
            'initial_duration_months' => ['required', 'integer', 'min:1', 'max:60'],
            'price_monthly_ht' => ['required', 'numeric', 'min:0'],
            'tax_rate' => ['required', 'numeric', 'min:0', 'max:100'],
            'insurance_monthly' => ['nullable', 'numeric', 'min:0'],
            'deposit_amount' => ['required', 'numeric', 'min:0'],
            'payment_method' => ['required', 'in:cash,check,bank_transfer,sepa,card'],
            'payment_day' => ['required', 'integer', 'min:1', 'max:28'],
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
            'customer_id' => 'client',
            'box_id' => 'box',
            'site_id' => 'site',
            'start_date' => 'date de début',
            'end_date' => 'date de fin',
            'initial_duration_months' => 'durée initiale',
            'price_monthly_ht' => 'prix mensuel HT',
            'tax_rate' => 'taux de TVA',
            'insurance_monthly' => 'assurance mensuelle',
            'deposit_amount' => 'montant du dépôt',
            'payment_method' => 'mode de paiement',
            'payment_day' => 'jour de prélèvement',
            'access_code' => 'code d\'accès',
            'notes' => 'notes',
        ];
    }
}
