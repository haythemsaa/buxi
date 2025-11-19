<?php

namespace App\Policies;

use App\Models\Box;
use App\Models\User;

class BoxPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Everyone can view boxes (public for search)
        return true;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Box $box): bool
    {
        // Everyone can view box details (public for search)
        return true;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // Only admin/staff can create boxes
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Box $box): bool
    {
        // Only admin/staff can update boxes
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Box $box): bool
    {
        // Only admin/staff can delete boxes
        // And only if not rented
        return $user
            && $user->userable_type === User::class
            && $box->status !== 'rented';
    }

    /**
     * Determine whether the user can reserve the box.
     */
    public function reserve(?User $user, Box $box): bool
    {
        // Box must be available
        return $box->status === 'available';
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Box $box): bool
    {
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Box $box): bool
    {
        return $user && $user->userable_type === User::class;
    }
}
