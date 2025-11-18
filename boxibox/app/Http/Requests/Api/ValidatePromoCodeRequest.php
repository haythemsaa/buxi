<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class ValidatePromoCodeRequest extends FormRequest
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
            'code' => 'required|string|max:50',
            'box_id' => 'nullable|exists:boxes,id',
            'duration_months' => 'nullable|integer|min:1|max:60',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'code.required' => 'Le code promo est requis.',
            'box_id.exists' => 'Le box sélectionné n\'existe pas.',
            'duration_months.min' => 'La durée doit être d\'au moins 1 mois.',
            'duration_months.max' => 'La durée ne peut pas dépasser 60 mois.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Convert code to uppercase
        if ($this->has('code')) {
            $this->merge([
                'code' => strtoupper($this->code),
            ]);
        }
    }
}
