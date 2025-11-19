<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\Payments\StripeHandler;
use App\Services\Payments\PayPalHandler;
use App\Services\Payments\SepaHandler;
use Illuminate\Support\Facades\Log;

class PaymentGatewayService
{
    /**
     * Charge a payment
     */
    public function charge(
        Invoice $invoice,
        string $gateway,
        array $paymentDetails
    ): Payment {
        $handler = $this->getHandler($gateway);

        try {
            $charge = $handler->charge($invoice->total_ttc, array_merge($paymentDetails, [
                'invoice_id' => $invoice->id,
                'customer_id' => $invoice->contract->customer_id,
            ]));

            Log::info("Payment successful", [
                'gateway' => $gateway,
                'invoice_id' => $invoice->id,
                'amount' => $invoice->total_ttc,
            ]);

            return $this->recordPayment($invoice, $charge, $gateway);
        } catch (\Exception $e) {
            Log::error("Payment failed for invoice {$invoice->id}", [
                'gateway' => $gateway,
                'error' => $e->getMessage(),
            ]);

            // Try fallback if enabled
            if (config('payments.fallback.enabled') && $gateway !== config('payments.fallback.gateway')) {
                Log::info("Trying fallback gateway: " . config('payments.fallback.gateway'));
                return $this->charge($invoice, config('payments.fallback.gateway'), $paymentDetails);
            }

            throw $e;
        }
    }

    /**
     * Get payment handler
     */
    private function getHandler(string $gateway): object
    {
        return match($gateway) {
            'stripe' => new StripeHandler(),
            'paypal' => new PayPalHandler(),
            'sepa' => new SepaHandler(),
            default => throw new \Exception("Unknown payment gateway: {$gateway}"),
        };
    }

    /**
     * Record payment in database
     */
    private function recordPayment(Invoice $invoice, object $charge, string $gateway): Payment
    {
        $payment = Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => $invoice->total_ttc,
            'payment_date' => now(),
            'payment_method' => $gateway,
            'payment_reference' => $charge->id ?? ($charge->getId() ?? uniqid()),
            'status' => 'completed',
        ]);

        // Update invoice status
        $invoice->update(['status' => 'paid']);

        Log::info("Payment recorded", [
            'payment_id' => $payment->id,
            'invoice_id' => $invoice->id,
        ]);

        return $payment;
    }

    /**
     * Get available payment methods
     */
    public function getAvailablePaymentMethods(): array
    {
        $methods = [];

        if (config('payments.methods.card')) {
            $methods[] = [
                'id' => 'stripe',
                'name' => 'Carte Bancaire',
                'icon' => '💳',
                'description' => 'Visa, Mastercard, American Express',
            ];
        }

        if (config('payments.methods.paypal')) {
            $methods[] = [
                'id' => 'paypal',
                'name' => 'PayPal',
                'icon' => '🅿️',
                'description' => 'Compte PayPal ou Carte via PayPal',
            ];
        }

        if (config('payments.methods.sepa')) {
            $methods[] = [
                'id' => 'sepa',
                'name' => 'Prélèvement SEPA',
                'icon' => '🏦',
                'description' => 'Virement bancaire européen',
            ];
        }

        return $methods;
    }
}
