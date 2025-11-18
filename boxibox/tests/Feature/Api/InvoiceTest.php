<?php

namespace Tests\Feature\Api;

use App\Models\Box;
use App\Models\Contract;
use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InvoiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test customer can get their invoices
     */
    public function test_customer_can_get_their_invoices(): void
    {
        $customer = Customer::factory()->create();
        $box = Box::factory()->create();
        $contract = Contract::factory()->create([
            'customer_id' => $customer->id,
            'box_id' => $box->id,
        ]);

        // Create invoices for the contract
        $invoices = Invoice::factory()->count(3)->create([
            'contract_id' => $contract->id,
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/invoices');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'invoices' => [
                    '*' => [
                        'id',
                        'invoice_number',
                        'status',
                        'total_ttc',
                    ],
                ],
            ]);
    }

    /**
     * Test customer can get single invoice details
     */
    public function test_customer_can_get_invoice_details(): void
    {
        $customer = Customer::factory()->create();
        $box = Box::factory()->create();
        $contract = Contract::factory()->create([
            'customer_id' => $customer->id,
            'box_id' => $box->id,
        ]);
        $invoice = Invoice::factory()->create([
            'contract_id' => $contract->id,
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/invoices/' . $invoice->id);

        $response->assertStatus(200)
            ->assertJson([
                'invoice' => [
                    'id' => $invoice->id,
                    'invoice_number' => $invoice->invoice_number,
                ],
            ]);
    }

    /**
     * Test customer cannot access other customer's invoices
     */
    public function test_customer_cannot_access_other_customers_invoices(): void
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        $box = Box::factory()->create();
        $contract = Contract::factory()->create([
            'customer_id' => $customer2->id,
            'box_id' => $box->id,
        ]);
        $invoice = Invoice::factory()->create([
            'contract_id' => $contract->id,
        ]);

        $token = $customer1->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/invoices/' . $invoice->id);

        $response->assertStatus(404);
    }

    /**
     * Test customer can download invoice PDF
     */
    public function test_customer_can_download_invoice_pdf(): void
    {
        $customer = Customer::factory()->create();
        $box = Box::factory()->create();
        $contract = Contract::factory()->create([
            'customer_id' => $customer->id,
            'box_id' => $box->id,
        ]);
        $invoice = Invoice::factory()->create([
            'contract_id' => $contract->id,
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->get('/api/v1/invoices/' . $invoice->id . '/download');

        $response->assertStatus(200)
            ->assertHeader('Content-Type', 'application/pdf');
    }
}
