<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePricingRuleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('manage_pricing');
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'site_id' => ['nullable', 'exists:sites,id'],

            // Occupancy thresholds
            'occupancy_threshold_min' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'occupancy_threshold_max' => ['nullable', 'numeric', 'min:0', 'max:100', 'gte:occupancy_threshold_min'],

            // Seasonal
            'season' => ['required', 'in:winter,spring,summer,fall,all'],

            // Adjustment
            'adjustment_type' => ['required', 'in:percentage,fixed'],
            'adjustment_value' => ['required', 'numeric'],

            // Duration
            'duration_months_min' => ['nullable', 'integer', 'min:1', 'max:60'],
            'duration_months_max' => ['nullable', 'integer', 'min:1', 'max:60', 'gte:duration_months_min'],

            // Box size filters
            'box_size_min' => ['nullable', 'numeric', 'min:0'],
            'box_size_max' => ['nullable', 'numeric', 'min:0', 'gte:box_size_min'],

            // Priority & status
            'priority' => ['required', 'integer', 'min:0', 'max:100'],
            'is_active' => ['boolean'],

            // Validity dates
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Le nom de la règle est obligatoire.',
            'occupancy_threshold_max.gte' => 'Le seuil max doit être supérieur ou égal au seuil min.',
            'duration_months_max.gte' => 'La durée max doit être supérieure ou égale à la durée min.',
            'box_size_max.gte' => 'La taille max doit être supérieure ou égale à la taille min.',
            'valid_to.after_or_equal' => 'La date de fin doit être après la date de début.',
        ];
    }
}
