<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\LoyaltyPoint;
use Illuminate\Database\Eloquent\Factories\Factory;

class LoyaltyPointFactory extends Factory
{
    protected $model = LoyaltyPoint::class;

    public function definition(): array
    {
        $pointsEarned = fake()->numberBetween(0, 5000);
        $pointsSpent = fake()->numberBetween(0, min(2000, $pointsEarned));
        $points = $pointsEarned - $pointsSpent;

        $tier = match(true) {
            $points >= 10000 => 'platinum',
            $points >= 5000 => 'gold',
            $points >= 1000 => 'silver',
            default => 'bronze',
        };

        return [
            'customer_id' => Customer::factory(),
            'points' => $points,
            'points_earned' => $pointsEarned,
            'points_spent' => $pointsSpent,
            'tier' => $tier,
        ];
    }

    public function bronze(): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => fake()->numberBetween(0, 999),
            'tier' => 'bronze',
        ]);
    }

    public function silver(): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => fake()->numberBetween(1000, 4999),
            'tier' => 'silver',
        ]);
    }

    public function gold(): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => fake()->numberBetween(5000, 9999),
            'tier' => 'gold',
        ]);
    }

    public function platinum(): static
    {
        return $this->state(fn (array $attributes) => [
            'points' => fake()->numberBetween(10000, 50000),
            'tier' => 'platinum',
        ]);
    }
}
