<?php

namespace App\Jobs;

use App\Models\LoyaltyTransaction;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessLoyaltyPointsExpiry implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $totalExpired = 0;

        try {
            // Find transactions that have expired (older than 12 months)
            $expiryDate = now()->subMonths(12);

            $expiredTransactions = LoyaltyTransaction::where('type', 'earned')
                ->where('expires_at', '<', now())
                ->whereNull('expired_at')
                ->get();

            foreach ($expiredTransactions as $transaction) {
                DB::transaction(function () use ($transaction, &$totalExpired) {
                    // Mark transaction as expired
                    $transaction->update([
                        'expired_at' => now(),
                    ]);

                    // Deduct points from customer's loyalty balance
                    $loyaltyPoint = $transaction->loyaltyPoint;
                    if ($loyaltyPoint) {
                        $loyaltyPoint->decrement('points', $transaction->points);
                        $loyaltyPoint->increment('points_expired', $transaction->points);

                        // Update tier if necessary
                        $loyaltyPoint->updateTier();
                    }

                    $totalExpired += $transaction->points;

                    Log::info('Loyalty points expired', [
                        'transaction_id' => $transaction->id,
                        'customer_id' => $transaction->customer_id,
                        'points' => $transaction->points,
                    ]);
                });
            }

            Log::info('Loyalty points expiry processed', [
                'total_points_expired' => $totalExpired,
                'transactions_count' => $expiredTransactions->count(),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process loyalty points expiry', [
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
