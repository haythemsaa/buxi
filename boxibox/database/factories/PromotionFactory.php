<?php

namespace Database\Factories;

use App\Models\Promotion;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class PromotionFactory extends Factory
{
    protected $model = Promotion::class;

    public function definition(): array
    {
        $discountType = fake()->randomElement(['percentage', 'fixed_amount', 'first_month_free', 'x_months_free']);
        $discountValue = match($discountType) {
            'percentage' => fake()->randomElement([10, 15, 20, 25, 30]),
            'fixed_amount' => fake()->randomElement([20, 30, 50, 100]),
            'first_month_free' => 1,
            'x_months_free' => fake()->randomElement([1, 2, 3]),
        };

        return [
            'code' => strtoupper(fake()->unique()->bothify('????###')),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'discount_type' => $discountType,
            'discount_value' => $discountValue,
            'valid_from' => fake()->dateTimeBetween('-1 month', 'now'),
            'valid_until' => fake()->dateTimeBetween('+1 month', '+6 months'),
            'max_uses' => fake()->optional(0.5)->numberBetween(10, 1000),
            'max_uses_per_customer' => fake()->optional(0.7)->numberBetween(1, 5),
            'current_uses' => 0,
            'min_duration_months' => fake()->optional(0.6)->randomElement([3, 6, 12]),
            'applicable_box_types' => null,
            'applicable_sites' => null,
            'new_customers_only' => fake()->boolean(30),
            'online_only' => fake()->boolean(40),
            'stackable' => fake()->boolean(20),
            'auto_apply' => fake()->boolean(15),
            'priority' => fake()->numberBetween(1, 10),
            'status' => 'active',
            'terms_conditions' => fake()->optional()->paragraph(),
        ];
    }

    public function percentage(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'percentage',
            'discount_value' => fake()->randomElement([10, 15, 20, 25, 30]),
        ]);
    }

    public function fixedAmount(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'fixed_amount',
            'discount_value' => fake()->randomElement([20, 30, 50, 100]),
        ]);
    }

    public function firstMonthFree(): static
    {
        return $this->state(fn (array $attributes) => [
            'discount_type' => 'first_month_free',
            'discount_value' => 1,
        ]);
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'valid_from' => now()->subDays(7),
            'valid_until' => now()->addMonths(3),
        ]);
    }

    public function expired(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'expired',
            'valid_until' => now()->subDays(1),
        ]);
    }
}
