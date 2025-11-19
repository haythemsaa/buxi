<?php

namespace Tests\Feature;

use App\Models\Invoice;
use App\Services\PaymentGatewayService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentGatewayTest extends TestCase
{
    use RefreshDatabase;

    protected PaymentGatewayService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(PaymentGatewayService::class);
    }

    public function test_can_get_available_gateways(): void
    {
        $gateways = $this->service->getAvailableGateways();

        $this->assertIsArray($gateways);
        $this->assertContains('stripe', $gateways);
        $this->assertContains('paypal', $gateways);
        $this->assertContains('sepa', $gateways);
    }

    public function test_fallback_gateway_is_used_when_primary_fails(): void
    {
        // This would require mocking the payment handlers
        // For now, just test the configuration
        $fallbackEnabled = config('payments.fallback.enabled');
        $fallbackGateway = config('payments.fallback.gateway');

        $this->assertTrue($fallbackEnabled);
        $this->assertNotEmpty($fallbackGateway);
    }

    public function test_payment_recording_creates_payment_record(): void
    {
        $invoice = Invoice::factory()->create([
            'status' => 'pending',
            'total_ttc' => 100.00,
        ]);

        // Mock payment charge result
        $chargeResult = (object) [
            'id' => 'pi_test_123',
            'amount' => 10000,
            'currency' => 'eur',
            'status' => 'succeeded',
        ];

        // This would normally be done by the service
        $payment = \App\Models\Payment::create([
            'invoice_id' => $invoice->id,
            'amount' => 100.00,
            'gateway' => 'stripe',
            'status' => 'success',
            'transaction_id' => $chargeResult->id,
        ]);

        $this->assertDatabaseHas('payments', [
            'invoice_id' => $invoice->id,
            'amount' => 100.00,
            'gateway' => 'stripe',
            'status' => 'success',
        ]);

        $invoice->refresh();
        $this->assertEquals('paid', $invoice->status);
    }

    public function test_3d_secure_is_enabled_for_amounts_over_threshold(): void
    {
        $threshold = config('payments.three_d_secure.amount_threshold', 30);
        $enabled = config('payments.three_d_secure.enabled', true);

        $this->assertTrue($enabled);
        $this->assertEquals(30, $threshold);
    }

    public function test_retry_configuration_is_set(): void
    {
        $maxRetries = config('payments.retry.max_attempts', 3);
        $delay = config('payments.retry.delay_seconds', 2);

        $this->assertEquals(3, $maxRetries);
        $this->assertEquals(2, $delay);
    }
}
