<?php

namespace App\Console\Commands;

use App\Jobs\SendContractRenewalReminder;
use App\Models\Contract;
use Illuminate\Console\Command;

class SendContractRenewalReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:send-renewal-reminders
                            {--dry-run : Simulate without sending reminders}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send renewal reminders for contracts expiring soon';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('📧 Sending contract renewal reminders...');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('🧪 Dry-run mode - No reminders will be sent');
            $this->newLine();
        }

        $sent = 0;

        // Find contracts expiring in 30 days
        $contracts30Days = Contract::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->addDays(30), now()->addDays(31)])
            ->get();

        // Find contracts expiring in 7 days
        $contracts7Days = Contract::where('status', 'active')
            ->whereNotNull('end_date')
            ->whereBetween('end_date', [now()->addDays(7), now()->addDays(8)])
            ->get();

        $this->info('Contracts expiring in 30 days: ' . $contracts30Days->count());
        $this->info('Contracts expiring in 7 days: ' . $contracts7Days->count());
        $this->newLine();

        if (!$this->option('dry-run')) {
            foreach ($contracts30Days as $contract) {
                SendContractRenewalReminder::dispatch($contract, 30);
                $sent++;
            }

            foreach ($contracts7Days as $contract) {
                SendContractRenewalReminder::dispatch($contract, 7);
                $sent++;
            }
        } else {
            $sent = $contracts30Days->count() + $contracts7Days->count();
        }

        $this->info("✅ {$sent} renewal reminder(s) " . ($this->option('dry-run') ? 'would be' : 'have been') . " sent!");

        return Command::SUCCESS;
    }
}
