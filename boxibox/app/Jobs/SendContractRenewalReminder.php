<?php

namespace App\Jobs;

use App\Models\Contract;
use App\Notifications\ContractRenewalReminder;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendContractRenewalReminder implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Contract $contract,
        public int $daysBeforeExpiry
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->contract->customer) {
                $this->contract->customer->notify(
                    new ContractRenewalReminder($this->contract, $this->daysBeforeExpiry)
                );

                Log::info('Contract renewal reminder sent', [
                    'contract_id' => $this->contract->id,
                    'contract_number' => $this->contract->contract_number,
                    'days_before_expiry' => $this->daysBeforeExpiry,
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Failed to send contract renewal reminder', [
                'contract_id' => $this->contract->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Contract renewal reminder job failed', [
            'contract_id' => $this->contract->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
