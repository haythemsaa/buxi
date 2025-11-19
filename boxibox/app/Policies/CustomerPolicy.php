<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(?User $user): bool
    {
        // Only admin/staff can view customer list
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(?User $user, Customer $customer): bool
    {
        if (!$user) {
            return false;
        }

        // Admin can view all
        if ($user->userable_type === User::class) {
            return true;
        }

        // Customer can only view their own profile
        if ($user->userable_type === Customer::class) {
            return $customer->id === $user->userable_id;
        }

        return false;
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(?User $user): bool
    {
        // Admin can create customers
        // Public registration is handled separately
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(?User $user, Customer $customer): bool
    {
        if (!$user) {
            return false;
        }

        // Admin can update all
        if ($user->userable_type === User::class) {
            return true;
        }

        // Customer can only update their own profile (limited fields)
        if ($user->userable_type === Customer::class) {
            return $customer->id === $user->userable_id;
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(?User $user, Customer $customer): bool
    {
        // Only admin can delete customers
        // And only if no active contracts
        return $user
            && $user->userable_type === User::class
            && $customer->contracts()->where('status', 'active')->count() === 0;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(?User $user, Customer $customer): bool
    {
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(?User $user, Customer $customer): bool
    {
        return $user && $user->userable_type === User::class;
    }

    /**
     * Determine whether the user can view customer statistics.
     */
    public function viewStatistics(?User $user, Customer $customer): bool
    {
        if (!$user) {
            return false;
        }

        // Admin can view all statistics
        if ($user->userable_type === User::class) {
            return true;
        }

        // Customer can view their own statistics
        if ($user->userable_type === Customer::class) {
            return $customer->id === $user->userable_id;
        }

        return false;
    }
}
