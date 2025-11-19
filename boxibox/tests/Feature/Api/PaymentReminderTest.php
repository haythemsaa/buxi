<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PaymentReminder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentReminderTest extends TestCase
{
    use RefreshDatabase;

    protected Customer $customer;
    protected User $user;
    protected string $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->customer = Customer::factory()->create(['status' => 'active']);
        $this->user = User::factory()->create([
            'userable_type' => Customer::class,
            'userable_id' => $this->customer->id,
        ]);
        $this->token = $this->user->createToken('api-token')->plainTextToken;
    }

    /**
     * Test customer can list their payment reminders
     */
    public function test_customer_can_list_their_payment_reminders(): void
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        PaymentReminder::factory()->count(2)->create([
            'customer_id' => $this->customer->id,
            'invoice_id' => $invoice->id,
        ]);

        $response = $this->getJson('/api/v1/payment-reminders', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(2, 'reminders')
            ->assertJsonStructure([
                'reminders' => [
                    '*' => [
                        'id',
                        'phase',
                        'phase_name',
                        'amount_due',
                        'late_fee',
                        'status',
                    ],
                ],
            ]);
    }

    /**
     * Test customer can view specific payment reminder
     */
    public function test_customer_can_view_specific_payment_reminder(): void
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $reminder = PaymentReminder::factory()->create([
            'customer_id' => $this->customer->id,
            'invoice_id' => $invoice->id,
        ]);

        $response = $this->getJson("/api/v1/payment-reminders/{$reminder->id}", [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'reminder' => [
                    'id',
                    'phase',
                    'days_overdue',
                    'amount_due',
                    'invoice',
                ],
            ]);
    }

    /**
     * Test customer can acknowledge payment reminder
     */
    public function test_customer_can_acknowledge_payment_reminder(): void
    {
        $invoice = Invoice::factory()->create([
            'customer_id' => $this->customer->id,
        ]);

        $reminder = PaymentReminder::factory()->create([
            'customer_id' => $this->customer->id,
            'invoice_id' => $invoice->id,
            'status' => 'sent',
        ]);

        $response = $this->postJson("/api/v1/payment-reminders/{$reminder->id}/acknowledge", [], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('payment_reminders', [
            'id' => $reminder->id,
            'status' => 'acknowledged',
        ]);

        $this->assertNotNull($reminder->fresh()->acknowledged_at);
    }

    /**
     * Test customer cannot view other customer's reminders
     */
    public function test_customer_cannot_view_other_customers_reminders(): void
    {
        $otherCustomer = Customer::factory()->create();
        $invoice = Invoice::factory()->create([
            'customer_id' => $otherCustomer->id,
        ]);

        $reminder = PaymentReminder::factory()->create([
            'customer_id' => $otherCustomer->id,
            'invoice_id' => $invoice->id,
        ]);

        $response = $this->getJson("/api/v1/payment-reminders/{$reminder->id}", [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(404);
    }
}
