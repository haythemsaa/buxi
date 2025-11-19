<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSiteRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:200'],
            'code' => ['required', 'string', 'max:50', 'unique:sites,code', 'regex:/^[A-Z0-9_]+$/'],
            'address' => ['required', 'string', 'max:255'],
            'postal_code' => ['required', 'string', 'max:10'],
            'city' => ['required', 'string', 'max:100'],
            'country' => ['required', 'string', 'max:100'],
            'phone' => ['required', 'string', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'gps_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'gps_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            'opening_hours' => ['nullable', 'json'],
            'access_instructions' => ['nullable', 'string'],
            'amenities' => ['nullable', 'array'],
            'status' => ['required', 'in:active,inactive,maintenance'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'name' => 'nom',
            'code' => 'code',
            'address' => 'adresse',
            'postal_code' => 'code postal',
            'city' => 'ville',
            'country' => 'pays',
            'phone' => 'téléphone',
            'email' => 'email',
            'gps_latitude' => 'latitude GPS',
            'gps_longitude' => 'longitude GPS',
            'opening_hours' => 'horaires d\'ouverture',
            'access_instructions' => 'instructions d\'accès',
            'amenities' => 'équipements',
            'status' => 'statut',
        ];
    }
}
