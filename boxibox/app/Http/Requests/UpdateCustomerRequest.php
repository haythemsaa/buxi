<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateCustomerRequest extends FormRequest
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
        $customerId = $this->route('customer');

        return [
            'email' => ['sometimes', 'email', Rule::unique('customers')->ignore($customerId)],

            // Individual fields
            'first_name' => ['sometimes', 'string', 'max:100'],
            'last_name' => ['sometimes', 'string', 'max:100'],
            'birth_date' => ['nullable', 'date', 'before:today'],

            // Company fields
            'company_name' => ['sometimes', 'string', 'max:200'],
            'siret' => ['nullable', 'string', 'size:14'],
            'vat_number' => ['nullable', 'string', 'max:20'],

            // Common fields
            'phone' => ['sometimes', 'string', 'max:20'],
            'phone_secondary' => ['nullable', 'string', 'max:20'],
            'address' => ['sometimes', 'string', 'max:255'],
            'address_complement' => ['nullable', 'string', 'max:255'],
            'postal_code' => ['sometimes', 'string', 'max:10'],
            'city' => ['sometimes', 'string', 'max:100'],
            'country' => ['sometimes', 'string', 'max:100'],
            'status' => ['sometimes', 'in:active,inactive,suspended'],

            // Emergency contact
            'emergency_contact_name' => ['nullable', 'string', 'max:200'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:20'],
            'emergency_contact_relation' => ['nullable', 'string', 'max:50'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'first_name' => 'prénom',
            'last_name' => 'nom',
            'birth_date' => 'date de naissance',
            'company_name' => 'nom de l\'entreprise',
            'siret' => 'SIRET',
            'vat_number' => 'numéro de TVA',
            'phone' => 'téléphone',
            'phone_secondary' => 'téléphone secondaire',
            'address' => 'adresse',
            'address_complement' => 'complément d\'adresse',
            'postal_code' => 'code postal',
            'city' => 'ville',
            'country' => 'pays',
            'status' => 'statut',
            'emergency_contact_name' => 'nom du contact d\'urgence',
            'emergency_contact_phone' => 'téléphone du contact d\'urgence',
            'emergency_contact_relation' => 'relation avec le contact',
        ];
    }
}
