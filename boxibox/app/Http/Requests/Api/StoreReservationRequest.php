<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class StoreReservationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Anyone can create a reservation
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        $rules = [
            'box_id' => 'required|exists:boxes,id',
            'start_date' => 'required|date|after_or_equal:today',
            'duration_months' => 'required|integer|min:1|max:60',
            'insurance' => 'nullable|boolean',
            'promo_code' => 'nullable|string|max:50',
        ];

        // If not authenticated, require guest information
        if (!auth()->check()) {
            $rules['guest_first_name'] = 'required|string|max:100';
            $rules['guest_last_name'] = 'required|string|max:100';
            $rules['guest_email'] = 'required|email|max:255';
            $rules['guest_phone'] = 'required|string|max:20';
        } else {
            $rules['guest_phone'] = 'nullable|string|max:20';
        }

        return $rules;
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'box_id.required' => 'Vous devez sélectionner un box.',
            'box_id.exists' => 'Le box sélectionné n\'existe pas.',
            'start_date.required' => 'La date de début est requise.',
            'start_date.after_or_equal' => 'La date de début ne peut pas être dans le passé.',
            'duration_months.required' => 'La durée est requise.',
            'duration_months.min' => 'La durée doit être d\'au moins 1 mois.',
            'duration_months.max' => 'La durée ne peut pas dépasser 60 mois.',
            'guest_first_name.required' => 'Le prénom est requis.',
            'guest_last_name.required' => 'Le nom est requis.',
            'guest_email.required' => 'L\'email est requis.',
            'guest_email.email' => 'L\'email doit être valide.',
            'guest_phone.required' => 'Le téléphone est requis.',
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
    }
}
