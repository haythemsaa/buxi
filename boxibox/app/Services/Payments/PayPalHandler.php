<?php

namespace App\Services\Payments;

use PayPal\Rest\ApiContext;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\Amount;
use PayPal\Api\Transaction;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Payer;

class PayPalHandler implements PaymentHandlerInterface
{
    private ApiContext $apiContext;

    public function __construct()
    {
        $this->apiContext = new ApiContext(
            new OAuthTokenCredential(
                config('payments.gateways.paypal.client_id'),
                config('payments.gateways.paypal.secret')
            )
        );

        $this->apiContext->setConfig([
            'mode' => config('payments.gateways.paypal.mode', 'sandbox'),
        ]);
    }

    /**
     * Charge a payment via PayPal
     */
    public function charge(float $amount, array $details): object
    {
        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $amountObj = new Amount();
        $amountObj->setCurrency(config('payments.gateways.paypal.currency', 'EUR'))
            ->setTotal($amount);

        $transaction = new Transaction();
        $transaction->setAmount($amountObj)
            ->setDescription("Invoice #{$details['invoice_id']}")
            ->setInvoiceNumber($details['invoice_id']);

        $redirectUrls = new RedirectUrls();
        $redirectUrls->setReturnUrl(route('customer.payments.paypal.success'))
            ->setCancelUrl(route('customer.payments.paypal.cancel'));

        $payment = new Payment();
        $payment->setIntent('sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirectUrls)
            ->setTransactions([$transaction]);

        try {
            $payment->create($this->apiContext);
            return $payment;
        } catch (\Exception $e) {
            throw new \Exception("PayPal payment failed: " . $e->getMessage());
        }
    }

    /**
     * Execute PayPal payment after approval
     */
    public function executePayment(string $paymentId, string $payerId): object
    {
        $payment = Payment::get($paymentId, $this->apiContext);

        $execution = new PaymentExecution();
        $execution->setPayerId($payerId);

        try {
            return $payment->execute($execution, $this->apiContext);
        } catch (\Exception $e) {
            throw new \Exception("PayPal payment execution failed: " . $e->getMessage());
        }
    }

    /**
     * Get payment details
     */
    public function getPayment(string $paymentId): object
    {
        return Payment::get($paymentId, $this->apiContext);
    }
}
