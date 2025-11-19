<?php

namespace Tests\Feature\Api;

use App\Models\Box;
use App\Models\Customer;
use App\Models\Reservation;
use App\Models\Site;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReservationTest extends TestCase
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
     * Test customer can search available boxes
     */
    public function test_customer_can_search_available_boxes(): void
    {
        $site = Site::factory()->create();
        Box::factory()->count(5)->create([
            'site_id' => $site->id,
            'status' => 'available',
        ]);

        $response = $this->postJson('/api/v1/boxes/search', [
            'site_id' => $site->id,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'boxes' => [
                    '*' => [
                        'id',
                        'number',
                        'volume',
                        'site',
                        'features',
                        'pricing',
                    ],
                ],
            ]);
    }

    /**
     * Test customer can calculate price
     */
    public function test_customer_can_calculate_price(): void
    {
        $box = Box::factory()->create(['status' => 'available']);

        $response = $this->postJson('/api/v1/boxes/calculate-price', [
            'box_id' => $box->id,
            'duration_months' => 6,
            'insurance' => true,
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'pricing' => [
                    'monthly_price_ht',
                    'total_monthly_ttc',
                    'first_payment',
                ],
            ]);
    }

    /**
     * Test authenticated customer can create reservation
     */
    public function test_authenticated_customer_can_create_reservation(): void
    {
        $box = Box::factory()->create(['status' => 'available']);

        $response = $this->postJson('/api/v1/reservations', [
            'box_id' => $box->id,
            'start_date' => now()->addDays(7)->format('Y-m-d'),
            'duration_months' => 6,
            'insurance' => false,
        ], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'reservation' => [
                    'id',
                    'reservation_number',
                    'status',
                ],
            ]);

        $this->assertDatabaseHas('reservations', [
            'customer_id' => $this->customer->id,
            'box_id' => $box->id,
            'status' => 'pending',
        ]);
    }

    /**
     * Test customer can list their reservations
     */
    public function test_customer_can_list_their_reservations(): void
    {
        Reservation::factory()->count(3)->create([
            'customer_id' => $this->customer->id,
        ]);

        $response = $this->getJson('/api/v1/reservations', [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200)
            ->assertJsonCount(3, 'reservations');
    }

    /**
     * Test customer can cancel their reservation
     */
    public function test_customer_can_cancel_their_reservation(): void
    {
        $reservation = Reservation::factory()->create([
            'customer_id' => $this->customer->id,
            'status' => 'pending',
        ]);

        $response = $this->postJson("/api/v1/reservations/{$reservation->id}/cancel", [], [
            'Authorization' => 'Bearer ' . $this->token,
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('reservations', [
            'id' => $reservation->id,
            'status' => 'cancelled',
        ]);
    }
}
