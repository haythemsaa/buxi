<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test customer can login with valid credentials
     */
    public function test_customer_can_login_with_valid_credentials(): void
    {
        // Create a customer with password
        $customer = Customer::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Attempt to login
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'customer' => [
                    'id',
                    'first_name',
                    'last_name',
                    'email',
                ],
            ]);
    }

    /**
     * Test customer cannot login with invalid credentials
     */
    public function test_customer_cannot_login_with_invalid_credentials(): void
    {
        // Create a customer
        $customer = Customer::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password123'),
        ]);

        // Attempt to login with wrong password
        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /**
     * Test authenticated customer can get their profile
     */
    public function test_authenticated_customer_can_get_profile(): void
    {
        $customer = Customer::factory()->create();
        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->getJson('/api/v1/me');

        $response->assertStatus(200)
            ->assertJson([
                'customer' => [
                    'id' => $customer->id,
                    'email' => $customer->email,
                ],
            ]);
    }

    /**
     * Test customer can logout
     */
    public function test_customer_can_logout(): void
    {
        $customer = Customer::factory()->create();
        $token = $customer->createToken('test-token')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/logout');

        $response->assertStatus(200)
            ->assertJson(['message' => 'Déconnexion réussie']);
    }

    /**
     * Test unauthenticated requests are rejected
     */
    public function test_unauthenticated_requests_are_rejected(): void
    {
        $response = $this->getJson('/api/v1/me');

        $response->assertStatus(401);
    }
}
