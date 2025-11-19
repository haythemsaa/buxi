<?php

namespace Database\Factories;

use App\Models\PricingRule;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class PricingRuleFactory extends Factory
{
    protected $model = PricingRule::class;

    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'name' => fake()->words(3, true),
            'description' => fake()->sentence(),
            'box_size_min' => null,
            'box_size_max' => null,
            'occupancy_threshold_min' => null,
            'occupancy_threshold_max' => null,
            'season' => fake()->randomElement(['winter', 'spring', 'summer', 'fall', 'all']),
            'duration_months_min' => null,
            'duration_months_max' => null,
            'adjustment_type' => fake()->randomElement(['percentage', 'fixed']),
            'adjustment_value' => fake()->numberBetween(-20, 30),
            'priority' => fake()->numberBetween(1, 100),
            'is_active' => true,
            'valid_from' => null,
            'valid_until' => null,
        ];
    }

    /**
     * Indicate that the rule is inactive
     */
    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    /**
     * Indicate that the rule is for high occupancy
     */
    public function highOccupancy(): static
    {
        return $this->state(fn (array $attributes) => [
            'occupancy_threshold_min' => 85,
            'occupancy_threshold_max' => 100,
            'adjustment_type' => 'percentage',
            'adjustment_value' => 20,
        ]);
    }

    /**
     * Indicate that the rule is for low occupancy
     */
    public function lowOccupancy(): static
    {
        return $this->state(fn (array $attributes) => [
            'occupancy_threshold_min' => 0,
            'occupancy_threshold_max' => 70,
            'adjustment_type' => 'percentage',
            'adjustment_value' => -10,
        ]);
    }
}
