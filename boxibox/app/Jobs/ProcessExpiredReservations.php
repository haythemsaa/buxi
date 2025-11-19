<?php

namespace App\Jobs;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessExpiredReservations implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;
    public int $timeout = 300;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $expiredCount = 0;

        try {
            // Find reservations that are expired but not marked as expired
            $reservations = Reservation::where('status', 'pending')
                ->where('expires_at', '<', now())
                ->get();

            foreach ($reservations as $reservation) {
                $reservation->update([
                    'status' => 'expired',
                ]);

                // Release the box if it was held
                if ($reservation->box) {
                    $reservation->box->update([
                        'status' => 'available',
                    ]);
                }

                $expiredCount++;

                Log::info('Reservation expired', [
                    'reservation_id' => $reservation->id,
                    'reservation_number' => $reservation->reservation_number,
                ]);
            }

            Log::info('Expired reservations processed', [
                'count' => $expiredCount,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to process expired reservations', [
                'error' => $e->getMessage(),
                'processed' => $expiredCount,
            ]);

            throw $e;
        }
    }
}
