<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PayInvoiceRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ensure user owns the invoice
        $invoice = $this->route('invoice');
        return $invoice && $invoice->contract->customer_id === $this->user()->customer->id;
    }

    public function rules(): array
    {
        return [
            'gateway' => ['required', 'in:stripe,paypal,sepa'],
            'payment_method_id' => ['required_if:gateway,stripe', 'string'],
            'save_payment_method' => ['boolean'],
        ];
    }

    public function messages(): array
    {
        return [
            'gateway.required' => 'Veuillez sélectionner un mode de paiement.',
            'gateway.in' => 'Mode de paiement invalide.',
            'payment_method_id.required_if' => 'Méthode de paiement requise pour Stripe.',
        ];
    }
}
