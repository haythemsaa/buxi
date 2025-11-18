<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class CalculatePriceRequest extends FormRequest
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
            'box_id' => 'required|exists:boxes,id',
            'duration_months' => 'required|integer|min:1|max:60',
            'insurance' => 'nullable|boolean',
            'promo_code' => 'nullable|string|max:50',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'box_id.required' => 'Vous devez sélectionner un box.',
            'box_id.exists' => 'Le box sélectionné n\'existe pas.',
            'duration_months.required' => 'La durée est requise.',
            'duration_months.min' => 'La durée doit être d\'au moins 1 mois.',
            'duration_months.max' => 'La durée ne peut pas dépasser 60 mois.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert insurance to boolean if it's a string
        if ($this->has('insurance')) {
            $this->merge([
                'insurance' => filter_var($this->insurance, FILTER_VALIDATE_BOOLEAN),
            ]);
        }

        // Convert promo code to uppercase
        if ($this->has('promo_code') && $this->promo_code) {
            $this->merge([
                'promo_code' => strtoupper($this->promo_code),
            ]);
        }
    }
}
