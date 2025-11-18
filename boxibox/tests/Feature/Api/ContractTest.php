<?php

namespace Tests\Feature\Api;

use App\Models\Box;
use App\Models\Contract;
use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ContractTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test customer can get their contracts
     */
    public function test_customer_can_get_their_contracts(): void
    {
        $customer = Customer::factory()->create();
        $box = Box::factory()->create();

        // Create contracts for the customer
        $contracts = Contract::factory()->count(3)->create([
            'customer_id' => $customer->id,
            'box_id' => $box->id,
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/contracts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'contracts' => [
                    '*' => [
                        'id',
                        'contract_number',
                        'start_date',
                        'status',
                    ],
                ],
            ])
            ->assertJsonCount(3, 'contracts');
    }

    /**
     * Test customer can get single contract details
     */
    public function test_customer_can_get_contract_details(): void
    {
        $customer = Customer::factory()->create();
        $box = Box::factory()->create();
        $contract = Contract::factory()->create([
            'customer_id' => $customer->id,
            'box_id' => $box->id,
        ]);

        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/contracts/' . $contract->id);

        $response->assertStatus(200)
            ->assertJson([
                'contract' => [
                    'id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                ],
            ]);
    }

    /**
     * Test customer cannot access other customer's contracts
     */
    public function test_customer_cannot_access_other_customers_contracts(): void
    {
        $customer1 = Customer::factory()->create();
        $customer2 = Customer::factory()->create();
        $box = Box::factory()->create();

        $contract = Contract::factory()->create([
            'customer_id' => $customer2->id,
            'box_id' => $box->id,
        ]);

        $token = $customer1->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/contracts/' . $contract->id);

        $response->assertStatus(404);
    }
}
