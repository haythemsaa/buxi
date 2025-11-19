<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Stripe\Webhook;
use Illuminate\Support\Facades\Log;
use App\Models\Payment;
use App\Models\Invoice;

class WebhookController extends Controller
{
    /**
     * Handle Stripe webhook
     */
    public function stripe(Request $request)
    {
        $payload = $request->getContent();
        $sig_header = $request->header('Stripe-Signature');
        $endpoint_secret = config('payments.gateways.stripe.webhook_secret');

        try {
            $event = Webhook::constructEvent($payload, $sig_header, $endpoint_secret);
        } catch (\UnexpectedValueException $e) {
            Log::error('Invalid Stripe webhook payload', ['error' => $e->getMessage()]);
            return response('Invalid payload', 400);
        } catch (\Stripe\Exception\SignatureVerificationException $e) {
            Log::error('Invalid Stripe webhook signature', ['error' => $e->getMessage()]);
            return response('Invalid signature', 400);
        }

        // Handle the event
        switch ($event->type) {
            case 'payment_intent.succeeded':
                $this->handlePaymentIntentSucceeded($event->data->object);
                break;

            case 'payment_intent.payment_failed':
                $this->handlePaymentIntentFailed($event->data->object);
                break;

            case 'charge.refunded':
                $this->handleChargeRefunded($event->data->object);
                break;

            case 'customer.subscription.deleted':
                $this->handleSubscriptionDeleted($event->data->object);
                break;

            default:
                Log::info('Unhandled Stripe event type', ['type' => $event->type]);
        }

        return response('Webhook received', 200);
    }

    /**
     * Handle PayPal webhook
     */
    public function paypal(Request $request)
    {
        Log::info('PayPal webhook received', $request->all());

        $eventType = $request->input('event_type');

        switch ($eventType) {
            case 'PAYMENT.SALE.COMPLETED':
                $this->handlePayPalSaleCompleted($request->all());
                break;

            case 'PAYMENT.SALE.REFUNDED':
                $this->handlePayPalSaleRefunded($request->all());
                break;

            default:
                Log::info('Unhandled PayPal event type', ['type' => $eventType]);
        }

        return response('Webhook received', 200);
    }

    /**
     * Handle successful payment intent
     */
    private function handlePaymentIntentSucceeded($paymentIntent)
    {
        Log::info('PaymentIntent succeeded', ['id' => $paymentIntent->id]);

        $invoiceId = $paymentIntent->metadata->invoice_id ?? null;

        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice && $invoice->status !== 'paid') {
                $invoice->update(['status' => 'paid']);

                Log::info('Invoice marked as paid via webhook', [
                    'invoice_id' => $invoiceId,
                    'payment_intent' => $paymentIntent->id,
                ]);
            }
        }
    }

    /**
     * Handle failed payment intent
     */
    private function handlePaymentIntentFailed($paymentIntent)
    {
        Log::warning('PaymentIntent failed', [
            'id' => $paymentIntent->id,
            'last_payment_error' => $paymentIntent->last_payment_error,
        ]);

        $invoiceId = $paymentIntent->metadata->invoice_id ?? null;

        if ($invoiceId) {
            $invoice = Invoice::find($invoiceId);
            if ($invoice) {
                // Potentially send notification to customer about failed payment
                Log::info('Payment failed for invoice', ['invoice_id' => $invoiceId]);
            }
        }
    }

    /**
     * Handle refunded charge
     */
    private function handleChargeRefunded($charge)
    {
        Log::info('Charge refunded', [
            'id' => $charge->id,
            'amount' => $charge->amount_refunded,
        ]);

        // Update payment status to refunded
        $payment = Payment::where('payment_reference', $charge->payment_intent)->first();
        if ($payment) {
            $payment->update(['status' => 'refunded']);
        }
    }

    /**
     * Handle deleted subscription
     */
    private function handleSubscriptionDeleted($subscription)
    {
        Log::info('Subscription deleted', ['id' => $subscription->id]);

        // Handle subscription cancellation logic
    }

    /**
     * Handle PayPal sale completed
     */
    private function handlePayPalSaleCompleted($data)
    {
        Log::info('PayPal sale completed', $data);

        $saleId = $data['resource']['id'] ?? null;
        $invoiceNumber = $data['resource']['invoice_number'] ?? null;

        if ($invoiceNumber) {
            $invoice = Invoice::find($invoiceNumber);
            if ($invoice && $invoice->status !== 'paid') {
                $invoice->update(['status' => 'paid']);

                Log::info('Invoice marked as paid via PayPal webhook', [
                    'invoice_id' => $invoiceNumber,
                    'sale_id' => $saleId,
                ]);
            }
        }
    }

    /**
     * Handle PayPal sale refunded
     */
    private function handlePayPalSaleRefunded($data)
    {
        Log::info('PayPal sale refunded', $data);

        // Handle refund logic
    }
}
