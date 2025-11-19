<?php

namespace Tests\Feature\Api;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test customer can login with valid credentials
     */
    public function test_customer_can_login_with_valid_credentials(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'customer@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        User::factory()->create([
            'email' => 'customer@example.com',
            'password' => bcrypt('password123'),
            'userable_type' => Customer::class,
            'userable_id' => $customer->id,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'customer@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'token',
                'customer' => [
                    'id',
                    'customer_number',
                    'type',
                    'email',
                ],
            ]);
    }

    /**
     * Test login fails with invalid credentials
     */
    public function test_login_fails_with_invalid_credentials(): void
    {
        $response = $this->postJson('/api/v1/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrongpassword',
        ]);

        $response->assertStatus(401);
    }

    /**
     * Test login fails for inactive customer
     */
    public function test_login_fails_for_inactive_customer(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password123'),
            'status' => 'inactive',
        ]);

        User::factory()->create([
            'email' => 'inactive@example.com',
            'password' => bcrypt('password123'),
            'userable_type' => Customer::class,
            'userable_id' => $customer->id,
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'inactive@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(403);
    }

    /**
     * Test customer can logout
     */
    public function test_customer_can_logout(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'customer@example.com',
            'password' => bcrypt('password123'),
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'password' => bcrypt('password123'),
            'userable_type' => Customer::class,
            'userable_id' => $customer->id,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->postJson('/api/v1/logout', [], [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200);
    }

    /**
     * Test authenticated user can get their profile
     */
    public function test_authenticated_user_can_get_profile(): void
    {
        $customer = Customer::factory()->create([
            'email' => 'customer@example.com',
            'status' => 'active',
        ]);

        $user = User::factory()->create([
            'email' => 'customer@example.com',
            'userable_type' => Customer::class,
            'userable_id' => $customer->id,
        ]);

        $token = $user->createToken('api-token')->plainTextToken;

        $response = $this->getJson('/api/v1/me', [
            'Authorization' => 'Bearer ' . $token,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'customer' => [
                    'id',
                    'customer_number',
                    'email',
                ],
            ]);
    }
}
