<?php

namespace App\Console\Commands;

use App\Jobs\ProcessExpiredReservations;
use Illuminate\Console\Command;

class CleanupExpiredReservations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reservations:cleanup
                            {--force : Force cleanup even if not in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Mark expired reservations as expired and release boxes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🧹 Cleaning up expired reservations...');
        $this->newLine();

        try {
            // Dispatch the job
            ProcessExpiredReservations::dispatch();

            $this->info('✅ Expired reservations cleanup job dispatched successfully!');

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Error cleaning up expired reservations:');
            $this->error($e->getMessage());

            return Command::FAILURE;
        }
    }
}
