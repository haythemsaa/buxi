<?php

namespace App\Services\Payments;

class SepaHandler implements PaymentHandlerInterface
{
    /**
     * Process SEPA payment (existing implementation)
     */
    public function charge(float $amount, array $details): object
    {
        // This would integrate with existing SEPA system
        // For now, return a mock object that matches the interface
        return (object) [
            'id' => 'sepa_' . uniqid(),
            'amount' => $amount,
            'status' => 'pending',
            'payment_method' => 'sepa_debit',
            'created_at' => now(),
        ];
    }

    /**
     * Create SEPA mandate
     */
    public function createMandate(array $customerData): object
    {
        return (object) [
            'mandate_id' => 'MNDT_' . uniqid(),
            'iban' => $customerData['iban'],
            'status' => 'active',
        ];
    }
}
