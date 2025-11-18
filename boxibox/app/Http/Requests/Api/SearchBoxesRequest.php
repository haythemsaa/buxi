<?php

namespace App\Http\Requests\Api;

use Illuminate\Foundation\Http\FormRequest;

class SearchBoxesRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'site_id' => 'nullable|exists:sites,id',
            'min_volume' => 'nullable|numeric|min:0|max:1000',
            'max_volume' => 'nullable|numeric|min:0|max:1000|gte:min_volume',
            'duration_months' => 'nullable|integer|min:1|max:60',
            'climate_controlled' => 'nullable|boolean',
            'ground_floor' => 'nullable|boolean',
            'vehicle_access' => 'nullable|boolean',
            'min_price' => 'nullable|numeric|min:0',
            'max_price' => 'nullable|numeric|min:0|gte:min_price',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'site_id.exists' => 'Le site sélectionné n\'existe pas.',
            'min_volume.numeric' => 'Le volume minimum doit être un nombre.',
            'max_volume.gte' => 'Le volume maximum doit être supérieur ou égal au volume minimum.',
            'duration_months.min' => 'La durée doit être d\'au moins 1 mois.',
            'duration_months.max' => 'La durée ne peut pas dépasser 60 mois.',
        ];
    }
}
