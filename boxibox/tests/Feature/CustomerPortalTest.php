<?php

namespace Tests\Feature;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CustomerPortalTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Customer $customer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->customer = Customer::factory()->create(['user_id' => $this->user->id]);
        $this->user->customer_id = $this->customer->id;
        $this->user->save();
    }

    public function test_customer_can_access_dashboard(): void
    {
        $response = $this->actingAs($this->user)
            ->get(route('customer.dashboard'));

        $response->assertStatus(200);
    }

    public function test_non_customer_cannot_access_portal(): void
    {
        $nonCustomerUser = User::factory()->create();

        $response = $this->actingAs($nonCustomerUser)
            ->get(route('customer.dashboard'));

        $response->assertStatus(403);
    }

    public function test_customer_can_view_contracts(): void
    {
        Contract::factory()->count(3)->create(['customer_id' => $this->customer->id]);

        $response = $this->actingAs($this->user)
            ->get(route('customer.contracts.index'));

        $response->assertStatus(200);
    }

    public function test_customer_can_view_invoices(): void
    {
        $contract = Contract::factory()->create(['customer_id' => $this->customer->id]);
        Invoice::factory()->count(5)->create(['contract_id' => $contract->id]);

        $response = $this->actingAs($this->user)
            ->get(route('customer.invoices.index'));

        $response->assertStatus(200);
    }

    public function test_customer_can_view_own_contract_only(): void
    {
        $ownContract = Contract::factory()->create(['customer_id' => $this->customer->id]);
        $otherContract = Contract::factory()->create();

        // Can view own contract
        $response = $this->actingAs($this->user)
            ->get(route('customer.contracts.show', $ownContract->id));
        $response->assertStatus(200);

        // Cannot view other's contract
        $response = $this->actingAs($this->user)
            ->get(route('customer.contracts.show', $otherContract->id));
        $response->assertStatus(403);
    }

    public function test_customer_can_update_profile(): void
    {
        $response = $this->actingAs($this->user)
            ->put(route('customer.profile.update'), [
                'name' => 'Updated Name',
                'email' => 'updated@example.com',
                'phone' => '0123456789',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('users', [
            'id' => $this->user->id,
            'name' => 'Updated Name',
            'email' => 'updated@example.com',
        ]);
    }

    public function test_customer_can_request_contract_termination(): void
    {
        $contract = Contract::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->post(route('customer.contracts.terminate', $contract->id), [
                'reason' => 'moving',
                'comments' => 'Moving to another city',
                'preferred_end_date' => now()->addDays(30)->format('Y-m-d'),
            ]);

        $response->assertRedirect();
    }
}
