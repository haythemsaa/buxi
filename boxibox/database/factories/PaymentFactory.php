<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentFactory extends Factory
{
    protected $model = Payment::class;

    public function definition(): array
    {
        return [
            'payment_number' => 'PAY-' . fake()->unique()->numberBetween(100000, 999999),
            'invoice_id' => Invoice::factory(),
            'contract_id' => Contract::factory(),
            'customer_id' => Customer::factory(),
            'amount' => fake()->randomFloat(2, 50, 500),
            'payment_date' => fake()->dateTimeBetween('-3 months', 'now'),
            'method' => fake()->randomElement(['cash', 'check', 'bank_transfer', 'sepa', 'card']),
            'status' => 'succeeded',
            'transaction_id' => fake()->optional()->uuid(),
            'reference' => fake()->optional()->bothify('REF-####-????'),
            'notes' => fake()->optional()->sentence(),
        ];
    }

    public function succeeded(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'succeeded',
        ]);
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function failed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }

    public function sepa(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'sepa',
        ]);
    }

    public function card(): static
    {
        return $this->state(fn (array $attributes) => [
            'method' => 'card',
            'transaction_id' => fake()->uuid(),
        ]);
    }
}
