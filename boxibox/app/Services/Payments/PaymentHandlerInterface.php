<?php

namespace App\Services\Payments;

interface PaymentHandlerInterface
{
    /**
     * Charge a payment
     *
     * @param float $amount Amount to charge
     * @param array $details Payment details (payment_method_id, customer_id, etc.)
     * @return object Payment result object
     */
    public function charge(float $amount, array $details): object;
}
