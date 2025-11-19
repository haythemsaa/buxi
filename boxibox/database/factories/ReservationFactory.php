<?php

namespace Database\Factories;

use App\Models\Box;
use App\Models\Customer;
use App\Models\Promotion;
use App\Models\Reservation;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class ReservationFactory extends Factory
{
    protected $model = Reservation::class;

    public function definition(): array
    {
        $startDate = fake()->dateTimeBetween('+1 week', '+2 months');
        $expiresAt = (clone $startDate)->modify('-1 day');
        $durationMonths = fake()->randomElement([1, 3, 6, 12, 24]);

        $monthlyPriceHt = fake()->randomFloat(2, 50, 200);
        $taxRate = 20.00;
        $insuranceMonthly = fake()->boolean(70) ? fake()->randomFloat(2, 10, 30) : 0;

        $subtotal = $monthlyPriceHt + $insuranceMonthly;
        $taxAmount = round($subtotal * ($taxRate / 100), 2);
        $totalMonthlyTtc = $subtotal + $taxAmount;
        $depositAmount = $monthlyPriceHt;
        $firstPayment = $totalMonthlyTtc + $depositAmount;

        return [
            'reservation_number' => 'RES-' . strtoupper(fake()->unique()->bothify('???###')),
            'customer_id' => Customer::factory(),
            'box_id' => Box::factory(),
            'site_id' => Site::factory(),
            'promotion_id' => fake()->optional(0.3)->randomElement([null, Promotion::factory()]),
            'start_date' => $startDate,
            'duration_months' => $durationMonths,
            'monthly_price_ht' => $monthlyPriceHt,
            'tax_rate' => $taxRate,
            'insurance_monthly' => $insuranceMonthly,
            'total_monthly_ttc' => $totalMonthlyTtc,
            'deposit_amount' => $depositAmount,
            'first_payment' => $firstPayment,
            'discount_applied' => 0,
            'promo_code_used' => null,
            'status' => fake()->randomElement(['pending', 'confirmed', 'cancelled', 'expired', 'converted']),
            'expires_at' => $expiresAt,
            'confirmed_at' => fake()->optional(0.6)->dateTimeBetween('now', $expiresAt),
            'cancelled_at' => null,
            'converted_to_contract_at' => null,
            'is_guest' => fake()->boolean(20),
            'guest_email' => null,
            'guest_phone' => null,
            'guest_first_name' => null,
            'guest_last_name' => null,
            'notes' => fake()->optional()->sentence(),
            'internal_notes' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'confirmed_at' => null,
        ]);
    }

    public function confirmed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'confirmed',
            'confirmed_at' => fake()->dateTimeBetween('-7 days', 'now'),
        ]);
    }

    public function guest(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_guest' => true,
            'guest_email' => fake()->safeEmail(),
            'guest_phone' => fake()->phoneNumber(),
            'guest_first_name' => fake()->firstName(),
            'guest_last_name' => fake()->lastName(),
            'customer_id' => null,
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'expires_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
        ]);
    }

    public function converted(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'converted',
            'converted_to_contract_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }
}
