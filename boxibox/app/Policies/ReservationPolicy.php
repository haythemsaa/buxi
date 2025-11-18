<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\Reservation;
use App\Models\User;

class ReservationPolicy
{
    /**
     * Determine if the user can view any reservations.
     */
    public function viewAny(?User $user): bool
    {
        // Anyone can search for available boxes
        return true;
    }

    /**
     * Determine if the user can view the reservation.
     */
    public function view(?User $user, Reservation $reservation): bool
    {
        // Guest reservations can be viewed by anyone with the link
        if (!$reservation->customer_id) {
            return true;
        }

        // Authenticated users can only view their own reservations
        if ($user && $user->userable_type === Customer::class) {
            return $reservation->customer_id === $user->userable_id;
        }

        // Admins can view all reservations
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine if the user can create reservations.
     */
    public function create(?User $user): bool
    {
        // Anyone can create a reservation (including guests)
        return true;
    }

    /**
     * Determine if the user can update the reservation.
     */
    public function update(?User $user, Reservation $reservation): bool
    {
        // Only pending reservations can be updated
        if ($reservation->status !== 'pending') {
            return false;
        }

        // Authenticated users can only update their own reservations
        if ($user && $user->userable_type === Customer::class) {
            return $reservation->customer_id === $user->userable_id;
        }

        // Admins can update all reservations
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine if the user can cancel the reservation.
     */
    public function cancel(?User $user, Reservation $reservation): bool
    {
        // Only pending or confirmed reservations can be cancelled
        if (!in_array($reservation->status, ['pending', 'confirmed'])) {
            return false;
        }

        // Authenticated users can only cancel their own reservations
        if ($user && $user->userable_type === Customer::class) {
            return $reservation->customer_id === $user->userable_id;
        }

        // Admins can cancel all reservations
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine if the user can confirm the reservation.
     */
    public function confirm(?User $user, Reservation $reservation): bool
    {
        // Only pending reservations can be confirmed
        if ($reservation->status !== 'pending') {
            return false;
        }

        // Only admins can confirm reservations
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine if the user can convert the reservation to a contract.
     */
    public function convert(?User $user, Reservation $reservation): bool
    {
        // Only confirmed reservations can be converted
        if ($reservation->status !== 'confirmed') {
            return false;
        }

        // Only admins can convert reservations to contracts
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine if the user can delete the reservation.
     */
    public function delete(?User $user, Reservation $reservation): bool
    {
        // Only cancelled or expired reservations can be deleted
        if (!in_array($reservation->status, ['cancelled', 'expired'])) {
            return false;
        }

        // Only admins can delete reservations
        return $user && $user->userable_type === User::class;
    }
}
