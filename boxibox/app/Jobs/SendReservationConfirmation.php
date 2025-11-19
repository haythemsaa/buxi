<?php

namespace App\Jobs;

use App\Models\Reservation;
use App\Notifications\ReservationConfirmed;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendReservationConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public int $timeout = 60;

    /**
     * Create a new job instance.
     */
    public function __construct(
        public Reservation $reservation
    ) {}

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            if ($this->reservation->customer) {
                $this->reservation->customer->notify(
                    new ReservationConfirmed($this->reservation)
                );
            } elseif ($this->reservation->is_guest && $this->reservation->guest_email) {
                // For guest reservations, send notification to guest email
                \Illuminate\Support\Facades\Notification::route('mail', $this->reservation->guest_email)
                    ->notify(new ReservationConfirmed($this->reservation));
            }

            Log::info('Reservation confirmation sent', [
                'reservation_id' => $this->reservation->id,
                'reservation_number' => $this->reservation->reservation_number,
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send reservation confirmation', [
                'reservation_id' => $this->reservation->id,
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
        Log::error('Reservation confirmation job failed', [
            'reservation_id' => $this->reservation->id,
            'exception' => $exception->getMessage(),
        ]);
    }
}
