<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\PaymentReminder;
use Illuminate\Database\Eloquent\Factories\Factory;

class PaymentReminderFactory extends Factory
{
    protected $model = PaymentReminder::class;

    public function definition(): array
    {
        $phase = fake()->randomElement(['phase_1', 'phase_2', 'phase_3']);
        $daysOverdue = match($phase) {
            'phase_1' => fake()->numberBetween(7, 14),
            'phase_2' => fake()->numberBetween(15, 29),
            'phase_3' => fake()->numberBetween(30, 90),
        };

        $amountDue = fake()->randomFloat(2, 50, 500);
        $lateFeePercentage = PaymentReminder::PHASES_CONFIG[$phase]['late_fee_percentage'] ?? 0;
        $lateFee = round($amountDue * ($lateFeePercentage / 100), 2);

        return [
            'invoice_id' => Invoice::factory(),
            'customer_id' => Customer::factory(),
            'contract_id' => Contract::factory(),
            'phase' => $phase,
            'days_overdue' => $daysOverdue,
            'amount_due' => $amountDue,
            'late_fee' => $lateFee,
            'status' => fake()->randomElement(['pending', 'sent', 'acknowledged', 'paid']),
            'sent_at' => fake()->optional(0.7)->dateTimeBetween('-30 days', 'now'),
            'acknowledged_at' => fake()->optional(0.3)->dateTimeBetween('-20 days', 'now'),
            'paid_at' => null,
            'sent_via' => ['email'],
            'message' => fake()->paragraph(),
            'metadata' => [
                'invoice_number' => 'INV-' . fake()->numberBetween(100000, 999999),
                'due_date' => fake()->dateTimeBetween('-60 days', '-7 days')->format('Y-m-d'),
            ],
        ];
    }

    public function phase1(): static
    {
        return $this->state(fn (array $attributes) => [
            'phase' => 'phase_1',
            'days_overdue' => fake()->numberBetween(7, 14),
            'late_fee' => 0,
        ]);
    }

    public function phase2(): static
    {
        return $this->state(function (array $attributes) {
            $lateFee = round($attributes['amount_due'] * 0.05, 2);
            return [
                'phase' => 'phase_2',
                'days_overdue' => fake()->numberBetween(15, 29),
                'late_fee' => $lateFee,
            ];
        });
    }

    public function phase3(): static
    {
        return $this->state(function (array $attributes) {
            $lateFee = round($attributes['amount_due'] * 0.10, 2);
            return [
                'phase' => 'phase_3',
                'days_overdue' => fake()->numberBetween(30, 90),
                'late_fee' => $lateFee,
            ];
        });
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'sent_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ]);
    }

    public function acknowledged(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'acknowledged',
            'sent_at' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'acknowledged_at' => fake()->dateTimeBetween($attributes['sent_at'], 'now'),
        ]);
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => fake()->dateTimeBetween('-10 days', 'now'),
        ]);
    }
}
