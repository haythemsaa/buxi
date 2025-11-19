<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StripeWebhookTest extends TestCase
{
    use RefreshDatabase;

    protected function generateSignature(string $payload, string $secret): string
    {
        $timestamp = time();
        $signedPayload = "{$timestamp}.{$payload}";
        $signature = hash_hmac('sha256', $signedPayload, $secret);

        return "t={$timestamp},v1={$signature}";
    }

    public function test_stripe_webhook_requires_valid_signature(): void
    {
        $payload = json_encode(['type' => 'payment_intent.succeeded']);

        $response = $this->postJson('/webhooks/stripe', json_decode($payload, true), [
            'Stripe-Signature' => 'invalid_signature',
        ]);

        $response->assertStatus(400);
    }

    public function test_payment_intent_succeeded_webhook_updates_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => 'pending',
            'total_ttc' => 100.00,
        ]);

        $payload = json_encode([
            'type' => 'payment_intent.succeeded',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                    'amount' => 10000, // 100.00 EUR in cents
                    'currency' => 'eur',
                    'metadata' => [
                        'invoice_id' => $invoice->id,
                        'customer_id' => $invoice->contract->customer_id,
                    ],
                ],
            ],
        ]);

        $secret = config('payments.gateways.stripe.webhook_secret', 'whsec_test');
        $signature = $this->generateSignature($payload, $secret);

        $response = $this->postJson('/webhooks/stripe', json_decode($payload, true), [
            'Stripe-Signature' => $signature,
        ]);

        $response->assertStatus(200);

        // Verify invoice was updated
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);

        // Verify payment was recorded
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 100.00,
            'gateway' => 'stripe',
            'status' => 'success',
        ]);
    }

    public function test_payment_intent_failed_webhook_marks_payment_as_failed(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => 'pending',
            'total_ttc' => 100.00,
        ]);

        $payload = json_encode([
            'type' => 'payment_intent.payment_failed',
            'data' => [
                'object' => [
                    'id' => 'pi_test_123',
                    'amount' => 10000,
                    'currency' => 'eur',
                    'metadata' => [
                        'invoice_id' => $invoice->id,
                        'customer_id' => $invoice->contract->customer_id,
                    ],
                    'last_payment_error' => [
                        'message' => 'Your card was declined.',
                    ],
                ],
            ],
        ]);

        $secret = config('payments.gateways.stripe.webhook_secret', 'whsec_test');
        $signature = $this->generateSignature($payload, $secret);

        $response = $this->postJson('/webhooks/stripe', json_decode($payload, true), [
            'Stripe-Signature' => $signature,
        ]);

        $response->assertStatus(200);

        // Verify payment was recorded as failed
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'gateway' => 'stripe',
            'status' => 'failed',
        ]);

        // Invoice should still be pending
        $invoice->refresh();
        $this->assertEquals('pending', $invoice->status);
    }

    public function test_charge_refunded_webhook_updates_payment_and_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => 'paid',
            'total_ttc' => 100.00,
        ]);

        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 100.00,
            'gateway' => 'stripe',
            'status' => 'success',
            'transaction_id' => 'ch_test_123',
        ]);

        $payload = json_encode([
            'type' => 'charge.refunded',
            'data' => [
                'object' => [
                    'id' => 'ch_test_123',
                    'amount' => 10000,
                    'amount_refunded' => 10000,
                    'refunded' => true,
                    'metadata' => [
                        'invoice_id' => $invoice->id,
                    ],
                ],
            ],
        ]);

        $secret = config('payments.gateways.stripe.webhook_secret', 'whsec_test');
        $signature = $this->generateSignature($payload, $secret);

        $response = $this->postJson('/webhooks/stripe', json_decode($payload, true), [
            'Stripe-Signature' => $signature,
        ]);

        $response->assertStatus(200);

        // Verify payment was updated
        $payment->refresh();
        $this->assertEquals('refunded', $payment->status);

        // Verify invoice was updated
        $invoice->refresh();
        $this->assertEquals('refunded', $invoice->status);
    }
}
