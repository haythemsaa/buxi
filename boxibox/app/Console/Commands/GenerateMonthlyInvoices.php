<?php

namespace App\Console\Commands;

use App\Models\Contract;
use App\Models\Invoice;
use App\Notifications\InvoiceGenerated;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateMonthlyInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'invoices:generate-monthly
                            {--dry-run : Simulate without actually creating invoices}
                            {--month= : Month to generate invoices for (YYYY-MM)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate monthly invoices for all active contracts';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('💰 Generating monthly invoices...');
        $this->newLine();

        // Determine the month
        $month = $this->option('month') ? now()->parse($this->option('month')) : now();
        $this->info('Month: ' . $month->format('F Y'));
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('🧪 Dry-run mode - No invoices will be created');
            $this->newLine();
        }

        $generated = 0;
        $errors = 0;

        // Get all active contracts
        $contracts = Contract::where('status', 'active')->get();

        $this->info("Found {$contracts->count()} active contract(s)");
        $this->newLine();

        $progressBar = $this->output->createProgressBar($contracts->count());
        $progressBar->start();

        foreach ($contracts as $contract) {
            try {
                if (!$this->option('dry-run')) {
                    DB::transaction(function () use ($contract, $month) {
                        // Check if invoice already exists for this month
                        $exists = Invoice::where('contract_id', $contract->id)
                            ->whereYear('period_start', $month->year)
                            ->whereMonth('period_start', $month->month)
                            ->exists();

                        if ($exists) {
                            return;
                        }

                        // Create invoice
                        $invoice = $this->createInvoice($contract, $month);

                        // Notify customer
                        if ($contract->customer) {
                            $contract->customer->notify(new InvoiceGenerated($invoice));
                        }
                    });
                }

                $generated++;
            } catch (\Exception $e) {
                $errors++;
                Log::error('Failed to generate invoice', [
                    'contract_id' => $contract->id,
                    'error' => $e->getMessage(),
                ]);
            }

            $progressBar->advance();
        }

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        $this->table(
            ['Metric', 'Count'],
            [
                ['Contracts processed', $contracts->count()],
                ['Invoices generated', $generated],
                ['Errors', $errors],
            ]
        );

        if ($errors === 0) {
            $this->info('✅ Monthly invoices generated successfully!');
            return Command::SUCCESS;
        } else {
            $this->warn("⚠️  Completed with {$errors} error(s)");
            return Command::FAILURE;
        }
    }

    /**
     * Create an invoice for a contract
     */
    private function createInvoice(Contract $contract, $month): Invoice
    {
        $periodStart = $month->copy()->startOfMonth();
        $periodEnd = $month->copy()->endOfMonth();
        $issueDate = $month->copy()->startOfMonth();
        $dueDate = $issueDate->copy()->addDays(15);

        $subtotalHt = $contract->price_monthly_ht + ($contract->insurance_monthly ?? 0);
        $taxAmount = round($subtotalHt * ($contract->tax_rate / 100), 2);
        $totalTtc = $subtotalHt + $taxAmount;

        return Invoice::create([
            'invoice_number' => 'INV-' . now()->format('Ymd') . '-' . str_pad($contract->id, 6, '0', STR_PAD_LEFT),
            'contract_id' => $contract->id,
            'customer_id' => $contract->customer_id,
            'site_id' => $contract->site_id,
            'type' => 'rent',
            'issue_date' => $issueDate,
            'due_date' => $dueDate,
            'period_start' => $periodStart,
            'period_end' => $periodEnd,
            'subtotal_ht' => $subtotalHt,
            'tax_amount' => $taxAmount,
            'tax_rate' => $contract->tax_rate,
            'total_ttc' => $totalTtc,
            'line_items' => [
                [
                    'description' => 'Location box ' . $contract->box->number . ' - ' . $month->format('F Y'),
                    'quantity' => 1,
                    'unit_price' => $contract->price_monthly_ht,
                    'total' => $contract->price_monthly_ht,
                ],
            ],
            'status' => 'pending',
            'amount_paid' => 0,
        ]);
    }
}
