<?php

namespace Database\Factories;

use App\Models\Contract;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Site;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $issueDate = fake()->dateTimeBetween('-6 months', 'now');
        $dueDate = (clone $issueDate)->modify('+15 days');

        $subtotalHt = fake()->randomFloat(2, 50, 300);
        $taxRate = 20.00;
        $taxAmount = round($subtotalHt * ($taxRate / 100), 2);
        $totalTtc = $subtotalHt + $taxAmount;

        return [
            'invoice_number' => 'INV-' . fake()->unique()->numberBetween(100000, 999999),
            'contract_id' => Contract::factory(),
            'customer_id' => Customer::factory(),
            'site_id' => Site::factory(),
            'type' => fake()->randomElement(['rent', 'deposit', 'insurance', 'late_fee', 'other']),
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'period_start' => (clone $issueDate)->modify('-1 month'),
            'period_end' => $issueDate,
            'subtotal_ht' => $subtotalHt,
            'tax_amount' => $taxAmount,
            'tax_rate' => $taxRate,
            'total_ttc' => $totalTtc,
            'discount_amount' => 0,
            'discount_reason' => null,
            'line_items' => [
                [
                    'description' => 'Location box - ' . fake()->monthName(),
                    'quantity' => 1,
                    'unit_price' => $subtotalHt,
                    'total' => $subtotalHt,
                ],
            ],
            'status' => fake()->randomElement(['draft', 'pending', 'paid', 'overdue', 'cancelled']),
            'amount_paid' => 0,
            'paid_at' => null,
            'pdf_path' => null,
            'xml_path' => null,
            'reminder_count' => 0,
            'last_reminder_sent' => null,
            'notes' => fake()->optional()->sentence(),
            'internal_notes' => fake()->optional()->sentence(),
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
            'amount_paid' => 0,
            'paid_at' => null,
        ]);
    }

    public function paid(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'status' => 'paid',
                'amount_paid' => $attributes['total_ttc'],
                'paid_at' => fake()->dateTimeBetween($attributes['issue_date'], 'now'),
            ];
        });
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => fake()->dateTimeBetween('-30 days', '-1 day'),
            'amount_paid' => 0,
            'paid_at' => null,
        ]);
    }
}
