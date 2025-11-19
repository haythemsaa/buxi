<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PayPalWebhookTest extends TestCase
{
    use RefreshDatabase;

    public function test_payment_sale_completed_webhook_updates_invoice(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => 'pending',
            'total_ttc' => 100.00,
        ]);

        $payload = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'id' => 'PAYID-TEST123',
                'amount' => [
                    'total' => '100.00',
                    'currency' => 'EUR',
                ],
                'state' => 'completed',
                'custom' => json_encode([
                    'invoice_id' => $invoice->id,
                    'customer_id' => $invoice->contract->customer_id,
                ]),
            ],
        ];

        $response = $this->postJson('/webhooks/paypal', $payload);

        $response->assertStatus(200);

        // Verify invoice was updated
        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);

        // Verify payment was recorded
        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 100.00,
            'gateway' => 'paypal',
            'status' => 'success',
            'transaction_id' => 'PAYID-TEST123',
        ]);
    }

    public function test_payment_sale_refunded_webhook_updates_payment(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => 'paid',
            'total_ttc' => 100.00,
        ]);

        $payment = Payment::factory()->create([
            'invoice_id' => $invoice->id,
            'amount' => 100.00,
            'gateway' => 'paypal',
            'status' => 'success',
            'transaction_id' => 'PAYID-TEST123',
        ]);

        $payload = [
            'event_type' => 'PAYMENT.SALE.REFUNDED',
            'resource' => [
                'id' => 'REFUND-TEST123',
                'sale_id' => 'PAYID-TEST123',
                'amount' => [
                    'total' => '100.00',
                    'currency' => 'EUR',
                ],
                'state' => 'completed',
            ],
        ];

        $response = $this->postJson('/webhooks/paypal', $payload);

        $response->assertStatus(200);

        // Verify payment was updated
        $payment->refresh();
        $this->assertEquals('refunded', $payment->status);

        // Verify invoice was updated
        $invoice->refresh();
        $this->assertEquals('refunded', $invoice->status);
    }

    public function test_webhook_ignores_unknown_event_types(): void
    {
        $payload = [
            'event_type' => 'UNKNOWN.EVENT.TYPE',
            'resource' => [],
        ];

        $response = $this->postJson('/webhooks/paypal', $payload);

        $response->assertStatus(200);
        $response->assertJson(['message' => 'Webhook received']);
    }

    public function test_webhook_handles_missing_invoice_gracefully(): void
    {
        $payload = [
            'event_type' => 'PAYMENT.SALE.COMPLETED',
            'resource' => [
                'id' => 'PAYID-TEST123',
                'amount' => [
                    'total' => '100.00',
                    'currency' => 'EUR',
                ],
                'state' => 'completed',
                'custom' => json_encode([
                    'invoice_id' => 999999, // Non-existent
                    'customer_id' => 1,
                ]),
            ],
        ];

        $response = $this->postJson('/webhooks/paypal', $payload);

        // Should not crash, but log error
        $response->assertStatus(200);
    }
}
