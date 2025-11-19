<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TerminateContractRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ensure user owns the contract
        $contract = $this->route('contract');
        return $contract && $contract->customer_id === $this->user()->customer->id;
    }

    public function rules(): array
    {
        return [
            'reason' => ['required', 'in:moving,too_expensive,no_longer_needed,found_alternative,other'],
            'comments' => ['nullable', 'string', 'max:1000'],
            'preferred_end_date' => ['required', 'date', 'after:today'],
        ];
    }

    public function messages(): array
    {
        return [
            'reason.required' => 'Veuillez indiquer la raison de la résiliation.',
            'reason.in' => 'Raison invalide.',
            'preferred_end_date.required' => 'Veuillez indiquer la date de fin souhaitée.',
            'preferred_end_date.after' => 'La date de fin doit être dans le futur.',
        ];
    }
}
