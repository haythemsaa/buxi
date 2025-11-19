<?php

namespace App\Console\Commands;

use App\Jobs\ProcessLoyaltyPointsExpiry;
use Illuminate\Console\Command;

class ProcessLoyaltyExpiry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'loyalty:process-expiry
                            {--dry-run : Simulate without actually expiring points}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process expiry of loyalty points older than 12 months';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🎁 Processing loyalty points expiry...');
        $this->newLine();

        if ($this->option('dry-run')) {
            $this->warn('🧪 Dry-run mode - No points will be expired');
            $this->newLine();

            // In dry-run, just show what would be expired
            $expiringTransactions = \App\Models\LoyaltyTransaction::where('type', 'earned')
                ->where('expires_at', '<', now())
                ->whereNull('expired_at')
                ->count();

            $this->info("Would expire {$expiringTransactions} transaction(s)");

            return Command::SUCCESS;
        }

        try {
            // Dispatch the job
            ProcessLoyaltyPointsExpiry::dispatch();

            $this->info('✅ Loyalty points expiry job dispatched successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error processing loyalty points expiry:');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
