<?php

namespace App\Services\Payments;

use Stripe\StripeClient;
use Stripe\Exception\ApiErrorException;

class StripeHandler implements PaymentHandlerInterface
{
    private StripeClient $stripe;

    public function __construct()
    {
        $this->stripe = new StripeClient(config('payments.gateways.stripe.secret'));
    }

    /**
     * Charge a payment via Stripe
     */
    public function charge(float $amount, array $details): object
    {
        try {
            return $this->stripe->paymentIntents->create([
                'amount' => (int)($amount * 100), // Convert to cents
                'currency' => config('payments.gateways.stripe.currency', 'eur'),
                'payment_method' => $details['payment_method_id'] ?? null,
                'confirmation_method' => 'automatic',
                'confirm' => true,
                'metadata' => [
                    'invoice_id' => $details['invoice_id'] ?? null,
                    'customer_id' => $details['customer_id'] ?? null,
                ],
                'return_url' => route('customer.payments.index'),
            ]);
        } catch (ApiErrorException $e) {
            throw new \Exception("Stripe payment failed: " . $e->getMessage());
        }
    }

    /**
     * Create a setup intent for saving payment methods
     */
    public function setupIntent(string $customerId): string
    {
        try {
            $intent = $this->stripe->setupIntents->create([
                'customer' => $customerId,
                'payment_method_types' => ['card', 'sepa_debit'],
            ]);

            return $intent->client_secret;
        } catch (ApiErrorException $e) {
            throw new \Exception("Failed to create setup intent: " . $e->getMessage());
        }
    }

    /**
     * Create a Stripe customer
     */
    public function createCustomer(array $customerData): object
    {
        try {
            return $this->stripe->customers->create([
                'email' => $customerData['email'],
                'name' => $customerData['name'] ?? null,
                'phone' => $customerData['phone'] ?? null,
                'metadata' => [
                    'customer_id' => $customerData['id'] ?? null,
                ],
            ]);
        } catch (ApiErrorException $e) {
            throw new \Exception("Failed to create Stripe customer: " . $e->getMessage());
        }
    }

    /**
     * Retrieve payment method
     */
    public function getPaymentMethod(string $paymentMethodId): object
    {
        return $this->stripe->paymentMethods->retrieve($paymentMethodId);
    }

    /**
     * Attach payment method to customer
     */
    public function attachPaymentMethod(string $paymentMethodId, string $customerId): object
    {
        return $this->stripe->paymentMethods->attach($paymentMethodId, [
            'customer' => $customerId,
        ]);
    }
}
